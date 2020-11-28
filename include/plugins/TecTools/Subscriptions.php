<?php

declare(strict_types=1);

/**
 * Class Subscriptions
 * Denne klasse indeholder metoder som vedrører abonnementer på TecTools siden
 * Den indeholder metoder til bl.a. oprette, opgradere, nedgradere og annullere abonnementer
 */
class Subscriptions extends Base {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    /**
     * @var bool $disableAutoLoading
     * Forhindrer RCMS at loade denne klasse automatisk
     */
    public static bool $disableAutoLoading;

    public static array $allowedEndpoints = [
        'newSubscription', 'cancelSubscription', 'upgradeDowngradeSubscription'
    ];

    /**
     * @var TecTools $TecTools
     */
    public TecTools $TecTools;

    public function __construct(TecTools $TecTools) {
        $this->TecTools = $TecTools;
        $this->RCMS = $TecTools->RCMS;

        parent::__construct();
    }

    /**
     * Gemmer navnet på abonnementet som brugeren har
     * @param $subName
     */
    private function setSubName(string $subName): void {
        $userID = $this->RCMS->Login->getUserID();

        $this->RCMS->execute('UPDATE Users SET SubName = ? WHERE UserID = ?', array('si', $subName, $userID));

        $_SESSION['user']['SubName'] = $subName;
    }

    /**
     * Sender en API request til Stripe, og annullerer et abonnement
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function cancelSubscription(): void {
        // Tjek om brugeren har nogen aktive check ins
        if ($this->TecTools->CheckIns->getCheckInCountForUser($this->RCMS->Login->getUserID()) !== 0) {
            Helpers::setNotification('Fejl', 'Brugeren har stadig aktive udlejninger', 'error');
            return;
        }

        $customerID = $this->TecTools->Users->getStripeID();
        $subscriptionID = $this->RCMS->StripeWrapper->getSubscriptionID($customerID);

        $this->RCMS->StripeWrapper->getStripeClient()->subscriptions->cancel($subscriptionID);

        $this->setSubName('');

        $this->RCMS->Logs->addLog(Logs::CANCEL_SUBSCRIPTION_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Success', 'Abonnementet er blevet opsagt');
    }

    /**
     * Sender en API request til Stripe, og opgraderer eller nedgraderer et abonnement
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function upgradeDowngradeSubscription(): void {
        $priceID = $_POST['price_id'];
        $productName = $_POST['product_name'];

        $customerID = $this->TecTools->Users->getStripeID();

        $subscription = $this->RCMS->StripeWrapper->getSubscription($customerID);
        $subscriptionID = $subscription->id;
        $client = $this->RCMS->StripeWrapper->getStripeClient();

        $client->subscriptions->update($subscriptionID, [
            'cancel_at_period_end' => false,
            'proration_behavior' => 'create_prorations',
            'items' => [
                [
                    'id' => $subscription->items->data[0]->id,
                    'price' => $priceID,
                ],
            ],
        ]);

        $this->setSubName($productName);

        $logType = $productName === 'Basis' ? Logs::DOWNGRADE_SUBSCRIPTION_TYPE_ID : Logs::UPGRADE_SUBSCRIPTION_TYPE_ID;
        $this->RCMS->Logs->addLog($logType, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Success', 'Dit abonnement er blevet ændret og gemt!');
    }

    /**
     * Sender en API request til Stripe, og opretter et abonnement for en eksisterende kunde
     * @throws \Stripe\Exception\ApiErrorException
     */
    protected function newSubscription(): void {
        $customerID = $this->TecTools->Users->getStripeID();
        $priceID = $_POST['priceID'];
        $paymentMethodID = $_POST['paymentMethodID'];

        $client = $this->RCMS->StripeWrapper->getStripeClient();

        try {
            $payment_method = $client->paymentMethods->retrieve(
                $paymentMethodID
            );

            $payment_method->attach([
                'customer' => $customerID
            ]);
        } catch (Exception $e) {
            Helpers::outputAJAXResult(400, ['result' => $e->getMessage(), 'data' => [$customerID, $priceID, $paymentMethodID]]);
        }

        // Sæt standard betalingsmetode for kunden
        $client->customers->update($customerID, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodID
            ]
        ]);

        // Opret abonnementet
        $subscription = $client->subscriptions->create([
            'customer' => $customerID,
            'items' => [
                [
                    'price' => $priceID,
                ],
            ],
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        Helpers::setNotification('Success', 'Du er nu abonneret!❤');

        $this->setSubName($_POST['product_name']);

        $this->RCMS->Logs->addLog(Logs::NEW_SUBSCRIPTION_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::outputAJAXResult(200, ['subscription' => $subscription]);
    }
}