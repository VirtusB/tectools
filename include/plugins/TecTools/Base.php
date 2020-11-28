<?php

abstract class Base {
    public static array $allowedEndpoints = [];

    private array $classes = [];

    public function __construct() {
        $this->classes[] = $this;
        $this->handlePOSTEndpoints();
    }

    public function handlePOSTEndpoints(): void {
        if (!isset($_POST['post_endpoint'])) {
            return;
        }

        $endpoint = $_POST['post_endpoint'];

        //foreach ($this->classes as $class) {
        //    if (method_exists($class, $endpoint) && in_array($endpoint, $class::$allowedEndpoints, true)) {
        //        $this->$endpoint();
        //    }
        //}

        if (method_exists($this, $endpoint) && in_array($endpoint, $this::$allowedEndpoints, true)) {
            $this->$endpoint();
        }
    }
}