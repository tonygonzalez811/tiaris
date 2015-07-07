<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 04/07/2015
 * Time: 11:23 PM
 */

class EspecialidadController extends BaseController {

    const PAGE_LIMIT = 5;

    const MODEL = 'Especialidad';

    const LANG_FILE = 'especialidad';

    const TITLE_FIELD = 'nombre';

    /** Navegacion **/

    /**
     * Muestra la página de administración de Pacientes
     * @return mixed
     */
    public function paginaAdmin() {
        if (User::canAdminModalidad()) {
            $total = $this->getTotalItems();
            return View::make('admin.especialidad')->with(
                array(
                    'active_menu' => 'especialidad',
                    'total' => $total
                )
            );
        }
        return View::make('admin.inicio');
    }


    /**
     * This function will be called after the model validation has passed successfully
     * @param $inputs
     * @return boolean
     */
    public function afterValidation($inputs) {
        return true;
    }

    /**
     * Proceso adicional al editar / crear un nuevo registro
     * @param $item
     * @return bool
     */
    public function editarRelational($item) {
        return true;
    }

    /**
     * Datos adicionales que se envian al solicitar la información del registro para editar
     * @param $item
     */
    public function additionalData($item) {

    }

    /**
     * Código HTML que se envía al solicitar la información del registro para visualizar
     * @param $item
     * @return string
     */
    public function outputInf($item) {
        $frm = new AForm;
        $output = "";
        $output .= $frm->id( $item->id );
        $output .= $frm->hidden('action');

        $output .= $frm->view('nombre', Lang::get(self::LANG_FILE . '.name'), $item->nombre);
        if (!empty($item->descripcion)) {
            $output .= $frm->view('descripcion', Lang::get(self::LANG_FILE . '.description'), $item->descripcion);
        }
        $output .= $frm->view('total', Lang::get('global.total') . ' ' . Lang::get('usuarios.doctors'), $item->doctores->count());

        $output .= $frm->controlButtons();

        return $output;
    }

    /**
     * Código HTML que se envía al realizar una búsqueda
     * @param $records
     * @param $search_fields
     * @return string
     */
    public function buscarReturnHtml($records, $search_fields) {
        return AForm::searchResults($records, 'nombre');
    }

}