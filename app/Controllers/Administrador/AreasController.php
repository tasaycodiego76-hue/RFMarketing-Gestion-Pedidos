<?php

  namespace App\Controllers\Administrador;

  use CodeIgniter\Controller;
  use App\Models\AreasAgenciaModel;
  use App\Models\EmpresaModel;

  class AreasController extends Controller
  {
      /**
       * Muestra la vista principal de áreas de la agencia
       * @return string
       */
      public function index(): string
      {
          $empresaModel = new EmpresaModel();

          return view('admin/areas', [
              'titulo'       => 'Áreas',
              'tituloPagina' => 'ÁREAS',
              'paginaActual' => 'areas',
              'empresas'     => $empresaModel->findAll(),
          ]);
      }

      /**
       * Retorna todas las áreas con su responsable en formato JSON
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function listar(): \CodeIgniter\HTTP\ResponseInterface
      {
          $model = new AreasAgenciaModel();
          $areas = $model->listarConResponsable();

          foreach ($areas as &$a) {
              $a['activo'] = ($a['activo'] === true || $a['activo'] === 't' || $a['activo'] == 1) ? 1 : 0;
          }

          return $this->response->setJSON($areas);
      }

      /**
       * Registra una nueva área desde JSON.
       * La creación de servicios está desacoplada de este flujo.
       * Los servicios se sincronizan al registrar/editar empleados por área.
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function registrar(): \CodeIgniter\HTTP\ResponseInterface
      {
          $json   = $this->request->getJSON(true);
          $db     = \Config\Database::connect();
          $model  = new AreasAgenciaModel();

          // Iniciar transacción para garantizar integridad de datos
          $db->transStart();

          try {
              // 1. Insertar en areas_agencia
              $model->insert([
                  'nombre'      => $json['nombre'],
                  'descripcion' => $json['descripcion'] ?? null,
                  'activo'      => true,
              ]);

              // Confirmar transacción
              $db->transComplete();

              if ($db->transStatus() === false) {
                  return $this->response->setJSON([
                      'success' => false,
                      'message' => 'Error al registrar el área. La transacción falló.'
                  ]);
              }

              return $this->response->setJSON(['success' => true, 'message' => 'Área registrada']);

          } catch (\Exception $e) {
              // Revertir transacción en caso de error
              $db->transRollback();

              return $this->response->setJSON([
                  'success' => false,
                  'message' => 'Error al registrar: ' . $e->getMessage()
              ]);
          }
      }

      /**
       * Retorna los datos de un área por su ID
       * @param mixed $id ID del área
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function obtener($id): \CodeIgniter\HTTP\ResponseInterface
      {
          $model = new AreasAgenciaModel();
          $area = $model->find($id);

          if (!$area) {
              return $this->response->setJSON(['success' => false, 'message' => 'Área no encontrada']);
          }

          return $this->response->setJSON($area);
      }

      /**
       * Actualiza los datos de un área existente
       * @param mixed $id ID del área a editar
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function editar($id): \CodeIgniter\HTTP\ResponseInterface
      {
          $json  = $this->request->getJSON(true);
          $model = new AreasAgenciaModel();

          $model->update($id, [
              'nombre'      => $json['nombre'],
              'descripcion' => $json['descripcion'] ?? null,
          ]);

          return $this->response->setJSON(['success' => true, 'message' => 'Área actualizada']);
      }

      /**
       * Habilita o deshabilita un área según el estado recibido
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function toggleEstado(): \CodeIgniter\HTTP\ResponseInterface
      {
          $json  = $this->request->getJSON(true);
          $model = new AreasAgenciaModel();
          $db = \Config\Database::connect();

          $idArea = $json['id'];
          $nuevoEstado = (bool) $json['estado'];

          $db->transStart();

          // 1. Actualizar estado del área de agencia
          $model->update($idArea, ['activo' => $nuevoEstado]);

          if (!$nuevoEstado) {
              // 2. Desactivar a los usuarios (empleados) de esa área
              $db->table('usuarios')
                 ->where('idarea_agencia', $idArea)
                 ->update(['estado' => false]);

              // 3. Desactivar el servicio vinculado (si existe)
              $area = $model->find($idArea);
              if ($area) {
                  $db->table('servicios')
                     ->where('LOWER(nombre)', mb_strtolower($area['nombre']))
                     ->update(['activo' => false]);
              }
          } else {
              // 2. Habilitar a los usuarios (empleados) de esa área
              $db->table('usuarios')
                 ->where('idarea_agencia', $idArea)
                 ->update(['estado' => true]);

              // 3. Habilitar el servicio vinculado (si existe)
              $area = $model->find($idArea);
              if ($area) {
                  $db->table('servicios')
                     ->where('LOWER(nombre)', mb_strtolower($area['nombre']))
                     ->update(['activo' => true]);
              }
          }

          $db->transComplete();

          $msg = $nuevoEstado ? 'habilitada' : 'deshabilitada';
          return $this->response->setJSON([
              'success' => true, 
              'message' => "Área $msg. Los servicios y empleados vinculados también han sido actualizados."
          ]);
      }
  }