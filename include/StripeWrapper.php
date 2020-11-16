<?php

declare(strict_types=1);

use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Plan;
use Stripe\Product;
use Stripe\StripeClient;
use Stripe\Subscription;
use Stripe\SubscriptionItem;

class StripeWrapper {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    /**
     * @var StripeClient
     */
    private StripeClient $stripe;

    /**
     * StripeWrapper constructor.
     * @param RCMS $RCMS
     * @param string $secretStripeKey
     */
    public function __construct(RCMS $RCMS, string $secretStripeKey) {
        $this->RCMS = $RCMS;
        $this->stripe = new StripeClient($secretStripeKey);
    }

    /**
     * Returnerer den instans af StripeClient som er blevet lavet i __construct
     * @return StripeClient
     */
    public function getStripeClient(): StripeClient {
        return $this->stripe;
    }

    /**
     * Opretter en kunde i Stripe
     *
     * Reference: https://stripe.com/docs/api/customers/create
     * @param array $params
     * @return Customer
     * @throws ApiErrorException
     */
    public function createCustomer(array $params): Customer {
        return $this->getStripeClient()->customers->create($params);
    }

    public function removeCustomer(string $customerID) {
        return $this->getStripeClient()->customers->delete($customerID);
    }

    public function editCustomer(string $customerID, array $params): Customer {
        return $this->getStripeClient()->customers->update($customerID, $params);
    }

    /**
     * Henter alle produkter fra Stripe, og returnerer kun det vigtige data som vi er interesseret i
     * @return array[]
     * @throws ApiErrorException
     */
    public function getStripeProducts(): array {
        $products = $this->getStripeClient()->products->all([
            'created' => ['gt' => strtotime('2020-11-15 12:00:00')]
        ]);

        $products = array_map(function ($product) {
            /**
             * @var Product $product
             */

            $metadata = $product->metadata->toArray();
            foreach ($metadata as $key => $prop) {
                $metadata[$key] = json_decode($prop, true, 512, JSON_THROW_ON_ERROR);
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $this->getPlanPrice($product->id),
                'metadata' => $metadata
            ];
        }, $products->data);

        usort($products, static fn($product1, $product2) => $product1['price'] <=> $product2['price']);

        return $products;
    }

    /**
     * Tjekker om prisen for et produkt er over 200 kroner
     *
     * Vi anser et produkt værende premium hvis det koster mere end 200 kroner
     * @param array $product
     * @return bool
     */
    public function isPremiumPlan(array $product): bool {
        return $product['price'] > 200;
    }

    /**
     * Returnerer prisen for en Stripe plan
     *
     * Reference for plan: https://stripe.com/docs/api/plans
     *
     * Reference for produkt: https://stripe.com/docs/api/products
     * @param string $productID ID på et Stripe produkt, F.eks. prod_HQWzfyxLdAjwWo
     * @return bool|float|int
     * @throws ApiErrorException
     */
    public function getPlanPrice(string $productID) {
        $plans = $this->getStripeClient()->plans->all()->data;

        $plan = array_filter($plans, static fn($plan) => $plan['product'] === $productID);

        if ($plan) {
            /**
             * @var Plan $plan
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
     * @throws ApiErrorException
     */
    public function getStripeProduct(string $id) {
        $product = array_filter($this->getStripeProducts(), static fn($product) => $product['id'] === $id);

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
     * @return bool|string|Product|null
     * @throws ApiErrorException
     */
    public function getProductIDForCustomer(string $customerStripeID) {
        $customer = $this->getStripeClient()->customers->retrieve($customerStripeID);

        /**
         * @var Subscription $sub
         */
        $sub = $customer->subscriptions->data[0] ?? null;

        if (!$sub) {
            return false;
        }

        /**
         * @var SubscriptionItem $subItem
         */
        $subItem = $sub->items->data[0];

        return $subItem->plan->product;
    }

}