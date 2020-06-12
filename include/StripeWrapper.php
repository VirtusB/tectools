<?php

require_once __DIR__ . '/vendor/autoload.php';

class StripeWrapper {
    /**
     * @var RCMS $RCMS
     */
    var $RCMS;

    /**
     * @var \Stripe\StripeClient
     */
    private $stripe;

    /**
     * StripeWrapper constructor.
     * @param RCMS $RCMS
     */
    public function __construct(RCMS $RCMS) {
        $this->RCMS = $RCMS;
        $this->stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);
    }

    /**
     * Returnerer den instans af StripeClient som er blevet lavet i __construct
     * @return \Stripe\StripeClient
     */
    public function getStripeClient() {
        return $this->stripe;
    }

    /**
     * Opretter en kunde i Stripe
     *
     * Reference: https://stripe.com/docs/api/customers/create
     * @param array $params
     * @return \Stripe\Customer
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createCustomer($params) {
        return $this->getStripeClient()->customers->create($params);
    }

    /**
     * Henter alle produkter fra Stripe, og returnerer kun det vigtige data som vi er interesseret i
     * @return array[]
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getStripeProducts() {
        $products = $this->getStripeClient()->products->all();

        $products = array_map(function ($product) {
            /**
             * @var \Stripe\Product $product
             */

            $metadata = $product->metadata->toArray();
            foreach ($metadata as $key => $prop) {
                $metadata[$key] = json_decode($prop, true);
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $this->getPlanPrice($product->id),
                'metadata' => $metadata
            ];
        }, $products->data);

        usort($products, function ($product1, $product2) {
            return $product1['price'] <=> $product2['price'];
        });

        return $products;
    }

    /**
     * Tjekker om prisen for et produkt er over 50 kroner
     *
     * Vi anser et produkt værende premium hvis det koster mere end 50 kroner
     * @param array $product
     * @return bool
     */
    public function isPremiumPlan($product) {
        return $product['price'] > 50;
    }

    /**
     * Returnerer prisen for en Stripe plan
     *
     * Reference for plan: https://stripe.com/docs/api/plans
     *
     * Reference for produkt: https://stripe.com/docs/api/products
     * @param string $productID ID på et Stripe produkt, F.eks. prod_HQWzfyxLdAjwWo
     * @return bool|float|int
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getPlanPrice($productID) {
        $plans = $this->getStripeClient()->plans->all()->data;

        $plan = array_filter($plans, function ($plan) use($productID) {
           return $plan['product'] === $productID;
        });

        if ($plan) {
            /**
             * @var \Stripe\Plan $plan
             */

            $plan = reset($plan);
            return $plan->amount / 100;
        }

        return false;
    }

    /**
     * Returnerer et Stripe produkt
     *
     * Reference for produkt: https://stripe.com/docs/api/products
     * @param string $id ID på et Stripe produkt, F.eks. prod_HQWzfyxLdAjwWo
     * @return bool|array
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getStripeProduct($id) {
        $product = array_filter($this->getStripeProducts(), function ($product) use ($id) {
           return $product['id'] === $id;
        });

        if ($product) {
            $product = reset($product);
            return $product;
        }

        return false;
    }

    /**
     * Returnerer ID på det Stripe produkt som en kunde har abonneret på
     *
     * Reference for kunder: https://stripe.com/docs/api/customers
     * @param string $customerStripeID Stripe customer ID som ligger i "StripeID" kolonnen i "Users" tabellen i databasen
     * @return bool|string|\Stripe\Product|null
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getProductIDForCustomer($customerStripeID) {
        $customer = $this->getStripeClient()->customers->retrieve($customerStripeID);

        /**
         * @var \Stripe\Subscription $sub
         */
        $sub = $customer->subscriptions->data[0] ?? null;

        if (!$sub) {
            return false;
        }

        /**
         * @var \Stripe\SubscriptionItem $subItem
         */
        $subItem = $sub->items->data[0];

        return $subItem->plan->product;
    }

}