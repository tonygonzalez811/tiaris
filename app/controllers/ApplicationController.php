<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 6/24/2015
 * Time: 12:06 PM
 */

class ApplicationController extends Controller {

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function instalar() {
        Application::instalar();

        return Redirect::route('inicio_sesion')->with(array(
            'msg' => 'Instalación completada.'
        ));
    }

    /**
     * Muestra la página por defecto
     * @return mixed
     */
    public static function mostrarDefault() {
        if (Auth::check()) {
            return UserController::paginaAdminInicio();
        }
        return self::mostrarInicioSesion();
    }

    /**
     * Muestra el formulario de inicio de sesión
     * @return mixed
     */
    public static function mostrarInicioSesion() {
        return View::make('formulario_inicio');
    }
}