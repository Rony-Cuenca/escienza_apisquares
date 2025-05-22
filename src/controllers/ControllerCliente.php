<?php
    require_once 'models/Cliente.php';

    class ClienteController {
        public function index() {
            $cliente = new Cliente();
            $clientes = $cliente->get_clientes();
            require_once 'views/cliente/index.php';
        }
    }