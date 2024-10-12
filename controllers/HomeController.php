<?php

class HomeController {
    public function index() {
        // Add any necessary logic here
        
        // Include the home view
        require_once __DIR__ . '/../views/home.php';
    }
}