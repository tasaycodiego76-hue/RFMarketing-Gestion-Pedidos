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

    $routes->get('areas',             'Administrador\AreasController::index');
    $routes->get('areas/clientes', 'Administrador\AreasController::clientes');
    $routes->post('areas/registrar',  'Administrador\AreasController::registrar');
    $routes->post('areas/clientes/registrar', 'Administrador\AreasController::registrarCliente');
    $routes->get('areas/clientes/listar/(:num)', 'Administrador\AreasController::listarPorEmpresa/$1');
    $routes->post('areas/clientes/registrar',    'Administrador\AreasController::registrarCliente');

    $routes->get('empresas',             'Administrador\EmpresasController::index');

    $routes->get('kanban/(:num)/(:num)', 'Administrador\Kanban::index/$1/$2');
    // Kanban acciones
$routes->post('kanban/asignar',        'Administrador\Kanban::asignar');
$routes->post('kanban/cambiarEstado',  'Administrador\Kanban::cambiarEstado');
$routes->post('kanban/cancelar',       'Administrador\Kanban::cancelar');
$routes->get('kanban/empleados/(:num)', 'Administrador\Kanban::empleadosPorArea/$1');
$routes->get('kanban/detalle/(:num)',   'Administrador\Kanban::detalle/$1');

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
    //Plantilla
    $routes->get('mis_solicitudes', 'Cliente\MisPedidosController::index');
    //Lista de los Requerimientos del Cliente
    $routes->get('pedidos/listar', 'Cliente\MisPedidosController::listar');
    //Datos de los Servicio para Eleccion del Modal
    $routes->get('nuevo-pedido/servicios', 'Cliente\MisPedidosController::servicios');
    //Prueba de Registro de Requerimiento (Servicio_Personalizado) Por Ahora sin Carga de Archivos
    $routes->post('requerimiento/guardar', 'Cliente\RequerimientoController::guardar');
    // Ruta para obtener el JSON con toda la info (EndPoint)
    $routes->get('requerimiento/detalle/(:num)', 'Cliente\RequerimientoController::detalle/$1');
    // Ruta especial para VER los archivos protegidos
    $routes->get('requerimiento/archivo/(:any)', 'Cliente\RequerimientoController::verArchivo/$1');
});