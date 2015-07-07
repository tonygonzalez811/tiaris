<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 4/05/2015
 * Time: 07:26 PM
 */

class LogController extends BaseController {

    const PAGE_LIMIT = 5;

    const MODEL = 'ActionLog';

    const LANG_FILE = 'log';

    const TITLE_FIELD = 'updated_at';

    /** Navegacion **/

    /**
     * Muestra la página de log
     * @return mixed
     */
    public function paginaAdmin() {
        if (User::canSeeNotifications()) {
            $total = $this->getTotalItems();
            return View::make('admin.log')->with(array(
                'total' => $total
            ));
        }
        return Redirect::route('inicio');
    }

    public function paginaAdminItem($id) {
        return Redirect::route('admin_log')->with(array('id' => $id));
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

        $user = User::find($item->usuario_id);
        if ($user) {
            $user_name = $user->nombre;
            $user = $user->paciente;
            if ($user) {
                $user_name = Functions::firstNameLastName($user->nombre, $user->apellido);
            }
        }
        else {
            $user_name = Lang::get('log.the_user');
        }

        //left panel
        $output .= $frm->halfPanelOpen(true);
            $output .= $frm->view('record_id', Lang::get(self::LANG_FILE . '.record_number'), $item->id);
            $output .= $frm->view('action', Lang::get(self::LANG_FILE . '.action'), $user_name . ' ' . Lang::get(self::LANG_FILE . '.' . $item->accion));
            $output .= $frm->view('date', Lang::get(self::LANG_FILE . '.date'), Functions::longDateFormat($item->updated_at, true));
        $output .= $frm->halfPanelClose();

        //right panel
        $output .= $frm->halfPanelOpen(false, 6, 'text-center');
            if (!empty($item->objeto)) {
                $output .= '<div class="well"><pre>' . $this->keyVal(unserialize($item->objeto), $item->accion) . '</pre></div>';
            }
        $output .= $frm->halfPanelClose(true);

        return $output;
    }

    public function searchByFields($date_from = '', $date_to = '', $usuario_id = 0) {
        $model = self::MODEL;
        $records = new $model;

        $order = 'DESC';

        $records = $records->orderBy('updated_at', $order)->take(100);

        //if the date from is specified
        if (strlen($date_from) > 0) {
            $records = $records->where('updated_at', '>=', $date_from);
        }
        //if the date to is specified
        if (strlen($date_to) > 0) {
            $records = $records->where('updated_at', '<=', $date_to . ' 23:59:59');
        }
        //if the user is specified
        if ($usuario_id > 0) {
            $records = $records->where('usuario_id', '=', $usuario_id);
        }

        $records = $records->get();

        return $records;
    }

    public function buscarGetAlt() {
        $validator = Validator::make(Input::all(),
            array(
                'search_query'      => '',
                /*'search_page'     => 'required|integer|min:1',*/
                'buscar_usuario_id' => 'integer|min:1'
            )
        );
        if ($validator->passes()) {
            $query  = Input::get('search_query');
            //$page   = Input::get('search_page');
            $date_to = Input::get('search_date_to');
            $usuario_id = (int)Input::get('buscar_usuario_id');

            if (strlen(trim($query)) > 0 || $usuario_id > 0 || strlen(trim($date_to)) > 0) {
                $records = $this->searchByFields($query, $date_to, $usuario_id);
            }
            else {
                $records = array();
            }

            $total = count($records);
            $match_total = $total;

            $this->setReturn('total', $match_total);
            $this->setReturn('total_page', $total);
            $this->setReturn('results', $this->buscarReturnHtml($records, array()));
            return $this->returnJson();
        }
        return $this->setError( Lang::get('global.wrong_action') );
    }

    /**
     * Código HTML que se envía al realizar una búsqueda
     * @param $records
     * @param $search_fields
     * @return string
     */
    public function buscarReturnHtml($records, $search_fields) {
        //return AForm::searchResults($records, 'updated_at');
        $output = "";

        foreach ($records as $result) {
            $row = Lang::get('log.the_user') . ' ' . Lang::get('log.' . $result->accion);
            $badge_lbl = Functions::longDateFormat($result->updated_at, true);
            $id = $result->id;
            $output.= <<<EOT
                <a class="list-group-item search-result" data-id="{$id}">{$row}<br><span class="text-muted">{$badge_lbl}</span></a>
EOT;
        }

        return $output;
    }


    private function keyVal($arr, $action) {
        $action = explode(' ', $action);
        $output = '';

        $model = trim(reset($action));
        if (class_exists($model)) {
            //$fields = (new $model)->getFillable();
            foreach ($arr as $field => $val) { //foreach ($fields as $field) {
                //if (!isset($arr[$field])) continue;
                $key = explode('_', $field);
                $is_id = next($key) == 'id';
                $key = reset($key);
                if ($is_id) {
                    $class = ucfirst($key);
                    if ($class == 'Doctor') $class = 'User';
                    if (class_exists($class)) {
                        $obj = $class::find( (int)$arr[$field] );
                        if ($obj) {
                            $class_controller = $class . 'Controller';
                            $main_field = class_exists($class_controller) ? $class_controller::TITLE_FIELD : 'id';
                            $output .= '<b>' . $key . ':</b> ' . $obj->$main_field . '<br>';
                        }
                    }
                }
                else {
                    if ($model == 'Cita' && $key == 'estado') {
                        $output .= '<b>' . $field . ':</b> ' . Cita::state($arr[$field]) . '<br>';
                    }
                    else {
                        $output .= '<b>' . $field . ':</b> ' . $arr[$field] . '<br>';
                    }
                }
            }
        }

        return $output;
    }

}