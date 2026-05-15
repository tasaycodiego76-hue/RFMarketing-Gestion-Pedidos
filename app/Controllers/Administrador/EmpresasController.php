<?php

  namespace App\Controllers\Administrador;

  use CodeIgniter\Controller;
  use App\Models\EmpresaModel;
  use App\Models\AreasModel;

  class EmpresasController extends Controller
  {
      /**
       *  Muestra la vista principal de empresas activas
       * @return string
       */
      public function index(): string
      {
          $model = new EmpresaModel();
          $data = [
              'titulo'       => 'Gestionar Empresas',
              'tituloPagina' => 'ADMINISTRACIÓN DE EMPRESAS CLIENTE',
              'paginaActual' => 'empresas',
              'empresas'     => $model->orderBy('nombreempresa', 'ASC')->findAll()
          ];

          return view('admin/empresas', $data);
      }

      /**
       * Lista todas las empresas en formato JSON
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function listar()
      {
          $model = new EmpresaModel();
          $empresas = $model->orderBy('nombreempresa', 'ASC')->findAll();
          
          // Normalizar estados para el frontend
          foreach ($empresas as &$e) {
              $e['estado'] = ($e['estado'] === true || $e['estado'] === 't' || $e['estado'] == 1);
          }
          
          return $this->response->setJSON($empresas);
      }

      /**
       * Valida que el nombre no esté vacío y que el RUC no esté duplicado
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function registrar()
      {
          $model = new EmpresaModel();
          $datos = $this->request->getJSON(true);

          if (empty($datos['nombreempresa'])) {
              return $this->response->setJSON(['success' => false, 'message' => 'El nombre es requerido']);
          }

          if ($model->where('ruc', $datos['ruc'])->first()) {
              return $this->response->setJSON(['success' => false, 'message' => 'El RUC ya está registrado']);
          }

          $id = $model->insert([
              'nombreempresa' => $datos['nombreempresa'],
              'ruc'           => $datos['ruc'] ?? '',
              'correo'        => $datos['correo'] ?? '',
              'telefono'      => $datos['telefono'] ?? '',
              'estado'        => true,
          ], true);

          if (!$id) {
              return $this->response->setJSON(['success' => false, 'message' => 'Error al registrar']);
          }

          return $this->response->setJSON(['success' => true, 'message' => 'Empresa registrada']);
      }

      /**
       * Retorna los datos de una empresa por su ID
       * @param mixed $id ID de la empresa
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function obtener($id)
      {
          $model = new EmpresaModel();
          $empresa = $model->find($id);

          if (!$empresa) {
              return $this->response->setJSON(['success' => false, 'message' => 'No encontrada']);
          }
          
          $empresa['estado'] = ($empresa['estado'] === true || $empresa['estado'] === 't' || $empresa['estado'] == 1);

          return $this->response->setJSON($empresa);
      }

      /**
       * Actualiza los datos de una empresa
       * @param mixed $id ID de la empresa
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function editar($id)
      {
          $model = new EmpresaModel();
          $datos = $this->request->getJSON(true);

          if ($model->where('ruc', $datos['ruc'])->where('id !=', $id)->first()) {
              return $this->response->setJSON(['success' => false, 'message' => 'El RUC ya está registrado']);
          }

          $actualizado = $model->update($id, [
              'nombreempresa' => $datos['nombreempresa'],
              'ruc'           => $datos['ruc'] ?? '',
              'correo'        => $datos['correo'] ?? '',
              'telefono'      => $datos['telefono'] ?? '',
          ]);

          if (!$actualizado) {
              return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar']);
          }

          return $this->response->setJSON(['success' => true, 'message' => 'Empresa actualizada']);
      }

      /**
       * Activa o desactiva una empresa y sus áreas/responsables vinculados
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function toggleEstado()
      {
          $model = new EmpresaModel();
          $datos = $this->request->getJSON(true);
          $id = $datos['id'] ?? null;
          $nuevoEstado = isset($datos['estado']) ? (bool)$datos['estado'] : null;

          if ($id === null || $nuevoEstado === null) {
              return $this->response->setJSON(['success' => false, 'message' => 'Datos insuficientes']);
          }

          // Iniciar transacción para asegurar consistencia
          $db = \Config\Database::connect();
          $db->transStart();

          // 1. Cambiar estado de la empresa
          $model->update($id, ['estado' => $nuevoEstado]);

          // 2. Cambiar estado de todas sus ÁREAS
          $db->table('areas')->where('idempresa', $id)->update(['activo' => $nuevoEstado]);

          // 3. Cambiar estado de todos sus RESPONSABLES (Usuarios de rol cliente en esas áreas)
          $db->table('usuarios')
             ->whereIn('idarea', function($builder) use ($id) {
                 return $builder->select('id')->from('areas')->where('idempresa', $id);
             })
             ->update(['estado' => $nuevoEstado]);

          $db->transComplete();

          if ($db->transStatus() === true) {
              $msg = $nuevoEstado ? 'activada' : 'desactivada';
              return $this->response->setJSON([
                  'success' => true,
                  'message' => "Empresa $msg con sus áreas y responsables vinculados."
              ]);
          }

          return $this->response->setJSON([
              'success' => false,
              'message' => 'Error al cambiar el estado de la empresa.'
          ]);
      }

      /**
       * Registra una nueva área vinculada a una empresa
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function registrarArea()
      {
          $areasModel = new AreasModel();
          $datos = $this->request->getJSON(true);

          if (empty($datos['idempresa']) || empty($datos['nombre'])) {
              return $this->response->setJSON(['success' => false, 'message' => 'Empresa y nombre de área son requeridos']);
          }

          // Verificar si el área ya existe para esta empresa
          $existe = $areasModel->where('idempresa', $datos['idempresa'])
                               ->where('LOWER(nombre)', mb_strtolower(trim($datos['nombre'])))
                               ->first();
          if ($existe) {
              return $this->response->setJSON(['success' => false, 'message' => 'Esta empresa ya tiene un área con ese nombre']);
          }

          $id = $areasModel->insert([
              'idempresa'   => $datos['idempresa'],
              'nombre'      => trim($datos['nombre']),
              'descripcion' => $datos['descripcion'] ?? '',
              'activo'      => true
          ]);

          if (!$id) {
              return $this->response->setJSON(['success' => false, 'message' => 'Error al registrar el área']);
          }

          return $this->response->setJSON(['success' => true, 'message' => 'Área registrada correctamente']);
      }
  }