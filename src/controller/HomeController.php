<?php
class HomeController
{
    public function index()
    {
        // Aquí puedes cargar la vista principal o realizar cualquier lógica necesaria.
        $contenido = 'view/components/home.php';
        require 'view/layout.php';
    }
}

