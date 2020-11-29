<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once 'setupTestDatabase.php';

class LoginTest extends TestCase {

    protected RCMS $RCMS;

    protected TecTools $TecTools;

    protected static array $userTemplate = [
      'email' => 'test@user.dk',
      'password' => 'password',
      'firstname' => 'Test',
      'lastname' => 'User',
      'phone' => '11111111',
      'address' => 'Test Road 12',
      'city' => 'TestCity',
      'zipcode' => '2750'
    ];

    protected function setUp(): void {
        $this->RCMS = setupTestDatabase(false, true);
        $this->TecTools = $GLOBALS['TecTools'];
        $_POST = [];
    }

    protected function tearDown(): void {
        $_POST = [];
        $this->RCMS->closeRCMS();
        setupTestDatabase(false, false);
    }

    /*
     * Tester om en bruger kan blive oprettet når alt fungerer
     */
    public function test_SignUp_EverythingValid_CreatesUser(): void {
        $user = array_replace([], self::$userTemplate);
        $_POST = $user;

        $this->RCMS->Login->createUser();

        $userFromDatabase = $this->RCMS->Login->getUserByEmail($user['email']);

        // Fjern brugeren fra Stripe, der er ingen grund til at den ligger der
        if (!empty($userFromDatabase)) {
            $this->RCMS->StripeWrapper->removeCustomer($userFromDatabase['StripeID']);
        }

        $this->assertNotEmpty($userFromDatabase);
    }

    /**
     * Test at en bruger ikke bliver oprettet, hvis der allerede eksisterer en bruger med den email
     */
    public function test_SignUp_DuplicateEmail_UserIsNotCreated(): void {
        $user = array_replace([], self::$userTemplate);

        $_POST = $user;
        $this->RCMS->Login->createUser();

        $_POST = $user;
        $this->RCMS->Login->createUser();

        $usersEmailQuery = $this->RCMS->execute('SELECT * FROM Users WHERE Email = ?', array('s', $user['email']));
        $userEmailNumRows = $usersEmailQuery->num_rows;

        //Fjern brugeren fra Stripe, der er ingen grund til at den ligger der
        if ($userEmailNumRows !== 0) {
            $userFromDatabase = $usersEmailQuery->fetch_array(MYSQLI_ASSOC);
            $this->RCMS->StripeWrapper->removeCustomer($userFromDatabase['StripeID']);
        }

        // Forvent at der kun ligger en bruger i databasen med den email
        $this->assertEquals(1, $userEmailNumRows);
    }
}