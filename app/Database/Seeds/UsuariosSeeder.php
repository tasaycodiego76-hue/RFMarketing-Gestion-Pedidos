<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    public function run()
    {
        $this->db->query("TRUNCATE TABLE usuarios RESTART IDENTITY CASCADE");

        // Buscar IDs reales de áreas para los clientes
        $areaUAI = $this->db->table('areas')
            ->select('areas.id')
            ->where('areas.nombre', 'BIBLIOTECA')
            ->join('empresas', 'empresas.id = areas.idempresa')
            ->where('empresas.nombreempresa', 'UNIVERSIDAD AUTÓNOMA DE ICA')
            ->get()->getRow();
        $idAreaUAI = $areaUAI ? $areaUAI->id : 1;

        $areaByron = $this->db->table('areas')
            ->select('areas.id')
            ->where('areas.nombre', 'ATENCIÓN AL CLIENTE')
            ->join('empresas', 'empresas.id = areas.idempresa')
            ->where('empresas.nombreempresa', 'COLEGIO ADA BYRON')
            ->get()->getRow();
        $idAreaByron = $areaByron ? $areaByron->id : 2;

        $data = [
            // ID 1: ADMIN
            [
                'nombre' => 'ADMIN',
                'apellidos' => 'SUPER ADMIN',
                'correo' => 'rodrigofelix.fotografo@gmail.com',
                'telefono' => '931826325',
                'tipodoc' => 'DNI',
                'numerodoc' => '75843912',
                'usuario' => 'administrador',
                'clave' => password_hash('rf_admin', PASSWORD_DEFAULT),
                'rol' => 'administrador',
                'idarea_agencia' => null,
                'idarea' => null,
                'esresponsable' => false,
                'estado' => true,
            ],
            // ID 2: RODRIGO (Fotografía, responsable)
            [
                'nombre' => 'RODRIGO ALEXANDER',
                'apellidos' => 'FELIX HUAMAN',
                'correo' => 'rodrigofelix.fotografo@gmail.com',
                'telefono' => '931826325',
                'tipodoc' => 'DNI',
                'numerodoc' => '75843912',
                'usuario' => 'rfelix_rf',
                'clave' => password_hash('rf_75843912', PASSWORD_DEFAULT),
                'rol' => 'empleado',
                'idarea_agencia' => 4,
                'idarea' => null,
                'esresponsable' => true,
                'estado' => true,
            ],
            // ID 3: NOEMI (Creación de Contenido, responsable)
            [
                'nombre' => 'NOEMI',
                'apellidos' => 'TORRES TINEDO',
                'correo' => '16noemitorres@gmail.com',
                'telefono' => '963604801',
                'tipodoc' => 'DNI',
                'numerodoc' => '76331600',
                'usuario' => 'ntorres_rf',
                'clave' => password_hash('rf_76331600', PASSWORD_DEFAULT),
                'rol' => 'empleado',
                'idarea_agencia' => 3,
                'idarea' => null,
                'esresponsable' => true,
                'estado' => true,
            ],
            // ID 4: MARIA (Creación de Contenido)
            [
                'nombre' => 'MARIA',
                'apellidos' => 'PIA CASTILLA',
                'correo' => 'piacaslhi@gmail.com',
                'telefono' => '912902965',
                'tipodoc' => 'DNI',
                'numerodoc' => '73245532',
                'usuario' => 'mpia_rf',
                'clave' => password_hash('rf_73245532', PASSWORD_DEFAULT),
                'rol' => 'empleado',
                'idarea_agencia' => 3,
                'idarea' => null,
                'esresponsable' => false,
                'estado' => true,
            ],
            // ID 5: JHENINFER (Creación de Contenido)
            [
                'nombre' => 'JHENINFER MIRELLI',
                'apellidos' => 'CCOICCA ALVAREZ',
                'correo' => 'mirelli@gmail.com',
                'telefono' => '958008832',
                'tipodoc' => 'DNI',
                'numerodoc' => '75491896',
                'usuario' => 'jalvarez_rf',
                'clave' => password_hash('rf_75491896', PASSWORD_DEFAULT),
                'rol' => 'empleado',
                'idarea_agencia' => 3,
                'idarea' => null,
                'esresponsable' => false,
                'estado' => true,
            ],
            // ID 6: NICOL (Creación de Contenido)
            [
                'nombre' => 'NICOL MICHELLE',
                'apellidos' => 'GUERRERO TORREALBA',
                'correo' => 'michelle@gmail.com',
                'telefono' => '927704167',
                'tipodoc' => 'CE',
                'numerodoc' => '007524370',
                'usuario' => 'nguerrero_rf',
                'clave' => password_hash('rf_007524370', PASSWORD_DEFAULT),
                'rol' => 'empleado',
                'idarea_agencia' => 3,
                'idarea' => null,
                'esresponsable' => false,
                'estado' => true,
            ],
            // ID 7: SONIA (Edición y Postproducción)
            [
                'nombre' => 'SONIA ALEJANDRA',
                'apellidos' => 'TELLO ROJAS',
                'correo' => 'satrsonia@gmail.com',
                'telefono' => '959125670',
                'tipodoc' => 'DNI',
                'numerodoc' => '49049820',
                'usuario' => 'stello_rf',
                'clave' => password_hash('rf_49049820', PASSWORD_DEFAULT),
                'rol' => 'empleado',
                'idarea_agencia' => 2,
                'idarea' => null,
                'esresponsable' => false,
                'estado' => true,
            ],
            // ID 8: JOSE (Edición y Postproducción)
            [
                'nombre' => 'JOSE',
                'apellidos' => 'GUERRA CHACÓN',
                'correo' => 'isaichachon695@gmail.com',
                'telefono' => '960733818',
                'tipodoc' => 'DNI',
                'numerodoc' => '75187044',
                'usuario' => 'jguerra_rf',
                'clave' => password_hash('rf_75187044', PASSWORD_DEFAULT),
                'rol' => 'empleado',
                'idarea_agencia' => 2,
                'idarea' => null,
                'esresponsable' => false,
                'estado' => true,
            ],
            // ID 9: JONATHAN (Edición y Postproducción, responsable)
            [
                'nombre' => 'JONATHAN',
                'apellidos' => 'MEDINA CAMPOS',
                'correo' => 'paratodoslados.2025@gmail.com',
                'telefono' => '979127328',
                'tipodoc' => 'DNI',
                'numerodoc' => '72324643',
                'usuario' => 'jmedina_rf',
                'clave' => password_hash('rf_72324643', PASSWORD_DEFAULT),
                'rol' => 'empleado',
                'idarea_agencia' => 2,
                'idarea' => null,
                'esresponsable' => true,
                'estado' => true,
            ],
            // ID 10: FABRIZIO (Diseño Gráfico)
            [
                'nombre' => 'FABRIZIO',
                'apellidos' => 'RAMOS TIPISMANA',
                'correo' => 'fabrz1rt13@gmail.com',
                'telefono' => '979099569',
                'tipodoc' => 'DNI',
                'numerodoc' => '75832420',
                'usuario' => 'framos_rf',
                'clave' => password_hash('rf_75832420', PASSWORD_DEFAULT),
                'rol' => 'empleado',
                'idarea_agencia' => 1,
                'idarea' => null,
                'esresponsable' => false,
                'estado' => true,
            ],
            // ID 11: NAYRU (Diseño Gráfico)
            [
                'nombre' => 'NAYRU',
                'apellidos' => 'GOMEZ MAGALLANES',
                'correo' => 'magallanesnayru@gmail.com',
                'telefono' => '902009682',
                'tipodoc' => 'DNI',
                'numerodoc' => '71992007',
                'usuario' => 'ngomez_rf',
                'clave' => password_hash('rf_71992007', PASSWORD_DEFAULT),
                'rol' => 'empleado',
                'idarea_agencia' => 1,
                'idarea' => null,
                'esresponsable' => false,
                'estado' => true,
            ],
            // ID 12: JESUS (Diseño Gráfico, responsable)
            [
                'nombre' => 'JESUS',
                'apellidos' => 'DE LA CRUZ GARCÍA',
                'correo' => 'gabrieljesusdelacruzgarcia@gmail.com',
                'telefono' => '937002191',
                'tipodoc' => 'DNI',
                'numerodoc' => '73009277',
                'usuario' => 'jdelacruz_rf',
                'clave' => password_hash('rf_73009277', PASSWORD_DEFAULT),
                'rol' => 'empleado',
                'idarea_agencia' => 1,
                'idarea' => null,
                'esresponsable' => true,    
                'estado' => true,
            ],
            // === CLIENTES (representantes de empresa) ===
            // ID 13: ANA (cliente de UNIVERSIDAD AUTÓNOMA DE ICA)
            [
                'nombre' => 'ANA',
                'apellidos' => 'FLORES QUISPE',
                'correo' => '62345678@uai.edu.pe',
                'telefono' => '999888777',
                'tipodoc' => 'DNI',
                'numerodoc' => '62345678',
                'usuario' => 'aflores_rf',
                'clave' => password_hash('rf_62345678', PASSWORD_DEFAULT),
                'rol' => 'cliente',
                'idarea_agencia' => null,
                'idarea' => $idAreaUAI,
                'esresponsable' => false,
                'estado' => true,
            ],
            // ID 14: LUIS (cliente de COLEGIO ADA BYRON)
            [
                'nombre' => 'LUIS',
                'apellidos' => 'MENDOZA RIVAS',
                'correo' => '62345678@byron.edu.pe',
                'telefono' => '999666333',
                'tipodoc' => 'DNI',
                'numerodoc' => '63456789',
                'usuario' => 'lmendoza_rf',
                'clave' => password_hash('rf_63456789', PASSWORD_DEFAULT),
                'rol' => 'cliente',
                'idarea_agencia' => null,
                'idarea' => $idAreaByron,
                'esresponsable' => false,
                'estado' => true,
            ],
        ];

        $this->db->table('usuarios')->insertBatch($data);
    }
}