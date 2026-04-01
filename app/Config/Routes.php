<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('admin/usuarios/listar', 'Administrador\UsuarioController::listar');