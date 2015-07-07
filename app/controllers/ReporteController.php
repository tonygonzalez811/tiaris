<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 17/06/15
 * Time: 10:12 AM
 */

class ReporteController extends Controller {

    /**
     * Muestra la página de administración
     * @return mixed
     */
    public function paginaAdmin() {
        if (Auth::user()->admin) {
            return View::make('admin.reportes')->with(
                array(
                    'active_menu' => 'reportes'
                )
            );
        }
        return View::make('admin.inicio');
    }

}