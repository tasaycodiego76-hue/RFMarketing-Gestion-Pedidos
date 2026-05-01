<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/* $routes->get('/', 'Home::index'); */

//Rutas Publicas(Cualquier sitio dentro de la aplicacion que no requiere acceso)
$routes->get('/', 'AuthController::login');                      //RUTA RAIZ 
$routes->get('/auth/login', 'AuthController::login');            //Intento de Inicio Sesion
$routes->post('/auth/login', 'AuthController::verificar');    //Verificar la Sesion
$routes->get('/auth/logout', 'AuthController::logout');          //Cerrar Sesion
$routes->get('logout', 'AuthController::logout');



//Rutas para el Administrador
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Administrador\DashboardController::index');
    $routes->get('usuarios', 'Administrador\UsuarioController::index');
    $routes->get('usuarios/listar', 'Administrador\UsuarioController::listar');
    $routes->get('usuarios/obtener/(:num)', 'Administrador\UsuarioController::obtener/$1');
    $routes->get('servicios/listar', 'Administrador\UsuarioController::listarServicios');
    $routes->post('usuarios/registrar', 'Administrador\UsuarioController::registrar');
    $routes->put('usuarios/editar/(:num)', 'Administrador\UsuarioController::editar/$1');
    $routes->post('usuarios/toggleEstado', 'Administrador\UsuarioController::toggleEstado');
    $routes->get('usuarios/infoReasignar/(:num)', 'Administrador\UsuarioController::infoReasignar/$1');
    $routes->post('usuarios/reasignarCliente', 'Administrador\UsuarioController::reasignarCliente');
    $routes->post('usuarios/reasignarEmpleadoArea', 'Administrador\UsuarioController::reasignarEmpleadoArea');

    $routes->get('areas', 'Administrador\AreasController::index');
    $routes->get('areas/listar', 'Administrador\AreasController::listar');
    $routes->post('areas/registrar', 'Administrador\AreasController::registrar');
    $routes->get('areas/obtener/(:num)', 'Administrador\AreasController::obtener/$1');
    $routes->put('areas/editar/(:num)', 'Administrador\AreasController::editar/$1');
    $routes->post('areas/toggleEstado', 'Administrador\AreasController::toggleEstado');

    $routes->get('empresas', 'Administrador\EmpresasController::index');

    $routes->get('kanban/(:num)/(:num)', 'Administrador\Kanban::index/$1/$2');
    // Kanban acciones
    $routes->post('kanban/asignarArea', 'Administrador\Kanban::asignarArea');
    $routes->post('kanban/cambiarEstado', 'Administrador\Kanban::cambiarEstado');
    $routes->post('kanban/cancelar', 'Administrador\Kanban::cancelar');
    $routes->get('kanban/empleados/(:num)', 'Administrador\Kanban::empleadosPorArea/$1');
    $routes->get('kanban/detalle/(:num)', 'Administrador\Kanban::detalle/$1');
    $routes->get('kanban/areas', 'Administrador\Kanban::areasAgencia');
    $routes->post('kanban/cambiarPrioridad', 'Administrador\Kanban::cambiarPrioridad');
    $routes->post('kanban/asignarEmpleado', 'Administrador\Kanban::asignarEmpleado');
    $routes->post('kanban/iniciarTrabajo', 'Administrador\kanban::iniciarTrabajo');
    $routes->post('kanban/regresarAProceso', 'Administrador\kanban::regresarAProceso');

    //EMPRESAS 
    $routes->get('empresas', 'Administrador\EmpresasController::index');
    $routes->get('empresas/listar', 'Administrador\EmpresasController::listar');
    $routes->get('empresas/obtener/(:num)', 'Administrador\EmpresasController::obtener/$1');
    $routes->post('empresas/registrar', 'Administrador\EmpresasController::registrar');
    $routes->put('empresas/editar/(:num)', 'Administrador\EmpresasController::editar/$1');
    $routes->post('empresas/toggleEstado', 'Administrador\EmpresasController::toggleEstado');

    $routes->get('historial', 'Administrador\HistorialController::index');
});

// Rutas para el Responsable (Jefe de Área)
$routes->group('responsable', ['filter' => 'auth'], function ($routes) {
    // DASHBOARD
    $routes->get('dashboard', 'Responsable\PedidosAreaController::index');

    // BANDEJA DE ENTRADA
    // Vistas
    $routes->get('bandeja', 'Responsable\PedidosAreaController::vistaBandeja');
    // Datos(JSON)
    $routes->get('pedidos/bandeja-json', 'Responsable\PedidosAreaController::bandeja');
    $routes->get('pedidos/detalle', 'Responsable\PedidosAreaController::obtenerDetalleRequerimiento');

    // GESTIÓN DE EQUIPO 
    // Vistas
    $routes->get('equipo', 'Responsable\EquipoController::index');
    $routes->get('en-proceso', 'Responsable\EquipoController::vistaTareasEnProceso');
    // Datos (JSON)
    $routes->get('empleados/mi-area-json', 'Responsable\EquipoController::empleadosMiAreaJson');
    $routes->get('tareas/en-proceso', 'Responsable\EquipoController::tareasEnProceso');
    $routes->get('tareas/empleado/(:num)', 'Responsable\EquipoController::tareasPorEmpleado/$1');
    $routes->get('equipo/miembro/(:num)', 'Responsable\EquipoController::detalleMiembro/$1');

    // GESTIÓN OPERATIVA (TAREAS)
    $routes->post('pedidos/asignar', 'Responsable\PedidosAreaController::asignarPedido');
    $routes->post('pedidos/actualizar', 'Responsable\PedidosAreaController::actualizarRequerimiento');
    $routes->post('pedido-iniciar/(:num)', 'Responsable\PedidosAreaController::iniciarPedido/$1');
    $routes->post('pedido-entregar/(:num)', 'Responsable\PedidosAreaController::entregarPedido/$1');

    // SEGUIMIENTO Y RETROALIMENTACIÓN
    $routes->get('historial', 'Responsable\GestionController::historial');
    $routes->get('retroalimentacion', 'Responsable\GestionController::retroalimentacion');

    // RECURSOS
    $routes->get('archivos/vista-previa/(:num)', 'Responsable\PedidosAreaController::vistaPrevia/$1');
    $routes->get('servicios/listar', 'Responsable\PedidosAreaController::listarServicios');
});

// Rutas para el Empleado
$routes->group('empleado', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Empleado\MisPedidosController::dashboard');
    $routes->get('mis_pedidos', 'Empleado\MisPedidosController::index');
    $routes->get('historial', 'Empleado\MisPedidosController::historial');
    $routes->get('retroalimentacion', 'Empleado\MisPedidosController::retroalimentacion');
    $routes->post('pedido-iniciar/(:num)', 'Empleado\MisPedidosController::iniciarPedido/$1');
    $routes->post('pedido-entregar/(:num)', 'Empleado\MisPedidosController::entregarPedido/$1');
    $routes->get('pedido-detalle/(:num)', 'Empleado\MisPedidosController::detalle/$1');
});

// Rutas para el Cliente
$routes->group('cliente', ['filter' => 'auth'], function ($routes) {
    // DASHBOARD / MIS SOLICITUDES
    // Vistas
    $routes->get('mis_solicitudes', 'Cliente\MisPedidosController::index');
    // Datos (JSON)
    $routes->get('pedidos/listar', 'Cliente\MisPedidosController::listar');

    // REQUERIMIENTOS
    // Vistas
    $routes->get('detalle_requerimiento/(:num)', 'Cliente\RequerimientoController::vistaDetalle/$1');
    // Datos (JSON/POST)
    $routes->post('requerimiento/guardar', 'Cliente\RequerimientoController::guardar');
    $routes->get('requerimiento/detalle/(:num)', 'Cliente\RequerimientoController::detalle/$1');
    $routes->get('nuevo-pedido/servicios', 'Cliente\MisPedidosController::servicios');

    // SEGUIMIENTO Y NOTIFICACIONES
    // Vistas
    $routes->get('notificaciones', 'Cliente\TrackingController::notificaciones');
    $routes->get('seguimiento/(:num)', 'Cliente\TrackingController::vistaSeguimiento/$1');
    // Datos (JSON)
    $routes->get('notificaciones-json', 'Cliente\TrackingController::notificacionesJson');
    $routes->get('requerimiento/seguimiento/(:num)', 'Cliente\TrackingController::seguimiento/$1');

    // RECURSOS
    $routes->get('archivos/(:num)', 'Cliente\RequerimientoController::verArchivo/$1');
});