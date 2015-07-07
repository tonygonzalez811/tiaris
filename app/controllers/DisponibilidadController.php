<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 08/03/15
 * Time: 03:58 PM
 */

class DisponibilidadController extends BaseController {

    const PAGE_LIMIT = 5;

    const MODEL = 'Disponibilidad';

    const LANG_FILE = 'disponibilidad';

    const TITLE_FIELD = 'inicio';

    /** Navegacion **/

    /**
     * Muestra la página de disponibilidad del doctor
     * @param $doctor_id
     * @return mixed
     */
    public function paginaAdminDisponibilidad($doctor_id) {
        if (User::canViewDisponibilidadState($doctor_id)) {

        }
        elseif (User::is(User::ROL_DOCTOR)) {
            $doctor_id = Auth::user()->id;
        }
        else {
            $doctor_id = 0;
        }
        $doctor = User::find($doctor_id);
        if ($doctor) {
            $doctor = $doctor->paciente;
            $options = Opcion::load();
            return View::make('admin.disponibilidad_doctor')->with(array(
                'doctor_id' => $doctor_id,
                'doctor_nombre' => $doctor ? Functions::firstNameLastName($doctor->nombre, $doctor->apellido) : Lang::get('global.not_found'),
                'read_only' => !User::canChangeDisponibilidadState($doctor_id),
                'options' => $options
            ));
        }
        return Redirect::route('inicio');
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
    public function outputInf( $item ) {
        return '';
    }

    /**
     * Código HTML que se envía al realizar una búsqueda
     * @param $records
     * @param $search_fields
     * @return string
     */
    public function buscarReturnHtml($records, $search_fields) {
        //return AForm::searchResults($records, 'inicio', array(array(Lang::get(self::LANG_FILE . '.patient'),'nombre_paciente'), array(Lang::get(self::LANG_FILE . '.doctor'),'nombre_doctor')));
        return '';
    }


    /*public function getDisponibilidad($doctor_id = 0) {
        if ($doctor_id == 0) {
            $doctor_id = (int)Input::get('doctor_id');
            if ($doctor_id == 0) return '[]';
        }
        $cal_start = strtotime(Input::get('start'));
        $cal_end = strtotime(Input::get('end'));
        //only fetch if range not larger than a week
        if ($cal_end - $cal_start > 604800000) { //604800000 = 1000 * 60 * 60 * 24 * 7
            return '[]';
        }
        $items_json = array();
        $doctor = User::find($doctor_id);
        if ($doctor) {
            $items = $doctor->disponibilidad()->fromDateToDate($cal_start, $cal_end)->get();

            $cal_start_w = date('N', $cal_start) - 1;
            if ($cal_start_w > 0) { //not a monday
                $cal_start = strtotime('-' . $cal_start_w . ' days', $cal_start);
            }
            
            $color = '#849917';//'#fff';

            foreach ($items as $item) {

                $start = strtotime($item->inicio);
                $end = strtotime($item->fin);

                $dws = date('N', $start) - 1;
                $dwe = date('N', $end) - 1;

                $start = date('Y-m-d', $dws > 0 ? strtotime('+' . $dws . ' days', $cal_start) : $start) . ' ' . date('H:i:s', $start);
                $end = date('Y-m-d', $dwe > 0 ? strtotime('+' . $dwe . ' days', $cal_start) : $end) . ' ' . date('H:i:s', $end);

                $items_json[] = <<<EOT
                {
                    "id": "{$item->id}",
                    "title": "",
                    "start": "{$start}",
                    "end": "{$end}",
                    "allDay": false,
                    "backgroundColor": "{$color}",
                    "state_id": "{$item->disponible}"
                }
EOT;
            }
        }

        return '[' . implode(',', $items_json) . ']';
    }*/


    public function getDisponibilidad($doctor_id = 0, $editable = false) {
        if ($doctor_id == 0) {
            $doctor_id = (int)Input::get('doctor_id');
            if ($doctor_id == 0) return '[]';
        }
        
        $cal_start = Input::get('start');
        $cal_end = Input::get('end');
        
        $items_json = array();
        $doctor = User::find($doctor_id);
        if ($doctor) {
            $items = $doctor->disponibilidad()->fromDateToDate($cal_start, $cal_end)->get();

            if (count($items)) {

                //using inverse background overlaps making the white not so white,
                //so I instead place a full one across and then place normal bacngrounds with color white, unless it's one day view
                if (!$editable) {
                    $items_json[] = <<<EOT
                        {
                            "start": "{$cal_start} 00:00:00",
                            "end": "{$cal_end} 00:00:00",
                            "rendering": "background"
                        }
EOT;
                }

                foreach ($items as $item) {
                    $start = $item->inicio;
                    $end = $item->fin;

                    if ($editable) {
                        $items_json[] = <<<EOT
                        {
                            "id": "{$item->id}",
                            "start": "{$start}",
                            "end": "{$end}"
                        }
EOT;
                    }
                    else {
                        $items_json[] = <<<EOT
                        {
                            "start": "{$start}",
                            "end": "{$end}",
                            "rendering": "background",
                            "backgroundColor": "#fff"
                        }
EOT;
                    }
                }
            }
            elseif (!$editable) {
                //nothing available, disable all calendar
                $items_json[] = <<<EOT
                {
                    "start": "{$cal_start} 00:00:00",
                    "end": "{$cal_end} 23:59:59",
                    "rendering": "background"
                }
EOT;
            }
        }

        return '[' . implode(',', $items_json) . ']';
    }

    public function getDisponibilidadEditable($doctor_id) {
        return $this->getDisponibilidad($doctor_id, true);
    }


    public function calendarActionPost() {
        $disponibilidad_id = (int)Input::get('disponibilidad_id');
        $action = Input::get('action');
        $val = Input::get('val');

        if ($disponibilidad_id > 0) {
            $model = self::MODEL;
            $item = $model::find($disponibilidad_id);
            if (!User::canChangeDisponibilidadState($item->usuario_id)) {
                return $this->setError(Lang::get('global.no_permission'));
            }
            switch ($action) {
                case 'set_state':
                    $val = (int)$val;
                    if ($val < 0 || $val > 1) $val = 0;
                    $item->disponible = $val;
                    $item->save();
                    $this->setReturn('disponibilidad_id', $disponibilidad_id);
                    $this->setReturn('state', $item->disponible);
                    $to_log = serialize($item->toArray());
                    ActionLog::log($model . ' changed_state', $to_log, $item->id);
                    break;
                case 'get_state':
                    $this->setReturn('disponibilidad_id', $disponibilidad_id);
                    $this->setReturn('state', $item->disponible);
                    break;
                case 'delete':
                    $to_log = serialize($item->toArray());
                    $item->delete();
                    $this->setReturn('disponibilidad_id', $disponibilidad_id);
                    $this->setReturn('state', '-1');
                    ActionLog::log($model . ' deleted', $to_log, $disponibilidad_id);
            }
        }
        return $this->returnJson();
    }


    public function duplicar($item, $fecha) {
        if ($item) {
            $date_start = strtotime($item->inicio);
            $date_end = strtotime($item->fin);
            $diff = $date_end - $date_start;

            $end_date = strtotime($fecha . ' 23:59:59');

            if ($date_start < $end_date) {
                $new_items = array();
                $next_date = strtotime('+1 week', $date_start);
                $n_items = 0;
                $times = 0;
                while ($next_date <= $end_date) {
                    $new_item = array(
                        'inicio' => date('Y-m-d H:i:s', $next_date),
                        'fin' => date('Y-m-d H:i:s', $next_date + $diff),
                        'usuario_id' => $item->usuario_id,
                        'disponible' => $item->disponible,
                        'fijo'  => $item->fijo
                    );
                    if (!$this->alreadyExists($new_item)) {
                        $new_items[] = $new_item;
                        $n_items++;
                        if ($n_items == 10) {
                            Disponibilidad::insert($new_items);
                            $times += count($new_items);
                            $new_items = array();
                            $n_items = 0;
                        }
                    }
                    $next_date = strtotime('+1 week', $next_date);
                }
                $n_items = count($new_items);
                if ($n_items) {
                    Disponibilidad::insert($new_items);
                    $times += count($new_items);
                }
                if ($times || $n_items) {
                    $to_log = serialize(array_merge($item->toArray(), array('hasta' => $fecha)));
                    ActionLog::log(self::MODEL . ' duplicated', $to_log, $item->id);
                }
                return $times;
            }
        }
        return false;
    }

    public function alreadyExists($item) {
        $model = self::MODEL;
        $items = $model::where('inicio', '=', $item['inicio'])->where('fin', '=', $item['fin'])->count();
        return ($items > 0);
    }


    public function duplicarPost() {
         $validator = Validator::make(Input::all(),
            array(
                'disponibilidad_id' => 'required|integer|min:1',
                'fecha'             => 'required|date_format:Y-m-d'
            )
        );
        if ($validator->passes()) {
            $disponibilidad_id = (int)Input::get('disponibilidad_id');
            $fecha = Input::get('fecha');

            $model = self::MODEL;
            $item = $model::find($disponibilidad_id);

            $times = $this->duplicar($item, $fecha);

            if ($times !== false) {
                return $this->setSuccess( Lang::get('disponibilidad.duplicated_msg') . ' ' . Functions::singlePlural(Lang::get('disponibilidad.times_single'), Lang::get('disponibilidad.times_plural'), $times, true) );
            }
            else {
                return $this->setError( Lang::get('global.unable_perform_action') );
            }
        }
        return $this->setError( Lang::get('global.wrong_action') );
    }

    public function duplicarSemanaPost() {
        $validator = Validator::make(Input::all(),
            array(
                'fecha'         => 'required|date_format:Y-m-d',
                'start'         => 'required|date_format:Y-m-d',
                'usuario_id'    => 'required|integer|min:1'
            )
        );
        if ($validator->passes()) {
            $times = false;

            $doctor = User::find( Input::get('usuario_id') );
            if ($doctor) {
                $fecha = Input::get('fecha');
                $cal_start = Input::get('start');
                $cal_end = date('Y-m-d', strtotime('+6 days', strtotime($cal_start)));

                $items = $doctor->disponibilidad()->fromDateToDate($cal_start, $cal_end)->get();

                foreach ($items as $item) {
                    $times = $this->duplicar($item, $fecha);
                    if ($times === false) break;
                }
            }

            if ($times !== false) {
                return $this->setSuccess( Lang::get('disponibilidad.duplicated_week_msg') . ' ' . Functions::singlePlural(Lang::get('disponibilidad.times_single'), Lang::get('disponibilidad.times_plural'), $times, true) );
            }
            else {
                return $this->setError( Lang::get('global.unable_perform_action') );
            }
        }
        return $this->setError( Lang::get('global.wrong_action') );
    }

    public function deletePost() {
        $validator = Validator::make(Input::all(),
            array(
                'start'         => 'required|date_format:Y-m-d',
                'usuario_id'    => 'required|integer|min:1',
                'all'           => 'in:0,1'
            )
        );
        if ($validator->passes()) {
            $cal_start = Input::get('start');
            $cal_end = date('Y-m-d', strtotime('+7 days', strtotime($cal_start)));

            $all = Input::get('all', false);

            if (!$all) {
                $to_log = serialize(array('desde' => $cal_start, 'hasta' => $cal_end));
                ActionLog::log(self::MODEL . ' deleted', $to_log);
                Disponibilidad::where('usuario_id', '=', Input::get('usuario_id'))->fromDateToDate($cal_start, $cal_end)->delete();
            }
            else {
                $to_log = serialize(array('todo' => 1));
                ActionLog::log(self::MODEL . ' deleted', $to_log);
                Disponibilidad::where('usuario_id', '=', Input::get('usuario_id'))->delete();
            }
            return $this->setSuccess( Lang::get('disponibilidad.' . ($all ? 'deleted_all_msg' : 'deleted_week_msg')) );
        }
        return $this->setError( Lang::get('global.wrong_action') );
    }

}