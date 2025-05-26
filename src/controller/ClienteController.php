<?php
require_once 'model/Cliente.php';


    class ClienteController {
    public function index() {
        $clientes = Cliente::obtenerTodos();
        $contenido = 'view/components/cliente.php';
        require 'view/layout.php';
    }

    // public function show($id) {
    //     $cliente = Cliente::obtenerPorId($id);
    //     $contenido = 'view/component/detalle_cliente.php';
    //     require 'view/component/layout.php';
    // }
}