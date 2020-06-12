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

    public function getStripeClient() {
        return $this->stripe;
    }

    public function createCustomer($params) {
        return $this->getStripeClient()->customers->create($params);
    }

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

    public function isPremiumPlan($product) {
        return $product['price'] > 50;
    }

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