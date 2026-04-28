<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UsuarioModel;
use Config\Validation;


class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function login()
    {
        //Si hay una Session, redirigir segun el rol
        if (session()->get('usuario_id')) {
            return $this->redirigirSegunRol();
        }

        //No inicio Sesion
        return view('auth/login');
    }

    /**
     * Procesar el inicio de sesion
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function verificar()
    {
        //Middleware (Function intermedia | Mecanismo de Validacion)
        //Validacion 1: los datos de inicio de sesion deben cumplir una longitud
        $rules = [
            'usuario' => 'required|min_length[3]',
            'clave' => 'required|min_length[3]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        //Validacion 2: Ya tenemos los datos, ¿Existe el Usuario?, ¿Su Contraseña es correcta?
        $model = new UsuarioModel();
        $usuario = $model->verificarCredenciales(
            $this->request->getPost('usuario'),
            $this->request->getPost('clave')
        );
        //Ingreso usuario incorrecto
        if (!$usuario) {
            return redirect()->back()->withInput()->with('error', 'Usuario o Contraseña Incorrecta');
        }
        //Llegado a este punto, el usuario LOGRO INICIAR SESION CORRECTAMENTE
        session()->set([
            'usuario_id' => $usuario->id,
            'usuario' => $usuario->usuario,
            'rol' => $usuario->rol,
            'esresponsable' => $usuario->esresponsable,
            'logged_in' => true
        ]);

        return $this->redirigirSegunRol();
    }

    /**
     * Cerrar session
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        //Cerrar Session
        session()->destroy();
        return redirect()->to('auth/login')->with('info', 'Session Cerrada Correctamente');
    }

    /**
     * Redirigir segun el rol del usuario
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function redirigirSegunRol()
    {
        // Obtenemos los datos de la sesión
        $rol = session()->get('rol');
        $esResponsable = session()->get('esresponsable');

        // Lógica de redirección por jerarquía
        if ($rol === 'administrador') {
            return redirect()->to('/admin/dashboard');
        }

        if ($rol === 'cliente') {
            return redirect()->to('/cliente/mis_solicitudes');
        }

        if ($rol === 'empleado') {
            // Si es empleado, verificamos si además es responsable de área
            if ($esResponsable === true || $esResponsable === 't' || $esResponsable == 1) {
                return redirect()->to('/responsable/dashboard');
            }

            // Si es empleado normal
            return redirect()->to('/empleado/dashboard');
        }
        return redirect()->back()->withInput()->with('error', '');
    }
}
