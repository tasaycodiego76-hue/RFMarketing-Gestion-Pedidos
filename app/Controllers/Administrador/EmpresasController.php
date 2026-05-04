<?php

  namespace App\Controllers\Administrador;

  use CodeIgniter\Controller;
  use App\Models\EmpresaModel;

  class EmpresasController extends Controller
  {
      /**
       *  Muestra la vista principal de empresas activas
       * @return string
       */
      public function index(): string
      {
          $empresaModel = new EmpresaModel();

          return view('admin/empresas', [
              'titulo'       => 'Empresas',
              'tituloPagina' => 'EMPRESAS',
              'paginaActual' => 'empresas',
              'empresas' => $empresaModel->where('estado', true)->findAll(),
          ]);
      }

      /**
       *  Retorna todas las empresas en formato JSON
       * Normaliza el campo estado a 1 o 0
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function listar()
      {
          $model = new EmpresaModel();
          $empresas = $model->findAll();

          foreach ($empresas as &$e) {
              $e['estado'] = ($e['estado'] === true || $e['estado'] === 't' || $e['estado'] == 1) ? 1 : 0;
          }

          return $this->response->setJSON($empresas);
      }

      /**
       * Registra una nueva empresa desde JSON
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

          return $this->response->setJSON($empresa);
      }

      /**
       * Actualiza los datos de una empresa existente
       * Valida que el nombre no esté vacío y que el RUC no esté en uso por otra empresa
       * @param mixed $id ID de la empresa a editar
       * @return \CodeIgniter\HTTP\ResponseInterface
       */
      public function editar($id)
      {
          $model = new EmpresaModel();
          $datos = $this->request->getJSON(true);

          if (empty($datos['nombreempresa'])) {
              return $this->response->setJSON(['success' => false, 'message' => 'El nombre es requerido']);
          }

          $existente = $model->where('ruc', $datos['ruc'])->where('id !=', $id)->first();
          if ($existente) {
              return $this->response->setJSON(['success' => false, 'message' => 'RUC en uso']);
          }

          $model->update($id, [
              'nombreempresa' => $datos['nombreempresa'],
              'ruc'           => $datos['ruc'] ?? '',
              'correo'        => $datos['correo'] ?? '',
              'telefono'      => $datos['telefono'] ?? '',
          ]);

          return $this->response->setJSON(['success' => true, 'message' => 'Actualizada']);
      }

  /**
   * Habilita o deshabilita una empresa según el estado recibido
   * @return \CodeIgniter\HTTP\ResponseInterface
   */
  public function toggleEstado()
  {
      $model = new EmpresaModel();
      $datos = $this->request->getJSON(true);
      $db = \Config\Database::connect();

      // Forzamos que el valor sea booleano (true o false)
      $nuevoEstado = filter_var($datos['estado'], FILTER_VALIDATE_BOOLEAN);

      $db->transStart();
      
      // 1. Actualizar estado de la empresa
      $model->update($datos['id'], ['estado' => $nuevoEstado]);

      // 2. Si se desactiva la empresa, desactivamos a sus responsables (usuarios clientes)
      if (!$nuevoEstado) {
          $db->table('usuarios u')
             ->join('areas a', 'a.id = u.idarea')
             ->where('a.idempresa', $datos['id'])
             ->update(['estado' => false]);
      }

      $db->transComplete();

      $msg = $nuevoEstado ? 'habilitada' : 'deshabilitada';
      return $this->response->setJSON(['success' => true, 'message' => "Empresa $msg con sus áreas y responsables vinculados."]);
  }
  }