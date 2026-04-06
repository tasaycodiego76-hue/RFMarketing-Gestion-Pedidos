<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */



$routes->get('/', 'Home::index');

//Rutas para el Administrador
$routes->group('admin', function ($routes) {
    $routes->get('dashboard', 'Administrador\DashboardController::index');
    $routes->get('usuarios',        'Administrador\UsuarioController::index');
    $routes->get('usuarios/listar', 'Administrador\UsuarioController::listar');
    $routes->get('servicios/listar',     'Administrador\UsuarioController::listarServicios');
    $routes->post('usuarios/registrar',  'Administrador\UsuarioController::registrar');
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
    $routes->get('pedidos/listar', 'Cliente\MisPedidosController::listar');
    $routes->get('nuevo-pedido/servicios', 'Cliente\MisPedidosController::servicios');
});

