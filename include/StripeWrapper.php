<?php

declare(strict_types=1);

use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Plan;
use Stripe\Product;
use Stripe\StripeClient;
use Stripe\Subscription;
use Stripe\SubscriptionItem;

/**
 * Class StripeWrapper
 * Denne klasse fungerer som en wrapper til Stripe API'et.
 * Den indeholder metoder som gør det nemmere at arbejde med API'et og reducere mængden af kode der skal skrives.
 * Den indeholder bl.a. metoder til at oprette, redigere og slette kunder og hente produkter
 */
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

    /**
     * Sletter en kunde i Stripe
     * @param string $customerID Kundens ID
     * @return Customer
     * @throws ApiErrorException
     */
    public function removeCustomer(string $customerID) {
        return $this->getStripeClient()->customers->delete($customerID);
    }

    /**
     * Ændrer brugeroplysninger for en kunde
     * @param string $customerID Kundens ID
     * @param array $params
     * @return Customer
     * @throws ApiErrorException
     */
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
     * Tjekker om prisen for et produkt er over X kroner
     *
     * @param array $product
     * @param float $premiumPrice
     * @return bool
     */
    public function isPremiumPlan(array $product, float $premiumPrice): bool {
        return $product['price'] > $premiumPrice;
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
     * Returnerer en Stripe plan
     *
     * Reference for plan: https://stripe.com/docs/api/plans
     *
     * Reference for produkt: https://stripe.com/docs/api/products
     * @param string $productID ID på et Stripe produkt, F.eks. prod_HQWzfyxLdAjwWo
     * @return \Stripe\StripeObject[]|null
     * @throws ApiErrorException
     */
    public function getPlan(string $productID) {
        $plans = $this->getStripeClient()->plans->all()->data;

        $plan = array_filter($plans, static fn($plan) => $plan['product'] === $productID);

        if ($plan) {
            $plan = reset($plan);
        }

        return $plan ?? null;
    }


    /**
     * Returnerer betalingsmetode ID'et for en kundes kort
     * @param string $customerID
     * @return false|string
     * @throws ApiErrorException
     */
    public function getCustomerPaymentCardID(string $customerID) {
        $methods = $this->getStripeClient()->paymentMethods->all([
            'customer' => $customerID,
            'type' => 'card'
        ]);

        $card = $methods->data[0] ?? null;

        if ($card !== null && !empty($card->id)) {
            return $card->id;
        }

        return false;
    }

    /**
     * Hæver penge fra et betalingskort og returnerer ID'et for betalingen
     * @param string $customerID
     * @param float $amount
     * @param string $cardID
     * @param string $description
     * @return string
     * @throws ApiErrorException
     */
    public function createPaymentIntent(string $customerID, float $amount, string $cardID, string $description = '') {
        $intent = $this->getStripeClient()->paymentIntents->create([
            'amount' => $amount * 100,
            'currency' => 'dkk',
            'customer' => $customerID,
            'payment_method' => $cardID,
            'off_session' => true,
            'confirm' => true,
            'description' => $description ?? ''
        ]);

        return $intent->id;
    }

    /**
     * Returnerer ID'et for et abonnement for kunden med $customerID
     * @param string $customerID Kundens ID
     * @return string|null
     * @throws ApiErrorException
     */
    public function getSubscriptionID(string $customerID): ?string {
        $subscription = $this->getStripeClient()->subscriptions->all(['customer' => $customerID]);
        return $subscription->data[0]->id ?? null;
    }

    /**
     * Returnerer abonnementet for kunden med $customerID
     * @param string $customerID Kundens ID
     * @return \Stripe\StripeObject
     * @throws ApiErrorException
     */
    public function getSubscription(string $customerID): ?\Stripe\StripeObject {
        return $this->getStripeClient()->subscriptions->all(['customer' => $customerID])->data[0] ?? null;
    }

    /**
     * Returnerer et Stripe produkt
     *
     * Reference for produkt: https://stripe.com/docs/api/products
     * @param string $productID ID på et Stripe produkt, F.eks. prod_HQWzfyxLdAjwWo
     * @return false|array
     * @throws ApiErrorException
     */
    public function getStripeProduct(string $productID) {
        $product = array_filter($this->getStripeProducts(), static fn($product) => $product['id'] === $productID);

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
     * @param string $customerID Stripe customer ID som ligger i "StripeID" kolonnen i "Users" tabellen i databasen
     * @return false|string
     * @throws ApiErrorException
     */
    public function getProductIDForCustomer(string $customerID) {
        $customer = $this->getStripeClient()->customers->retrieve($customerID);

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

        return $subItem->plan->product ?? false;
    }

}