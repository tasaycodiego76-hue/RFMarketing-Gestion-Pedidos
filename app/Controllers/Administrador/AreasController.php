<?php

  namespace App\Controllers\Administrador;

  use CodeIgniter\Controller;
  use App\Models\AreasAgenciaModel;

  class AreasController extends Controller
  {
      public function index(): string
      {
          return view('admin/areas', [
              'titulo'       => 'Áreas',
              'tituloPagina' => 'ÁREAS',
              'paginaActual' => 'areas',
          ]);
      }

      public function listar(): \CodeIgniter\HTTP\ResponseInterface
      {
          $model = new AreasAgenciaModel();
          $areas = $model->listarConResponsable();

          foreach ($areas as &$a) {
              $a['activo'] = ($a['activo'] === true || $a['activo'] === 't' || $a['activo'] == 1) ? 1 : 0;
          }

          return $this->response->setJSON($areas);
      }

      public function registrar(): \CodeIgniter\HTTP\ResponseInterface
      {
          $json   = $this->request->getJSON(true);
          $model  = new AreasAgenciaModel();

          $model->insert([
              'nombre'      => $json['nombre'],
              'descripcion' => $json['descripcion'] ?? null,
              'activo'      => true,
          ]);

          return $this->response->setJSON(['success' => true, 'message' => 'Área registrada']);
      }

      public function obtener($id): \CodeIgniter\HTTP\ResponseInterface
      {
          $model = new AreasAgenciaModel();
          $area = $model->find($id);

          if (!$area) {
              return $this->response->setJSON(['success' => false, 'message' => 'Área no encontrada']);
          }

          return $this->response->setJSON($area);
      }

      public function editar($id): \CodeIgniter\HTTP\ResponseInterface
      {
          $json  = $this->request->getJSON(true);
          $model = new AreasAgenciaModel();

          $model->update($id, [
              'nombre'      => $json['nombre'],
              'descripcion' => $json['cripcion'] ?? null,
          ]);

          return $this->response->setJSON(['success' => true, 'message' => 'Área actualizada']);
      }

      public function toggleEstado(): \CodeIgniter\HTTP\ResponseInterface
      {
          $json  = $this->request->getJSON(true);
          $model = new AreasAgenciaModel();

          $model->update($json['id'], ['activo' => (bool) $json['estado']]);

          $msg = $json['estado'] ? 'habilitada' : 'deshabilitada';
          return $this->response->setJSON(['success' => true, 'message' => "Área $msg"]);
      }
  }