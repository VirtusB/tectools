<?php

declare(strict_types=1);

/**
 * Class Base
 * Denne klasse er fundamentet for alle vores andre klasser, som indeholder metoder der skal køres ved POST requests.
 * Klassen eksister kun for at reducere duplikeret kode, ved at give alle underklasser adgang til variablen $allowedEndpoints og metoden handlePOSTEndpoints.
 * Den kan selvfølgelig udvides senere, hvis der er andre metoder man gerne vil have adgang til at alle underklasser.
 */
abstract class Base {
    /**
     * Liste over POST endpoints, som har en metode i denne klasse (og underklasse), som kan eksekveres automatisk
     * Vi er nød til at have en liste over tilladte endpoints, så brugere ikke kan eksekvere andre metoder i denne klasse
     * @var array|string[]
     */
    public static array $allowedEndpoints = [];

    public function __construct() {
        $this->handlePOSTEndpoints();
    }

    /**
     * Denne metode tjekker, om $_POST array'et indeholder navnet på en metode i denne klasse (og underklasse),
     * tjekker derefter om det er en af de tilladte endpoints,
     * og eksekvere efterfølgende metoden hvis det er tilfældet
     */
    public function handlePOSTEndpoints(): void {
        if (!isset($_POST['post_endpoint'])) {
            return;
        }

        $endpoint = $_POST['post_endpoint'];

        if (method_exists($this, $endpoint) && in_array($endpoint, $this::$allowedEndpoints, true)) {
            $this->$endpoint();
        }
    }
}