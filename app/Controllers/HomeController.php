<?php

namespace App\Controllers;

class HomeController
{
    public function index()
    {
        $title = "Welcome to the Slot Machine Game!";
        $content = "Welcome to our exciting slot machine game. Are you ready to try your luck?";
        require_once __DIR__ . '/../../views/home.php';
    }

    public function play()
    {
        // TODO: Implement the slot machine game logic here
        $title = "Slot Machine Game";
        $content = "Spin the slot machine!";
        require_once __DIR__ . '/../../views/slot_machine.php';
    }
}