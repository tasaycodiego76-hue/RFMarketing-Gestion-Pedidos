<?php

namespace App\Models;

use CodeIgniter\Model;

class RequerimientoModel extends Model
{
    protected $table = 'requerimiento';
    protected $primaryKey = 'id';
    // Definimos qué columnas se pueden devolver o manipular
    protected $allowedFields = [
        'idempresa',
        'idservicio',
        'servicio_personalizado',
        'titulo',
        'objetivo_comunicacion',
        'descripcion',
        'tipo_requerimiento',
        'canales_difusion',
        'publico_objetivo',
        'tiene_materiales',
        'formatos_solicitados',
        'formato_otros',
        'fecharequerida',
        'prioridad'
    ];
}