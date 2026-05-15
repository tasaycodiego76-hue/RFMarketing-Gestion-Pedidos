<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\AtencionModel;

class NotificationController extends BaseController
{
    public function getRevisions()
    {
        $atencionModel = new AtencionModel();
        $revisions = $atencionModel->getRevisionesParaNotificaciones();

        return $this->response->setJSON([
            'status' => 'success',
            'total' => count($revisions),
            'data' => $revisions
        ]);
    }
}
