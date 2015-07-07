<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 25/01/15
 * Time: 04:25 PM
 */

class CitaController extends BaseController {

    const PAGE_LIMIT = 5;

    const MODEL = 'Cita';

    const LANG_FILE = 'citas';

    const TITLE_FIELD = 'fecha';

    private $counter = 0;

    /** Navegacion **/

    /**
     * Muestra la página de administración
     * @return mixed
     */
    public function paginaAdmin() {
        if (Auth::user()->admin) {
            $total = $this->getTotalItems();
            $servicios = Servicio::lists('nombre', 'id');
            $equipos = Equipo::lists('nombre', 'id');
            $estados = Functions::langArray(self::LANG_FILE, Cita::state());
            return View::make('admin.citas')->with(
                array(
                    'active_menu' => 'reportes',
                    'total' => $total,
                    'servicios' => $servicios,
                    'equipos' => $equipos,
                    'estados' => $estados
                )
            );
        }
        return View::make('admin.inicio');
    }

    /**
     * Muestra la página del Calendario
     * @return mixed
     */
    public function paginaCalendario() {
        $equipos = Equipo::lists('nombre', 'id'); //$equipos = Functions::arrayIt(Equipo::get(), 'id', 'nombre');
        $genders = Functions::langArray('pacientes', Persona::getGenders());
        $marital_statuses = Functions::langArray('pacientes', Persona::getMaritalStatuses());
        $modalidades = Modalidad::getAllInUse();
        $options = Opcion::load();
        return View::make('admin.calendario')->with(
            array(
                'active_menu' => 'citas',
                'equipos' => $equipos,
                'estados' => Functions::langArray( self::LANG_FILE, Cita::state() ),
                'genders' => $genders,
                'marital_statuses' => $marital_statuses,
                'modalidades' => $modalidades,
                'options' => $options,
                'read_only' => !User::canAddCitas()
            )
        );
    }

    /**
    * This function will be called after the model validation has passed successfully
    * @param $inputs
    * @return boolean
    */
    public function afterValidation($inputs) {
        //checking that the user has permission
        if (!User::canAddCitas()) {
            $this->setError(Lang::get('global.no_permission'));
            return false;
        }

        //will log the current user id with the record
        $user_id = Auth::user()->id;
        Input::merge(array('usuario_id' => $user_id));

        $disposicion_data = array();
        if (isset($inputs['equipo_id'])) $disposicion_data['equipo_id'] = $inputs['equipo_id'];
        if (isset($inputs['doctor_id'])) $disposicion_data['doctor_id'] = $inputs['doctor_id'];
        if (isset($inputs['tecnico_id'])) $disposicion_data['tecnico_id'] = $inputs['tecnico_id'];
        if (isset($inputs['consultorio_id'])) $disposicion_data['consultorio_id'] = $inputs['consultorio_id'];

        $model = self::MODEL;
        //if the servicio_id (to pick one) has not been sent then is sending just the time (ie dragging & dropping)
        //I will then find the object so I can get the missing attributes
        if (!isset($_POST['servicio_id'])) {
            $is_dragging = true;

            $item = $model::find((int)Input::get('id'));
            if ($item) {
                if (!User::canChangeDisponibilidadState() && $item->estado != Cita::UNCONFIRMED) {
                    $this->setError(Lang::get('global.no_permission'));
                    return false;
                }
                $fields = $item->getFillable();
                $missing = array();
                foreach ($fields as $field) {
                    if (isset($inputs[$field])) continue;
                    $missing[$field] = $item->$field;
                }
                Input::merge($missing);
            }
            else {
                $this->setError(Lang::get('global.not_found'));
                return false;
            }

            $disposicion_id = $missing['disposicion_id'];
        }
        else {
            $is_dragging = false;

            //will find the id for the service-equipment-doctor-technician-office relationship, and if it doesn't exists, will create it if user is an admin
            if (!($disposicion_id = Disposicion::findFor($inputs['servicio_id'], $disposicion_data, Auth::user()->admin))) {
                $this->setError(Lang::get('citas.overlap_equipment'));
                return false;
            }
            Input::merge(array('disposicion_id' => $disposicion_id));
        }

		Session::set('input_fecha', Input::get('fecha'));

        //gets service duration
        $disposicion = Input::get('disposicion_id');
        if ($disposicion > 0) {
            $disposicion = Disposicion::find($disposicion);
            $service = $disposicion->servicio;
            $duration = $service->duracion;
        }
        else {
            $duration = 0;
        }
        $start = Input::get('inicio');
        //$end = Input::get('fin'];

        //to be valid, a start time and a doctor id are required
        if (!empty($start) && Input::get('persona_id', false)) {
            //gets the input data from the post
            $date = Input::get('fecha');
            $doctor_id = Input::get('doctor_id');
            $technician_id = Input::get('tecnico_id');
            $patient_id = Input::get('persona_id');
            $cita_id = (int)Input::get('id', 0);

            $ignore_warning = Input::get('ignore_warning', false);
            $ignore_warning_all = Input::get('ignore_warning_all', false);
            $warning_key = (int)Input::get('warning_key', 0);

            $start = $date . ' ' . Functions::ampmto24($start);
            $end = Functions::addMinutes($start, $duration, 'h:i A');
            Input::merge(array('fin' => $end)); //<-- replaces the input end time with the start time + service duration
            $end = $date . ' ' . Functions::ampmto24($end);

            //the grouped events will move only when dragging (not by editing)
            $grouped_ids = $is_dragging ? Cita::getGroupedItemsIds($cita_id) : array();

            if ($ignore_warning_all) {
                if (count($grouped_ids)) {
                    $moved_items = array();
                    if (!Cita::moveGroupedItems($grouped_ids, $end, $cita_id, $moved_items)) {
                        $this->setError(Lang::get(self::LANG_FILE . '.drag_collapse'));
                        return false;
                    }
                    foreach ($moved_items as $item) {
                        $item->save();
                    }
                }
                //skips warning checks
                return true;
            }

            //WARNINGS

            $cita = new Cita;
            $cita->id = (int)Input::get('id');
            $cita->fecha = $date;
            $cita->inicio = $start;
            $cita->fin = $end;
            $cita->estado = 0;
            $cita->persona_id = $patient_id;
            $cita->doctor_id = $doctor_id;
            $cita->tecnico_id = $technician_id;
            $cita->usuario_id = $user_id;
            $cita->disposicion_id = $disposicion_id;

            $error = 0;

            if (!Cita::isOkToPlace($cita, $grouped_ids, $error)) {

                //Horario del Servicio
                if ($error == Cita::ERR_SERVICE_TIME) {
                    if (!($ignore_warning && $warning_key == Cita::ERR_SERVICE_TIME)) {
                        $this->setReturn('warning_key', Cita::ERR_SERVICE_TIME);
                        $this->setReturn('bad', 'service');
                        $this->setReturn('overlapping', '0');
                        $this->setError(Lang::get(self::LANG_FILE . '.unavailable_service'));
                        return false;
                    }
                }

                //Doctor
                if ($error == Cita::ERR_DOCTOR) {
                    if (!($ignore_warning && $warning_key == Cita::ERR_DOCTOR)) {
                        $this->setReturn('warning_key', Cita::ERR_DOCTOR);
                        $this->setReturn('bad', 'doctor');
                        $this->setReturn('overlapping', '0');
                        $this->setError(Lang::get(self::LANG_FILE . '.overlap_doctor'));
                        return false;
                    }
                }

                //Técnico
                if ($error == Cita::ERR_TECHNICIAN) {
                    if (!($ignore_warning && $warning_key == Cita::ERR_TECHNICIAN)) {
                        $this->setReturn('warning_key', Cita::ERR_TECHNICIAN);
                        $this->setReturn('bad', 'technician');
                        $this->setReturn('overlapping', '0');
                        $this->setError(Lang::get(self::LANG_FILE . '.overlap_technician'));
                        return false;
                    }
                }

                //Paciente
                if ($error == Cita::ERR_PATIENT) {
                    if (!($ignore_warning && $warning_key == Cita::ERR_PATIENT)) {
                        $this->setReturn('warning_key', Cita::ERR_PATIENT);
                        $this->setReturn('bad', 'patient');
                        $this->setReturn('overlapping', '0');
                        $this->setError(Lang::get(self::LANG_FILE . '.overlap_patient'));
                        return false;
                    }
                }

                //Equipo
                if ($error == Cita::ERR_EQUIPMENT) {
                    if (!($ignore_warning && $warning_key == Cita::ERR_EQUIPMENT)) {
                        $this->setReturn('warning_key', Cita::ERR_EQUIPMENT);
                        $this->setReturn('bad', 'equipment');
                        $this->setReturn('overlapping', '0');
                        $this->setError(Lang::get(self::LANG_FILE . '.overlap_equipment'));
                        return false;
                    }
                }

            }

            //moves the grouped items (if any)
            if (count($grouped_ids)) {
                $moved_items = array();
                //will cancel if cannot move grouped items
                if (!Cita::moveGroupedItems($grouped_ids, $end, $cita_id, $moved_items)) {
                    $this->setError(Lang::get(self::LANG_FILE . '.drag_collapse'));
                    return false;
                }
                foreach ($moved_items as $item) {
                    $item->save();
                }
            }
        }
        else {
            $this->setError(Lang::get('global.wrong_action'));
            return false;
        }
        return true;
    }

    public function isOverlapping($start1, $end1, $start2, $end2) {
        //                      start time                              end time                         complete overlapping                           inside
        return (($start1 >= $start2 && $start1 < $end2) || ($end1 > $start2 && $end1 < $end2) || ($start1 < $start2 && $end1 > $end2) || ($start1 >= $start2 && $start1 <= $start2));
    }

    /**
     * Proceso adicional al editar / crear un nuevo registro
     * @param $item
     * @return bool
     */
    public function editarRelational($item) {
        $paciente = $item->persona;
        $this->setReturn('titulo', $paciente ? Functions::firstNameLastName($paciente->nombre, $paciente->apellido) : '-');
        $this->setReturn('inicio', Functions::explodeDateTime($item->inicio));
        $this->setReturn('fin', empty($item->fin) ? '' : Functions::explodeDateTime($item->fin));
        $this->setReturn('dia_completo', empty($item->fin) ? '1' : '0');
        $this->setReturn('estado', isset($item->estado) ? $item->estado : '1');
        return true; //needs to return true to output json
    }

    /**
     * Datos adicionales que se envian al solicitar la información del registro para editar
     * @param $item
     */
    public function additionalData($item) {
        $doctor = $item->disposicion->doctor;
        $doctor_persona = $item->persona;
        $paciente = $item->persona;
        $this->setReturn('doctor_id_lbl', $doctor_persona ? Functions::firstNameLastName($doctor->nombre, $doctor->apellido) : $doctor->numero);
        $this->setReturn('paciente_id_lbl', Functions::firstNameLastName($paciente->nombre, $paciente->apellido));
    }

    /**
     * Código HTML que se envía al solicitar la información del registro para visualizar
     * @param $item
     * @return string
     */
    public function outputInf( $item ) {
        $frm = new AForm;
        $output = "";
        $output .= $frm->id( $item->id );
        $output .= $frm->hidden('action');

        $disposicion = $item->disposicion;
        $doctor = $disposicion->doctor->persona;
        $patient = $item->persona;
        $office = $disposicion->consultorio;

        //left panel
        $output .= $frm->halfPanelOpen(true);
            $output .= $frm->view('fecha', Lang::get(self::LANG_FILE . '.date'), '<i class="fa fa-calendar-o"></i>&nbsp; ' . Functions::longDateFormat($item->fecha));
            if (!empty($item->fin)) {
                $output .= $frm->view('start_time', Lang::get(self::LANG_FILE . '.time_start'), '<i class="fa fa-clock-o"></i>&nbsp; ' . Functions::justTime($item->inicio) . ' - ' . Functions::justTime($item->fin));
            }
            $output .= $frm->view('patient', Lang::get(self::LANG_FILE . '.patient'), '<i class="fa fa-user"></i>&nbsp; ' . mb_strtoupper($patient->apellido) . ', ' . $patient->nombre);
            $output .= $frm->view('doctor', Lang::get(self::LANG_FILE . '.doctor'), '<i class="fa fa-user-md"></i>&nbsp; ' . mb_strtoupper($doctor->apellido) . ', ' . $doctor->nombre);
            $output .= $frm->view('office', Lang::get(self::LANG_FILE . '.office'), '<i class="fa fa-cube"></i>&nbsp; ' . $office->nombre . ' (' . $office->area->nombre . ')');
            $output .= $frm->view('record_date', Lang::get(self::LANG_FILE . '.record_date'), '<i>' . Functions::longDateFormat($item->created_at) . '</i>');
        $output .= $frm->halfPanelClose();
        //right panel
        $output .= $frm->halfPanelOpen(false, 6, 'text-center');
            if (!empty($item->inicio)) {
                $remaining = Functions::remainingTime($item->inicio);
                if ($item->inicio > date('Y-m-d H:i')) { //(!$remaining->invert) {
                    //if it is a matter of minutes, show minutes counter
                    if ($remaining->y == 0 && $remaining->m == 0 && $remaining->d == 0 && $remaining->h == 0) {
                        if ($remaining->i > 0) {
                            $output .= $frm->remainingTime($remaining->i, 60000);
                            $this->setReturn('script', $frm->script());
                        }
                    }
                    else {
                        $output .=  ($remaining->y ? (Functions::singlePlural(Lang::get('global.year'), Lang::get('global.years'), $remaining->y, true) . ' ') : '') .
                                    ($remaining->m ? (Functions::singlePlural(Lang::get('global.month'), Lang::get('global.months'), $remaining->m, true) . ' ') : '') .
                                    ($remaining->d ? (Functions::singlePlural(Lang::get('global.day'), Lang::get('global.days'), $remaining->d, true) . ' ') : '') .
                                    ($remaining->h ? (Functions::singlePlural(Lang::get('global.hour'), Lang::get('global.hours'), $remaining->h, true) . ' ') : '') .
                                    ($remaining->i ? (Functions::singlePlural(Lang::get('global.minute'), Lang::get('global.minutes'), $remaining->i, true)) : '');
                    }
                }
            }
        $output .= $frm->halfPanelClose(true);

        $output .= $frm->controlButtons();


        return $output;
    }

    public function searchByFields($from, $to, $doctor_id = 0, $paciente_id = 0, $servicio_id = 0, $equipo_id = 0, $estado = 'any') {
        /*$model = self::MODEL;
        $records = new $model*/;

        $records = DB::table('vw_cita_disposicion');

        $records = $records->orderBy('inicio', 'DESC')->take(500);

        //if the date 'from' is specified
        if (strlen($from) > 0) {
            $records = $records->where('fecha', '>=', $from);
        }
        //if the date 'to' is specified
        if (strlen($to) > 0) {
            $records = $records->where('fecha', '<=', $to);
        }

        //if the doctor is specified
        if ($doctor_id > 0) {
            $records = $records->where('doctor_id', '=', $doctor_id);
        }
        //if the patient is specified
        if ($paciente_id > 0) {
            $records = $records->where('persona_id', '=', $paciente_id);
        }
        //if the service is specified
        if ($servicio_id > 0) {
            $records = $records->where('servicio_id', '=', $servicio_id);
        }
        //if the office is specified
        if ($equipo_id > 0) {
            $records = $records->where('equipo_id', '=', $equipo_id);
        }
        //if the state is specified
        if ($estado != 'any') {
            $estado = (int)$estado;
            $records = $records->where('estado', '=', $estado);
        }
        $records = $records->get();

        return $records;
    }

    public function buscarGetAlt() {
        $validator = Validator::make(Input::all(),
            array(
                'search_query'          => '',
                /*'search_page'           => 'required|integer|min:1',*/
                'buscar_doctor_id'      => 'integer|min:1',
                'buscar_paciente_id'    => 'integer|min:1',
                'buscar_servicio_id'    => 'integer|min:0',
                'buscar_equipo_id'      => 'integer|min:0'
            )
        );
        if ($validator->passes()) {
            /*$query  = Input::get('search_query');
            $page   = Input::get('search_page');

            //1. searches by text input
                $search_fields = array('fecha', 'nombre_paciente', 'apellido_paciente', 'cedula_paciente', 'nombre_doctor', 'apellido_doctor', 'cedula_doctor');
                $match_total = 0;

                $records = $this->buscarTabla('citas', $query, $page, $search_fields, $match_total );
                $total = count($records);*/

            //2. searches by rows
                $from = Input::get('from');
                $to = Input::get('to');

                $doctor_id = (int)Input::get('buscar_doctor_id');
                $paciente_id = (int)Input::get('buscar_paciente_id');
                $servicio_id = (int)Input::get('buscar_servicio_id');
                $equipo_id = (int)Input::get('buscar_equipo_id');
                $estado = Input::get('buscar_estado_id');

                $records = $this->searchByFields($from, $to, $doctor_id, $paciente_id, $servicio_id, $equipo_id, $estado);

                $total = count($records);
                $match_total = $total;

            $is_export = (bool)Input::get('export', false);

            $this->setReturn('total', $match_total);
            $this->setReturn('total_page', $total);
            if (!$is_export) {
                //$this->setReturn('results', $this->buscarReturnHtml($records, array()));
                $this->counter = 0;
                $this->setReturn('results', $this->buscarReturnHtmlTable($records));
            }
            else {
                //$this->exportToExcel($records);
                if (Input::get('export_csv', false)) {
                    return $this->exportCsv($records, Input::get('show_notes', false));
                }
                else {
                    return $this->outputReport($records, $from, $to, $doctor_id, $paciente_id, $servicio_id, $equipo_id, $estado);
                }
            }
            return $this->returnJson();
        }
        return $this->setError( Lang::get('global.wrong_action') );
    }

    /**
     * Código HTML que se envía al realizar una búsqueda
     * @param $records
     * @param $search_fields
     * @param bool $show_doctor
     * @param bool $show_service
     * @param bool $show_office
     * @return string
     */
    public function buscarReturnHtml($records, $search_fields, $show_doctor = true, $show_service = false, $show_office = false) {
        //return AForm::searchResults($records, 'inicio', array(array(Lang::get(self::LANG_FILE . '.patient'),'nombre_paciente'), array(Lang::get(self::LANG_FILE . '.doctor'),'nombre_doctor')));

        $output = "";
        if (count($records)) {
            foreach ($records as $record) {
                //1. searches by text input
                /*$row = Functions::longDateFormat($record->fecha);
                if (!empty($record->inicio)) {
                    $row .= AForm::badge(Functions::justTime($record->inicio));
                }
                $row = '<h4><i class="fa fa-clock-o"></i> ' . $row . '</h4>';
                //$id = $record->id;
                $row .= '<br><b>' . Lang::get(self::LANG_FILE . '.patient') . '</b>: ' .  Functions::firstNameLastName($record->nombre_paciente, $record->apellido_paciente);
                $row .= '<br><b>' . Lang::get(self::LANG_FILE . '.doctor') . '</b>: ' .  Functions::firstNameLastName($record->nombre_doctor, $record->apellido_doctor);
                $output.= <<<EOT
                    <a class="list-group-item search-result" data-id="{$id}">{$row}</a>
EOT;*/
                //2. searches by rows
                $row = Functions::longDateFormat($record->fecha);
                if (!empty($record->fin)) {
                    $row .= AForm::badge(Functions::justTime($record->inicio));
                }
                //  date & time
                $row = Functions::inactiveIf('<h4><i class="fa fa-clock-o"></i> ' . $row . '</h4>', $record->estado == Cita::DONE);
                
                //  patient
                $patient = $record->persona;//Paciente::find($record->persona_id);
                $row .= '<br><b>' . Lang::get(self::LANG_FILE . '.patient') . '</b>: ' . Functions::firstNameLastName($patient->nombre, $patient->apellido);
                
                //  doctor
                if ($show_doctor) {
                    $doctor = Doctor::find($record->disposicion->doctor_id);
                    if ($doctor) {
                        $doctor = $doctor->persona;
                        if ($doctor) {
                            $doctor = Functions::firstNameLastName($doctor->nombre, $doctor->apellido);
                        }
                        else {
                            $doctor = $doctor->numero;
                        }
                        $row .= '<br><b>' . Lang::get(self::LANG_FILE . '.doctor') . '</b>: ' . $doctor;
                    }
                }

                //  treatment
                if ($show_service) {
                    $service = $record->servicio;//Servicio::find($record->persona_id);
                    $row .= '<br><b>' . Lang::get(self::LANG_FILE . '.service') . '</b>: ' . $service->nombre;
                }

                $output.= <<<EOT
                    <a class="list-group-item search-result" data-id="{$record->id}">{$row}</a>
EOT;
            }
        }

        return $output;
    }


    public function buscarReturnHtmlPrint($records, $show_date = true, $show_doctor = true, $show_service = true, $show_office = true) {
        $output = "";
        if (count($records)) {
            foreach ($records as $record) {
                //date & time
                if ($show_date) {
                    $row = Functions::longDateFormat($record->fecha);
                    if (!empty($record->fin)) {
                        $row .= '&nbsp;&nbsp;(' . Functions::justTime($record->inicio) . ')';
                    }
                }
                else {
                    if (!empty($record->fin)) {
                        $row = Functions::justTime($record->inicio);
                    }
                }
                $row = '<h4>' . $row . '</h4>';

                //  patient
                $patient = $record->persona;//Paciente::find($record->persona_id);
                $row .= '<b>' . Lang::get(self::LANG_FILE . '.patient') . '</b>: ' . Functions::firstNameLastName($patient->nombre, $patient->apellido);
                
                //  doctor
                if ($show_doctor) {
                    $doctor = Doctor::find($record->disposicion->doctor_id);
                    if ($doctor) {
                        $doctor = $doctor->persona;
                        if ($doctor) {
                            $doctor = Functions::firstNameLastName($doctor->nombre, $doctor->apellido);
                        }
                        else {
                            $doctor = $doctor->numero;
                        }
                        $row .= '<br><b>' . Lang::get(self::LANG_FILE . '.doctor') . '</b>: ' . $doctor;
                    }
                }

                //  treatment
                if ($show_service) {
                    $service = $record->servicio;//Servicio::find($record->persona_id);
                    $row .= '<br><b>' . Lang::get(self::LANG_FILE . '.service') . '</b>: ' . $service->nombre;
                }

                //  location
                if ($show_office) {
                    $office = $record->consultorio;
                    $area = $office->area->nombre;
                    $row .= '<br><b>' . Lang::get(self::LANG_FILE . '.office') . '</b>: ' . $office->nombre . ' &nbsp; (' . $area . ')';
                }

                $output.= '<br><br>' . $row;
            }
        }

        return $output;
    }

    public function buscarReturnHtmlTable($records) {
        $id = 'tbl_return_' . uniqid();
        $script = <<<EOT
            $('#{$id}').bootstrapTable();
EOT;
        /*
         {
                columns: [
                    {
                        field: 'id',
                        title: '#'
                    }, {
                        field: 'state',
                        title: 'Estado'
                    }, {
                        field: 'date',
                        title: 'Fecha'
                    }, {
                        field: 'patient',
                        title: 'Paciente'
                    }, {
                        field: 'service',
                        title: 'Tratamiento'
                    }, {
                        field: 'equipment',
                        title: 'Equipo'
                    }, {
                        field: 'doctor',
                        title: 'Terapeuta'
                    }
                ]
            }
         */
        $this->setReturn('script', $script);
        $output = <<<EOT
            <table id="{$id}">
                <thead>
                    <tr>
                        <th data-field="count" data-sortable="true">#</th>
                        <th data-field="state" data-sortable="true">Estado</th>
                        <th>Fecha</th>
                        <th data-field="patient" data-sortable="true">Paciente</th>
                        <th data-field="service" data-sortable="true">Servicio</th>
                        <th data-field="equipment" data-sortable="true">Equipo</th>
                        <th data-field="doctor" data-sortable="true">Doctor</th>
                    </tr>
                </thead>
                <tbody>
EOT;
        foreach ($records as $record) {
            $this->counter++;
            $properties = Cita::getProperties($record);
            $date = Functions::longDateFormat($record->inicio, true);
            $output.= <<<EOT
                    <tr>
                        <td>{$this->counter}</td>
                        <td>{$properties['state']}</td>
                        <td>{$date}</td>
                        <td>{$properties['patient']}</td>
                        <td>{$properties['service']}</td>
                        <td>{$properties['equipment']}</td>
                        <td>{$properties['doctor']}</td>
                    </tr>
EOT;
        }
        $output.= <<<EOT
                </tbody>
            </table>
EOT;
        return $output;
    }


    public function buscarReturnCountHtml($records, $fn_name = null) {
        $output = "";
        if (count($records)) {
            foreach ($records as $record) {
                if ($fn_name !== null && method_exists($this, $fn_name)) {
                    $row = $this->$fn_name($record->index);
                }
                else {
                    $row = $record->index;
                }
                $row.= AForm::badge( $record->total );

                $output.= <<<EOT
                    <a class="list-group-item search-result" data-id="0">{$row}</a>
EOT;
            }
        }

        return $output;
    }

    public function countHtmlState($index) {
        return Lang::get('citas.' . Cita::state($index));
    }

    public function returnGraphStates($records) {
        $data = array();
        foreach ($records as $record) {
            $label = Lang::get('citas.' . Cita::state($record->index));
            $data[] = <<<EOT
        {label:'{$label} ({$record->total})', data:{$record->total}}
EOT;
        }
        $data = '[' . implode(',', $data) . ']';

        return <<<EOT
        $.plot($("#result_holder_graph"), {$data}, {
            series: {
                pie: {
                    innerRadius: 0.5,
                    show: true,
                    label: {
                        show: false,
                        radius: 1
                    }
                }
            },
            colors: ["#F0AD4E", "#A8BC7B", "#70AFC4", "#D9534F", "#DB5E8C", "#FCD76A", "#A696CE"]
        });
EOT;

    }

    public function returnGraphServices($records) {

        return <<<EOT

EOT;
    }


    public function outputReport($records, $from, $to, $doctor_id, $paciente_id, $servicio_id, $equipo_id, $estado) {
        //doctor
        $doctor = $doctor_id ? Doctor::find($doctor_id) : false;
        if ($doctor) {
            $doctor = $doctor->persona;
            if ($doctor) {
                $doctor = Functions::firstNameLastName($doctor->nombre, $doctor->apellido) . ' (' . $doctor->dni . ')';
            }
            else {
                $doctor = false;
            }
        }
        else {
            $doctor = false;
        }

        //patient
        $paciente = $paciente_id ? Persona::find($paciente_id) : false;
        if ($paciente) {
            $paciente = Functions::firstNameLastName($paciente->nombre, $paciente->apellido) . ' (' . $paciente->dni . ')';
        }
        else {
            $paciente = false;
        }

        //service
        $servicio = $servicio_id ? Servicio::find($servicio_id) : false;
        if ($servicio) {
            $servicio = $servicio->nombre;
        }
        else {
            $servicio = false;
        }

        //equipo
        $equipo = $equipo_id ? Equipo::find($equipo_id) : false;
        if ($equipo) {
            $equipo = $equipo->nombre . Functions::encloseStr($equipo->modelo, ' - ', '');
        }
        else {
            $equipo = false;
        }

        //state
        $estado = $estado == 'any' ? false : Lang::get('citas.' . Cita::state($estado));

        return View::make('admin.reporte_citas')->with(
            array(
                'records'           => $records,
                'show_date_range'   => (!empty($from) || !empty($to)),
                'date_range'        => (!empty($from) ? Functions::longDateFormat($from) : '...') . '  —  ' . (!empty($to) ? Functions::longDateFormat($to) : '...'),
                'doctor'            => $doctor,
                'patient'           => $paciente,
                'service'           => $servicio,
                'equipment'         => $equipo,
                'state'             => $estado,
                'show_note'         => Input::get('show_notes', false)
            )
        );
    }


    /*public function exportToExcel($records) {
        require public_path() . '/PHPExcel.php';
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("PHPExcel Test Document")
            ->setSubject("PHPExcel Test Document")
            ->setDescription("Test document for PHPExcel, generated using PHP classes.")
            ->setKeywords("office PHPExcel php")
            ->setCategory("Test result file");

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Hello')
            ->setCellValue('B2', 'world!')
            ->setCellValue('C1', 'Hello')
            ->setCellValue('D2', 'world!');

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Save Excel 2007 file
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(public_path() . '/tmp_report.xlsx');
    }*/


    public function exportCsv($records, $show_note) {
        $contents = '';
        $count = 0;
        foreach ($records as $record) {
            $vals = Cita::getRowValues($count, $record, true, true, true, true, true, $show_note, false); //LOL
            if (is_array($vals)) {
                foreach ($vals as $key => $val) {
                    $vals[$key] = str_replace('"', "'", $val);
                    if (strpos($val, ';') !== false) {
                        $vals[$key] = '"' . $val . '"'; //puts quotes in values with semicolons
                    }
                }
                $contents .= implode(';', $vals) . PHP_EOL;
            }
        }

        $response = Response::make(utf8_decode($contents));
        $response->header('Content-Type', 'text/csv; charset=UTF-8');
        return $response;
    }

    public function getEstadisticas() {
        $validator = Validator::make(Input::all(),
            array(
                'from'  => 'integer|min:1',
                'to'    => 'integer|min:1',
                'type'  => ''
            )
        );
        if ($validator->passes()) {
            $from = Input::get('from');
            $to = Input::get('to');
            $type = Input::get('type');

            switch ($type) {
                case 'state':
                    $records = Cita::getStatesCount($from, $to);
                    break;
                case 'service':
                    $records = Cita::getServiceCount($from, $to);
                    break;
                case 'office':
                    $records = array();
                    $fn = null;
                    break;
                case 'equipment':
                    $records = array();
                    $fn = null;
                    break;
                default:
                    $records = array();
                    $fn = null;
            }

            $total = count($records);
            $match_total = $total;

            $is_export = (bool)Input::get('export', false);

            $this->setReturn('total', $match_total);
            $this->setReturn('total_page', $total);
            if (!$is_export) {
                //$this->setReturn('results', $this->buscarReturnCountHtml($records, $fn));
                switch ($type) {
                    case 'state':
                        $script = $this->returnGraphStates($records);
                        break;
                    case 'service':
                        $script = $this->returnGraphServices($records);
                        break;
                    default:
                        $script = '';
                }
                $this->setReturn('script', $script);
            }
            else {
                //$this->exportToExcel($records);
                if (Input::get('export_csv', false)) {
                    //return $this->exportCsv($records, Input::get('show_notes', false));
                }
                else {
                    //return $this->outputReport($records, $from, $to, $doctor_id, $paciente_id, $servicio_id, $consultorio_id, $estado);
                }
            }
            return $this->returnJson();
        }
        return $this->setError( Lang::get('global.wrong_action') );
    }


    public function getCitas() {
        $cal_start = Input::get('start');
        $cal_end = Input::get('end');
        $citas_json = array();
        if (User::canViewAllCitas()) {
            $citas = Cita::fromDate($cal_start)->toDate($cal_end)->orderBy('persona_id')->orderBy('inicio')->get();
        }
        else {
            $citas = Auth::user()->cita()->fromDate($cal_start)->toDate($cal_end)->orderBy('inicio')->get();
        }

        $group_patients = true;

        $j = 0;
        $last_patient_id = 0;
        $last_doctor_id = 0;
        $last_day = 0;
        $last_end_time = 0;

        $citas_array = array();
        foreach ($citas as $cita) {
            $doctor = $cita->doctor;
            if ($doctor) {
                $doctor_data = $doctor->persona;
                if ($doctor_data) {
                    $doctor = Functions::firstNameLastName($doctor_data->nombre, $doctor_data->apellido, true);
                }
                else {
                    $doctor = $doctor->nombre;
                }
            }
            else {
                $doctor = '';
            }

            $paciente = $cita->persona;

            //finding the equipment
            $disposicion = Disposicion::find($cita->disposicion_id);
            if ($disposicion) {
                $equipo = $disposicion->equipo;
                $equipo_id = $equipo->id;
                $equipo = $equipo->nombre . Functions::encloseStr($equipo->modelo, ' - ', '');

                $servicio = $disposicion->servicio;
            }
            else {
                $equipo = '';
                $equipo_id = 0;
                $servicio = false;
            }

            $patients_phone = $paciente->contactos()->telefonos()->lists('contenido');
            if (is_array($patients_phone) && count($patients_phone) > 0) {
                $patients_phone = " &nbsp; <i class='fa fa-phone'></i> " . Functions::formatPhone(reset($patients_phone));
            }
            else {
                $patients_phone = '';
            }

            $title = mb_strtoupper(Functions::firstNameLastName($paciente->nombre, $paciente->apellido)) . "<span class='phone'>" . $patients_phone . '</span>' . ($servicio->duracion >= 20 ? '<br>' : '&nbsp;-&nbsp;') .
                     '<i>' . $servicio->nombre . '</i>' . ($servicio->duracion >= 30 ? '<br>' : '&nbsp;-&nbsp;') .
                     '<b>' . $equipo . '</b>';

            $title = str_replace('"', '',  $title);

            $start = $cita->inicio;
            $end = !empty($cita->fin) ? "\"end\": \"$cita->fin\"," : '';
            //$all_day = $end != '' ? 'false' : 'true';

            $bg_color = '375471';//'2983AE'
            $doctor_color = '406182';

            $atention = (($cita->estado != Cita::DONE && $cita->estado != Cita::CANCELLED) && strtotime($cita->inicio) < time()) ? '1' : '0';
			
			$comment = $cita->nota;
			$comment = $comment ? Functions::nl2br(htmlentities($comment->contenido)) : '';
            $comment = str_replace("'", '', str_replace('"', '', $comment));

            //finding the user
            $user = $cita->usuario_id > 0 ? User::find($cita->usuario_id) : '';
            if ($user) {
                $user_data = $user->persona;
                if ($user_data) {
                    $user = Functions::firstNameLastName($user_data->nombre, $user_data->apellido, true);
                }
                else {
                    $user = $user->nombre;
                }
            }

            if ($group_patients && $cita->persona_id == $last_patient_id && $last_day == $cita->fecha && $j > 0 && ($cita->inicio == $last_end_time /*|| $cita->estado == Cita::CANCELLED*/)) {
                $title = '<i>' . $servicio->nombre . '</i>' . ($servicio->duration >= 30 ? '<br>' : '&nbsp;-&nbsp;') .
                         '<b>' . $equipo . '</b>';
                $duration = $servicio->duracion;
                if ($citas_array[$j-1]['has_many'] == 0) {
                    $citas_array[$j-1]['title'] = "<span class='grouped-event first-in-group state" . $citas_array[$j-1]['state_id'] . "' attr-id='" . $citas_array[$j-1]['id'] . "' attr-doctor='" . $citas_array[$j-1]['doctor_id'] . "' attr-state='" . $citas_array[$j-1]['state_id'] . "' attr-office='" . $citas_array[$j-1]['office_id'] . "' attr-service='" . $citas_array[$j-1]['service_id'] . "' attr-duration='" . $citas_array[$j-1]['duration'] . "' attr-equipment='" . $citas_array[$j-1]['equipment_id'] . "'>" . $citas_array[$j-1]['title'] . "</span>";
                }
                $title = str_replace('"', '',  $title);
                $doctor_name = $cita->doctor_id != $last_doctor_id ? (' <span class=\'badge\' style=\'background-color:#' . $doctor_color . '\'>' . $doctor . '</span>') : '';
                $citas_array[$j-1]['title'] .= "<br>" . //"<br>____________<br>" .
                    "<span class='grouped-event state{$cita->estado} doctor{$cita->doctor_id} office0 service{$servicio->id} equipment{$equipo_id} tip-right' attr-id='{$cita->id}' attr-doctor='{$cita->doctor_id}' attr-state='{$cita->estado}' attr-office='0' attr-service='{$servicio->id}' attr-duration='{$duration}' attr-equipment='{$equipo_id}' data-html='true' data-toggle='tooltip' data-original-title='{$comment}' title='{$comment}'>" .
                        "<small>" . strtolower(Functions::justTime($start,true,true,true)) . "</small>" . $doctor_name . (strlen($comment) > 0 ? (" <small><i class='fa fa-comment'></i></small>") : "") . "<br>" .
                        $title .
                    "</span>";
                $citas_array[$j-1]['end'] = $end;
                $citas_array[$j-1]['has_many'] = 1;
            }
            else {
                $citas_array[$j] = array(
                    "id" => $cita->id,
                    "title" => $title,
                    "start" => $start,
                    "end" => $end,
                    "allDay" => 'false',
                    "backgroundColor" => $bg_color,
                    "doctor_id" => $cita->doctor_id,
                    "doctor_name" => $doctor,
                    "doctor_color" => $doctor_color,
                    "patient_id" => $cita->persona_id,
                    "service_id" => $servicio->id,
                    "duration" => $servicio->duracion,
                    "office_id" => 0,
                    "equipment_id" => $equipo_id,
                    "state_id" => $cita->estado,
                    "atention" => $atention,
                    "comment" => $comment,
                    "user" => $user,
                    "group_id" => 0,
                    "has_many" => 0
                );
                $j++;
                $last_patient_id = $cita->persona_id;
                $last_doctor_id = $cita->doctor_id;
                $last_day = $cita->fecha;
            }
            $last_end_time = $cita->fin;
        }

        foreach ($citas_array as $cita) {
            $citas_json[] = <<<EOT
            {
                "id": "{$cita['id']}",
                "title": "{$cita['title']}",
                "start": "{$cita['start']}",{$cita['end']}
                "allDay": {$cita['allDay']},
                "backgroundColor": "#{$cita['backgroundColor']}",
                "doctor_id": "{$cita['doctor_id']}",
                "doctor_name": "{$cita['doctor_name']}",
                "doctor_color": "#{$cita['doctor_color']}",
                "patient_id": "{$cita['patient_id']}",
                "service_id": "{$cita['service_id']}",
                "equipment_id": "{$cita['equipment_id']}",
                "office_id": "{$cita['office_id']}",
                "state_id": "{$cita['state_id']}",
                "atention": "{$cita['atention']}",
				"comment": "{$cita['comment']}",
				"user": "{$cita['user']}",
				"group_id": "{$cita['group_id']}",
				"has_many": "{$cita['has_many']}"
            }
EOT;
        }
        return '[' . implode(',', $citas_json) . ']';
    }


    public function getCitasPrint() {
        $validator = Validator::make(Input::all(),
            array(
                'day'  => 'required|date_format:Y-m-d'
            )
        );

        if ($validator->passes()) {
            $day = Input::get('day');
            $items = Cita::notCancelled()->forDay($day)->orderBy('doctor_id')->orderBy('inicio')->get();
            $citas = array();
            $doctors = array();
            $last_patient_id = 0;
            $last_cita_end = 0;
            $last_doctor_id = 0;
            $last_item = null;
            $j = array();
            foreach ($items as $item) {
                $properties = Cita::getProperties($item, true);
                $dr_id = $properties['doctor_id'];
                $joined = $properties['patient_id'] == $last_patient_id && $properties['start'] == $last_cita_end && $properties['doctor_id'] == $last_doctor_id;
                $properties['joined'] = (int)$joined;
                $properties['will_have_joined'] = 0;
                $properties['continuous'] = 0;

                if (isset($citas[$dr_id], $j[$dr_id])) {
                    //if it's going to be joined and the previous one wasn't then tell the previous one it will have a joined one
                    $citas[$dr_id][$j[$dr_id] - 1]['will_have_joined'] = (int)($joined && !$citas[$dr_id][$j[$dr_id] - 1]['joined']);
                    //if the start time same as previous end time then is continuous
                    $properties['continuous'] = (int)($properties['start'] == $citas[$dr_id][$j[$dr_id] - 1]['end']);
                }

                $last_patient_id = $properties['patient_id'];
                $last_cita_end = $properties['end'];
                $last_doctor_id = $dr_id;
                if (!isset($citas[$dr_id])) {
                    $citas[$dr_id] = array();
                }
                if (!isset($j[$dr_id])) {
                    $j[$dr_id] = 0;
                }
                $citas[$dr_id][$j[$dr_id]] = $properties;
                $j[$dr_id]++;
                if (!isset($doctors[$dr_id])) {
                    $doctors[$dr_id] = $properties['doctor'];
                }
            }

            $max_citas = 0;
            foreach ($doctors as $dr_id => $dr) {
                if (count($citas[$dr_id]) > $max_citas) {
                    $max_citas = count($citas[$dr_id]);
                }
            }

            $start = 8;
            $end = 20;
            $interval = .5;

            $times = array();
            for ($t = $start; $t <= $end; $t += $interval) {
                $h = floor($t);
                $m = ($t - $h) * 60;
                $times[] = mktime($h, $m, 0);
            }

            return View::make('admin.calendario_print_alt')->with(
                array(
                    'times' => $times,
                    'doctors' => $doctors,
                    'citas' => $citas,
                    'max_citas' => $max_citas,
                    'n_rows' => 8,
                    'total_rows' => count($doctors)
                )
            );
        }
        return '';
    }


    public function checkAvailabilityPost() {
        $model = self::MODEL;
        $validator = Validator::make(Input::all(),
            $model::getValidationRules()
        );
        if ($validator->passes()) {

        }
    }

    public function getCitaHistory() {
        $id = (int)Input::get('cita_id');
        if ($id > 0) {
            $logs = Cita::getHistory($id);
            $output = '';
            $last_user_id = 0;
            $last_user = null;
            foreach ($logs as $log) {
                if ($last_user_id == 0 || $last_user_id != $log->usuario_id) {
                    $user = User::find($log->usuario_id);
                    if ($user) {
                        $doctor = $user->persona;
                        if ($doctor) {
                            $user = Functions::firstNameLastName($doctor->nombre, $doctor->apellido);
                        } else {
                            $user = $user->nombre;
                        }
                    }
                    else {
                        $user = Lang::get('global.not_found');
                    }
                    $last_user_id = $log->usuario_id;
                    $last_user = $user;
                }
                else {
                    $user = $last_user;
                }
                $row = Functions::longDateFormat($log->created_at, true);
                $row.= '<br>' . $user . ' ' . Lang::get('log.' . $log->accion . '_doctor');
                switch ($log->accion) {
                    case 'Cita changed_state':
                        try {
                            $obj = @unserialize($log->objeto);
                        }
                        catch(Exception $ex) {
                            $obj = false;
                        }
                        if (is_array($obj)) {
                            $row .= AForm::badge(Lang::get('citas.' . Cita::state((int)$obj['estado'])));
                        }
                }
                unset($obj);
                $output.= <<<EOT
                    <a class="list-group-item" data-id="{$log->id}">{$row}</a>
EOT;
            }
            $this->setReturn('html', $output);
        }
        return $this->returnJson();
    }


    private function infoDateTime($date, $start, $end) {
        $remaining = Functions::remainingTime( $date . ' ' . Functions::ampmto24($start), 'all' );
        //send information
        $this->setReturn('fecha_inf', Functions::longDateFormat( $date ));
        $this->setReturn('restante', $remaining != '' ? (Lang::get(self::LANG_FILE . '.in') . ' ' . $remaining) : ('<i class="fa fa-exclamation-triangle"></i> &nbsp;' . Lang::get(self::LANG_FILE . '.passed_time')));
        $this->setReturn('hora_inf', $start);
        //send back data
        $this->setReturn('fecha', $date);
        $this->setReturn('inicio', $start);
        $this->setReturn('fin', $end);
    }

    private function infoDoctor($doctor_id) {
        $avatar = URL::asset('img/avatars/s/default.jpg');
        $doctor = $doctor_id > 0 ? Doctor::find($doctor_id) : false;
        if ($doctor) {
            $doctor_data = $doctor->persona;
            if ($doctor_data) {
                if (!empty($doctor_data->avatar)) {
                    $avatar = URL::asset('img/avatars/s/' . $doctor_data->avatar);
                }
                $doctor = Functions::firstNameLastName($doctor_data->nombre, $doctor_data->apellido);
            }
            else {
                $doctor = $doctor->numero;
            }
        }
        else {
            $doctor = Lang::get('global.' . ($doctor_id > 0 ? 'not_found' : 'not_assigned'));
        }
        //send information
        $this->setReturn('doctor_name_inf', $doctor);
        $this->setReturn('avatar_inf', $avatar);
        //send back data
        $this->setReturn('doctor_id', $doctor_id);
    }

    private function infoTechnician($technician_id) {
        $avatar = URL::asset('img/avatars/s/default.jpg');
        $tecnico = $technician_id > 0 ? Tecnico::find($technician_id) : false;
        if ($tecnico) {
            $doctor_data = $tecnico->persona;
            if ($doctor_data) {
                if (!empty($doctor_data->avatar)) {
                    $avatar = URL::asset('img/avatars/s/' . $doctor_data->avatar);
                }
                $tecnico = Functions::firstNameLastName($doctor_data->nombre, $doctor_data->apellido);
            }
            else {
                $tecnico = $tecnico->cod_dicom;
            }
        }
        else {
            $tecnico = Lang::get('global.' . ($technician_id > 0 ? 'not_found' : 'not_assigned'));
        }
        //send information
        $this->setReturn('technician_name_inf', $tecnico);
        $this->setReturn('avatar_inf_technician', $avatar);
        //send back data
        $this->setReturn('tecnico_id', $technician_id);
    }

    private function infoPatient($patient_id) {
        if ($patient_id) {
            $patient = Persona::find($patient_id);
            if ($patient) {
                $record_inf = Lang::get(self::LANG_FILE . '.record_date_alt') . ' ' . Functions::longDateFormat($patient->created_at);
                $patient = Functions::firstNameLastName($patient->nombre, $patient->apellido) . ' <span class="pull-right text-muted">' . $patient->dni . '</span>';
            }
            else {
                $patient = Lang::get('global.not_found');
                $record_inf = '';
            }
            $num_citas = Cita::total($patient_id)->count();
        }
        else {
            $patient = Lang::get('global.not_assigned');
            $num_citas = 0;
            $record_inf = '';
        }
        //send information
        $this->setReturn('patient_name_inf', $patient);
        $this->setReturn('record_inf', $record_inf);
        $this->setReturn('num_citas_inf', Functions::singlePlural(Lang::get(self::LANG_FILE . '.title_single'), Lang::get(self::LANG_FILE . '.title_plural'), $num_citas, true));
        //send back data
        $this->setReturn('persona_id', $patient_id);
    }

    private function infoService($service_id, $from_disposicion = false) {
        if (!$from_disposicion) {
            $service = Servicio::find($service_id);
            if ($service) {
                $equipment = $service->equipos()->first();

                $desc = $service->nombre;
                if ($equipment) {
                    $desc .= ' <span class="pull-right text-muted">' . $equipment->modalidad->nombre . '</span>';
                }
            } else {
                $desc = Lang::get('global.not_found');
                $service = false;
            }
        }
        else {
            $disposicion = Disposicion::find($service_id);
            if ($disposicion) {
                $service = $disposicion->servicio;
                $equipment = $disposicion->equipo;

                $desc = $service->nombre;
                $desc .= ' <span class="pull-right text-muted">' . $equipment->modalidad->nombre . '</span>';
                $service_id = $service->id;
            }
            else {
                $desc = Lang::get('global.not_found');
                $service = false;
            }
        }

        //send information
        $this->setReturn('service_name_inf', $desc);
        $this->setReturn('duration_inf', $service ? Functions::minToHours($service->duracion) : '');
        $this->setReturn('duration', $service ? $service->duracion : '0');
        //send back data
        $this->setReturn('servicio_id', $service_id);
    }

    private function infoEquipment($equipo_id, $from_disposicion = false) {
        if (!$from_disposicion) {
            $equipment = Equipo::find($equipo_id);
            if ($equipment) {
                $desc = $equipment->nombre;
                //$desc .= ' <span class="pull-right badge">' . $equipment->modalidad->nombre . '</span>';
            } else {
                $desc = Lang::get('global.not_found');
            }
        }
        else {
            $disposicion = Disposicion::find($equipo_id);
            if ($disposicion) {
                $equipment = $disposicion->equipo;

                $desc = $equipment->nombre;
                //$desc .= ' <span class="pull-right badge">' . $equipment->modalidad->nombre . '</span>';
                $equipo_id = $equipment->id;
            }
            else {
                $desc = Lang::get('global.not_found');
            }
        }

        //send information
        $this->setReturn('equipment_name_inf', $desc);
        //send back data
        $this->setReturn('equipo_id', $equipo_id);
    }


    public function getAllInfo() {
        $cita_id = (int)Input::get('id');
        $this->setReturn('cita_id', $cita_id);
        if ($cita_id > 0) {
            $model = self::MODEL;
            $cita = $model::find($cita_id);
            if ($cita) {
                $this->infoDateTime($cita->fecha, Functions::justTime($cita->inicio), Functions::justTime($cita->fin));
                $this->infoDoctor($cita->doctor_id);
                $this->infoTechnician($cita->tecnico_id);
                $this->infoPatient($cita->persona_id);
                $this->infoService($cita->disposicion_id, true);
                $this->infoEquipment($cita->disposicion_id, true);
            }
        }
        return $this->returnJson();
    }

    public function getInfoDateTime() {
        if ($this->validateInputs()) {
            $this->infoDateTime(Input::get('fecha'), Input::get('inicio'), Input::get('fin'));
        }
        return $this->returnJson();
    }

    public function getInfoDoctor() {
        if ($this->validateInputs()) {
            $this->infoDoctor(Input::get('doctor_id'));
        }
        return $this->returnJson();
    }

    public function getInfoPatient() {
        if ($this->validateInputs()) {
            $this->infoPatient(Input::get('persona_id'));
        }
        return $this->returnJson();
    }

    public function getInfoService() {
        if ($this->validateInputs()) {
            $this->infoService(Input::get('servicio_id'));
        }
        return $this->returnJson();
    }

    public function getInfoEquipment() {
        if ($this->validateInputs()) {
            $this->infoEquipment(Input::get('equipo_id'));
        }
        return $this->returnJson();
    }


    public function getAvailableEquipment() {
        $service_id = (int)Input::get('servicio_id', 0);
        $start = Input::get('inicio', '');
        $date = Input::get('fecha', '');
        $ignore_id = (int)Input::get('ignore_cita_id', 0);
        $equipments_html = '';
        $first_available = false;
        if ($service_id) {
            $service = Servicio::find($service_id);
            if ($service) {
                $start = $date . ' ' . Functions::ampmto24($start);
                $start_time = strtotime($start);
                $duration = $service->duracion;
                $end = Functions::addMinutes($start_time, $duration);
                $busy_list = Cita::notCancelled()->between($start, $end, $inclusive=true)->with('Disposicion')->get();
                if ($busy_list) {
                    $equipments_html = '<div class="list-group">';
                    //$equipments = $service->equipos;
                    $equipments = DB::table('vw_servicio_equipo')->where('servicio_id', '=', $service_id)->get();
                    foreach ($equipments as $equipment) {
                        $badge = Lang::get(self::LANG_FILE . '.available');
                        $in_use = false;
                        $last_start_time = false;
                        $last_end_time = false;
                        foreach ($busy_list as $o) {
                            if ($o->disposicion->equipo_id == $equipment->equipo_id && $o->id != $ignore_id) {
                                if ($last_start_time !== false) {
                                    if ($this->isOverlapping($o->inicio, $o->fin, $last_start_time, $last_end_time)) {
                                        $in_use = true;
                                        break;
                                    }
                                }
                                else {
                                    $in_use = true;
                                    break;
                                }
                                $last_start_time = $o->inicio;
                                $last_end_time = $o->fin;
                            }
                        }
                        if ($in_use) {
                            $badge = '<i class="fa fa-exclamation-triangle"></i> &nbsp;' . Lang::get(self::LANG_FILE . '.not_available');
                        }
                        else {
                            if ($first_available === false) {
                                $first_available = $equipment->equipo_id;
                            }
                        }

                        $model = Functions::encloseStr($equipment->modelo, ' - ', '');
                        $equipments_html .= <<<EOT
                            <a href="#" class="list-group-item equipment-btn" attr-id="{$equipment->equipo_id}">
                                {$equipment->equipo}{$model}
                                <span class="badge">{$badge}</span>
                            </a>
EOT;
                    }
                    $equipments_html .= '</div>';
                    //no available equipments
                    if ($first_available === false) {
                        $first_available = 0;
                        $this->setReturn('msg', Lang::get('citas.overlap_equipment'));
                    }
                }
            }
        }
        $this->setReturn('btns_list', $equipments_html);
        if ($first_available !== false) {
            $this->setReturn('available', $first_available);
        }
        return $this->returnJson();
    }


    public function calendarActionPost() {
        $cita_id = (int)Input::get('cita_id');
        $action = Input::get('action');
        $val = Input::get('val');

        if ($cita_id > 0) {
            $now = strtotime('-30 minutes'); //now, with a 30 minutes span
            $model = self::MODEL;
            $item = $model::find($cita_id);
            switch ($action) {
                case 'set_state':
                    $allowed = false;
                    $val = (int)$val;
                    //cannot change state after marked as done unless is admin
                    if ($val != Cita::DONE || Auth::user()->admin) {
                        switch ($val) {
                            case Cita::UNCONFIRMED:
                                $allowed = true;
                                break;

                            case Cita::CONFIRMED:
                                if (User::canConfirmOrCancelCita()) {
                                    $paciente = $item->persona;
                                    if ($paciente) {
                                        if (empty($paciente->dni)) {
                                            $this->setReturn('dni_required', $paciente->id);
                                            break;
                                        }
                                    }

                                    $nexts = (int)Input::get('grouped_nexts_apply');
                                    if ($nexts == 1) {
                                        $nexts = trim(Input::get('grouped_nexts'), ',');
                                        $nexts = explode(',', $nexts);
                                        foreach ($nexts as $next) {
                                            $next = (int)$next;
                                            if ($next > 0) {
                                                $next_item = $model::find($next);
                                                if ($next_item) {
                                                    $next_item->estado = Cita::CONFIRMED;
                                                    $next_item->save();
                                                    $to_log = serialize($next_item->toArray());
                                                    ActionLog::log(self::MODEL . ' changed_state', $to_log, $next_item->id);
                                                }
                                            }
                                        }
                                    }
                                    $allowed = true;
                                }
                                break;

                            case Cita::CANCELLED:
                                if (User::canConfirmOrCancelCita()) {
                                    $allowed = true;
                                    //when cancelling all grouped events
                                    $nexts = (int)Input::get('grouped_nexts_apply');
                                    if ($nexts == 1) {
                                        $nexts = trim(Input::get('grouped_nexts'), ',');
                                        $nexts = explode(',', $nexts);
                                        foreach ($nexts as $next) {
                                            $next = (int)$next;
                                            if ($next > 0) {
                                                $next_item = $model::find($next);
                                                if ($next_item && ($next_item->estado == Cita::UNCONFIRMED || $next_item->estado == Cita::CONFIRMED)) {
                                                    $next_item->estado = Cita::CANCELLED;
                                                    $next_item->save();
                                                    $to_log = serialize($next_item->toArray());
                                                    ActionLog::log(self::MODEL . ' changed_state', $to_log, $next_item->id);
                                                }
                                            }
                                        }
                                    }
                                }
                                break;

                            case Cita::DONE:
                                if (User::canChangeCitaStateToDone()) {
                                    //if not admin, then check time
                                    if (!Auth::user()->admin) {
                                        $inicio = strtotime($item->inicio);
                                        if ($now < $inicio) {
                                            $this->setReturn('msg', Lang::get(self::LANG_FILE . '.not_yet_started'));
                                            $allowed = false;
                                            break;
                                        }
                                    }
                                    $allowed = true;
                                }
                                break;
                        }
                    }
                    if ($allowed) {
                        $item->estado = $val;
                        $to_log = serialize($item->toArray());
                        ActionLog::log(self::MODEL . ' changed_state', $to_log, $item->id);
                        $item->save();
                    }
                    $this->setReturn('cita_id', $cita_id);
                    $this->setReturn('state', $item->estado);
                    $this->setReturn('time_diff', strtotime($item->inicio) - $now);
                    break;

                case 'get_state':
                    $this->setReturn('cita_id', $cita_id);
                    $this->setReturn('state', $item->estado);
                    $this->setReturn('time_diff', strtotime($item->inicio) - $now);
                    $properties = Cita::getProperties($item);
                    $properties['date'] = Functions::longDateFormat($properties['start']);
                    $properties['range'] = Functions::justTime($properties['start'], true, true, true) . ' — ' . Functions::justTime($properties['end'], true, true, true);
                    $this->setReturn('cita', $properties);
                    break;
            }
        }
        return $this->returnJson();
    }


    public function getNoteId() {
        $cita_id = (int)Input::get('cita_id');
        $note_id = 0;
        $note_content = '';
        $cita = Cita::find($cita_id);
        if ($cita) {
            $note = $cita->nota;
            if ($note) {
                $note_id = (int)$note->id;
                $note_content = $note->contenido;
            }
        }
        $this->setReturn('nota_id', $note_id);
        $this->setReturn('nota', $note_content);
        return $this->returnJson();
    }


    public function findInCalendar() {
        $query = trim(Input::get('query'));
        $page = 1;
        $search_fields = array(
            'paciente_nombre',
            'paciente_dni'
        );
        $match_total = 0;

        if (strlen($query)) {
            $records = $this->buscarTabla('vw_cita', $query, $page, $search_fields, $match_total, null, array('inicio', 'DESC'));

            if ($match_total > 0) {
                foreach ($records as $record) {
                    $this->setReturn('cita_id', $record->id);
                    $this->setReturn('fecha', str_replace(' ', 'T', $record->inicio));
                    break; //only using first one
                }
            }
        }
        else {
            return $this->setError( Lang::get('global.not_found') );
        }
        return $this->returnJson();
    }


    public function getFullDate() {
        $date = Input::get('date');
        if (count(explode('-', $date)) == 3) {
            $this->setReturn('date', Functions::longDateFormat($date));
            return $this->returnJson();
        }
        return $this->setError(Lang::get('global.wrong_action'));
    }

}