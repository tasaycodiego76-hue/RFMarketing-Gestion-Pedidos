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


//Rutas para el Administrador
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Administrador\DashboardController::index');
    $routes->get('usuarios', 'Administrador\UsuarioController::index');
    $routes->get('usuarios/listar', 'Administrador\UsuarioController::listar');
    $routes->get('usuarios/obtener/(:num)', 'Administrador\UsuarioController::obtener/$1'); // editar: cargar datos

    $routes->get('servicios/listar', 'Administrador\UsuarioController::listarServicios');
    $routes->post('usuarios/registrar', 'Administrador\UsuarioController::registrar');
    $routes->put('usuarios/editar/(:num)', 'Administrador\UsuarioController::editar/$1');
    $routes->post('usuarios/toggleEstado', 'Administrador\UsuarioController::toggleEstado');

    $routes->get('areas', 'Administrador\AreasController::index');
    $routes->get('areas/listar', 'Administrador\AreasController::listar');
    $routes->post('areas/registrar', 'Administrador\AreasController::registrar');
    $routes->get('areas/obtener/(:num)', 'Administrador\AreasController::obtener/$1');
    $routes->put('areas/editar/(:num)', 'Administrador\AreasController::editar/$1');
    $routes->post('areas/toggleEstado', 'Administrador\AreasController::toggleEstado');

    $routes->get('empresas', 'Administrador\EmpresasController::index');

    $routes->get('kanban/(:num)/(:num)', 'Administrador\Kanban::index/$1/$2');
    // Kanban acciones
    $routes->post('kanban/asignar', 'Administrador\Kanban::asignar');
    $routes->post('kanban/asignarArea', 'Administrador\Kanban::asignarArea');
    $routes->post('kanban/cambiarEstado', 'Administrador\Kanban::cambiarEstado');
    $routes->post('kanban/cancelar', 'Administrador\Kanban::cancelar');
    $routes->get('kanban/empleados/(:num)', 'Administrador\Kanban::empleadosPorArea/$1');
    $routes->get('kanban/detalle/(:num)', 'Administrador\Kanban::detalle/$1');
    $routes->get('kanban/areas', 'Administrador\Kanban::areasAgencia');
    $routes->get('responsable/kanban', 'Administrador\Kanban::responsable');
    $routes->post('kanban/cambiarPrioridad', 'Administrador\Kanban::cambiarPrioridad');
    $routes->post('kanban/asignarEmpleado', 'Administrador\Kanban::asignarEmpleado');
    $routes->post('kanban/iniciarTrabajo', 'Administrador\kanban::iniciarTrabajo');

    //EMPRESAS 
    $routes->get('empresas', 'Administrador\EmpresasController::index');
    $routes->get('empresas/listar', 'Administrador\EmpresasController::listar');
    $routes->get('empresas/obtener/(:num)', 'Administrador\EmpresasController::obtener/$1');
    $routes->post('empresas/registrar', 'Administrador\EmpresasController::registrar');
    $routes->put('empresas/editar/(:num)', 'Administrador\EmpresasController::editar/$1');
    $routes->post('empresas/toggleEstado', 'Administrador\EmpresasController::toggleEstado');
});

//Rutas para el Responsable (Jefe de Área)
$routes->group('responsable', ['filter' => 'auth'], function ($routes) {
    //Dashboar de Metricas - Plantilla (Prueba)
    $routes->get('dashboard', 'Responsable\PedidosAreaController::index');

    // Vistas
    $routes->get('bandeja', 'Responsable\PedidosAreaController::vistaBandeja');
    $routes->get('equipo', 'Responsable\PedidosAreaController::vistaEquipo');
    $routes->get('en-proceso', 'Responsable\PedidosAreaController::vistaTareasEnProceso');

    // Endpoints JSON
    $routes->get('pedidos/bandeja-json', 'Responsable\PedidosAreaController::bandeja');
    $routes->get('empleados/mi-area-json', 'Responsable\PedidosAreaController::empleadosMiAreaJson');
    $routes->post('pedidos/asignar', 'Responsable\PedidosAreaController::asignarPedido');
    $routes->get('pedidos/detalle', 'Responsable\PedidosAreaController::obtenerDetalleRequerimiento');
    $routes->get('tareas/en-proceso', 'Responsable\PedidosAreaController::tareasEnProceso');
    $routes->get('tareas/empleado/(:num)', 'Responsable\PedidosAreaController::tareasPorEmpleado/$1');
    $routes->get('archivos/vista-previa/(:num)', 'Responsable\PedidosAreaController::vistaPrevia/$1');
    $routes->post('pedidos/actualizar', 'Responsable\PedidosAreaController::actualizarRequerimiento');

    // NUEVAS RUTAS PARA EL RESPONSABLE (Similares a Empleado)
    $routes->get('historial', 'Responsable\PedidosAreaController::historial');
    $routes->get('retroalimentacion', 'Responsable\PedidosAreaController::vistaRetroalimentacion');
    $routes->post('pedido-iniciar/(:num)', 'Responsable\PedidosAreaController::iniciarPedido/$1');
    $routes->post('pedido-entregar/(:num)', 'Responsable\PedidosAreaController::entregarPedido/$1');

    // Miembros del equipo
    $routes->get('equipo/miembro/(:num)', 'Responsable\EquipoController::detalleMiembro/$1');
});

//Rutas para el Empleado
$routes->group('empleado', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Empleado\MisPedidosController::dashboard');
    $routes->get('mis_pedidos', 'Empleado\MisPedidosController::index');
    $routes->get('historial', 'Empleado\MisPedidosController::historial');
    $routes->post('pedido-iniciar/(:num)', 'Empleado\MisPedidosController::iniciarPedido/$1');
    $routes->post('pedido-entregar/(:num)', 'Empleado\MisPedidosController::entregarPedido/$1');
    $routes->get('pedido-detalle/(:num)', 'Empleado\MisPedidosController::detalle/$1');
});

//Rutas para el Cliente
$routes->group('cliente', ['filter' => 'auth'], function ($routes) {
    //Plantilla
    $routes->get('mis_solicitudes', 'Cliente\MisPedidosController::index');
    // API / Datos
    $routes->get('pedidos/listar', 'Cliente\MisPedidosController::listar');
    $routes->get('nuevo-pedido/servicios', 'Cliente\MisPedidosController::servicios');
    //Prueba de Registro de Requerimiento (Servicio_Personalizado) Por Ahora sin Carga de Archivos
    $routes->post('requerimiento/guardar', 'Cliente\RequerimientoController::guardar');
    // Ruta para obtener el JSON con toda la info (EndPoint)
    $routes->get('requerimiento/detalle/(:num)', 'Cliente\RequerimientoController::detalle/$1');
    // Ruta para la Pagina de Visualizacion del Requerimiento
    $routes->get('detalle_requerimiento/(:num)', 'Cliente\RequerimientoController::vistaDetalle/$1');
    //Ruta Para Guardar el Requerimiento (Vista + Backend Logica)
    $routes->post('requerimiento/guardar', 'Cliente\RequerimientoController::guardar');
    // Ruta especial para VER los archivos desde la Vista Detalle
    $routes->get('archivos/(:segment)', 'Cliente\RequerimientoController::verArchivo/$1');
    // Ruta para ver notificaciones (Vista)
    $routes->get('notificaciones', 'Cliente\TrackingController::notificaciones');
    // Ruta para obtener notificaciones en JSON (AJAX)
    $routes->get('notificaciones-json', 'Cliente\TrackingController::notificacionesJson');
    // Ruta para Seguimiento de un Requerimiento (Específico) / Endpoint
    $routes->get('requerimiento/seguimiento/(:num)', 'Cliente\TrackingController::seguimiento/$1');
    //Vista para el Seguimiento del Requerimiento
    $routes->get('seguimiento/(:num)', 'Cliente\TrackingController::vistaSeguimiento/$1');
});