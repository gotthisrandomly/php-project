<?php

class HomeController {
    public function index() {
        // Add any necessary logic here
        
        // Set the title for the page
        $title = 'Welcome to SD777Slots';
        
        // Start output buffering
        ob_start();
        
        // Include the home view
        require_once __DIR__ . '/../views/home.php';
        
        // Get the content and clean the buffer
        $content = ob_get_clean();
        
        // Include the layout
        require_once __DIR__ . '/../views/layout.php';
    }
}