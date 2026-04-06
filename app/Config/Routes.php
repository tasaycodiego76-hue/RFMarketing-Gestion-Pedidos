<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */



$routes->get('/', 'Home::index');

//Rutas para el Administrador
$routes->group('admin', function ($routes) {
    $routes->get('dashboard', 'Administrador\DashboardController::index');
    $routes->get('usuarios',             'Administrador\UsuarioController::index');
    $routes->get('usuarios/listar',      'Administrador\UsuarioController::listar');
    $routes->get('usuarios/obtener/(:num)', 'Administrador\UsuarioController::obtener/$1'); // editar: cargar datos
    $routes->get('servicios/listar',     'Administrador\UsuarioController::listarServicios');
    $routes->post('usuarios/registrar',  'Administrador\UsuarioController::registrar');
    $routes->put('usuarios/editar/(:num)',   'Administrador\UsuarioController::editar/$1');  // editar: guardar
    $routes->post('usuarios/toggleEstado',   'Administrador\UsuarioController::toggleEstado'); // habilitar/deshabilitar
});

//Rutas para el Responsable (Jefe de Área)
$routes->group('responsable', function ($routes) {
    $routes->get('pedidos_area', 'Responsable\PedidosAreaController::index');
});

//Rutas para el Empleado
$routes->group('empleado', function ($routes) {
    $routes->get('mis_pedidos', 'Empleado\MisPedidosController::index');
});

//Rutas para el Cliente
$routes->group('cliente', function ($routes) {
    $routes->get('mis_solicitudes', 'Cliente\MisPedidosController::index');
});

