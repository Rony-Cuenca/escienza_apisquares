<?php
require_once 'model/Usuario.php';

class UsuarioController {
    public function index() {
        $usuarios = Usuario::obtenerTodos();
        $contenido = 'view/components/usuario.php';
        require 'view/layout.php'; // Usa un layout con navbar
    }

    // public function show($id) {
    //     $usuario = Usuario::obtenerPorId($id);
    //     $contenido = 'view/component/detalle_usuario.php';
    //     require 'view/component/layout.php';
    // }

    // Aquí podrías agregar métodos como crear(), guardar(), eliminar(), etc.
}