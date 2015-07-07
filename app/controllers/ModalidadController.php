<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 3/10/2015
 * Time: 09:14 PM
 */

class ModalidadController extends BaseController {

    const PAGE_LIMIT = 5;

    const MODEL = 'Modalidad';

    const LANG_FILE = 'modalidad';

    const TITLE_FIELD = 'nombre';

    /** Navegacion **/

    /**
     * Muestra la página de administración de Pacientes
     * @return mixed
     */
    public function paginaAdmin() {
        if (User::canAdminModalidad()) {
            $total = $this->getTotalItems();
            return View::make('admin.modalidad')->with(
                array(
                    'active_menu' => 'modalidad',
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
        return true; //needs to return true to output json
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
        $output .= $frm->view('total', Lang::get('global.total') . ' ' . Lang::get('equipo.title_plural'), $item->equipos->count());

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


    public function getStatuses() {
        $items = DB::table('vw_modalidad_cita')->get();
        $statuses = array();
        foreach ($items as $modalidad) {
            $realizados = $modalidad->realizados;
            $pendientes = $modalidad->pendientes;
            $t = $realizados + $pendientes;
            if ($t > 0) {
                $p_realizado = (int)(($realizados / $t) * 100);
                $p_pendiente = 100 - $p_realizado;
            }
            else {
                $p_realizado = 0;
                $p_pendiente = 0;
            }
            $statuses['item_status_' . $modalidad->id] = array(
                'realizados' => $realizados,
                'pendientes' => $pendientes,
                'p_realizado' => $p_realizado,
                'p_pendiente' => $p_pendiente
            );
        }
        $statuses['ok'] = 1;
        return json_encode($statuses);
    }

}