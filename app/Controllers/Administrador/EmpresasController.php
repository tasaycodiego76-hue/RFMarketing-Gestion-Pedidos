<?php

  namespace App\Controllers\Administrador;

  use CodeIgniter\Controller;
  use App\Models\EmpresaModel;

  class EmpresasController extends Controller
  {
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

      public function listar()
      {
          $model = new EmpresaModel();
          $empresas = $model->findAll();

          foreach ($empresas as &$e) {
              $e['estado'] = ($e['estado'] === true || $e['estado'] === 't' || $e['estado'] == 1) ? 1 : 0;
          }

          return $this->response->setJSON($empresas);
      }

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

      public function obtener($id)
      {
          $model = new EmpresaModel();
          $empresa = $model->find($id);

          if (!$empresa) {
              return $this->response->setJSON(['success' => false, 'message' => 'No encontrada']);
          }

          return $this->response->setJSON($empresa);
      }

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

  public function toggleEstado()
{
    $model = new EmpresaModel();
    $datos = $this->request->getJSON(true);

    // Forzamos que el valor sea booleano (true o false)
    $nuevoEstado = filter_var($datos['estado'], FILTER_VALIDATE_BOOLEAN);

    $model->update($datos['id'], ['estado' => $nuevoEstado]);

    $msg = $nuevoEstado ? 'habilitada' : 'deshabilitada';
    return $this->response->setJSON(['success' => true, 'message' => "Empresa $msg"]);
}
  }