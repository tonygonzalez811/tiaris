<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 25/01/15
 * Time: 02:23 PM
 */

class Cita extends Eloquent {

    public $timestamps = true;

    protected $fillable = array(
        'fecha',
        'inicio',
        'fin',
        'estado',
        'persona_id',
        'usuario_id',
        'disposicion_id'
    );

    protected $table = 'cita';

    protected $searchable = array(
        'fecha'
    );

    protected $booleans = array();

    protected $deletable_models = array();

    const UNCONFIRMED = 0;
    const DONE = 1;
    const CONFIRMED = 2;
    const CANCELLED = 3;

    const ERR_SERVICE_TIME = 1;
    const ERR_DOCTOR = 2;
    const ERR_TECHNICIAN = 3;
    const ERR_PATIENT = 4;
    const ERR_EQUIPMENT = 5;
    const ERR_OFFICE = 6;

    public static function state($index = null) {
        $states = array(
            self::UNCONFIRMED   => 'por_confirmar',
            self::DONE          => 'realizada',
            self::CONFIRMED     => 'confirmada',
            self::CANCELLED     => 'cancelada'
        );
        if ($index === null) return $states;
        return $states[$index];
    }

    /**
     * Devuélve las reglas de validación para un campo específico o el arreglo de reglas por defecto.
     *
     * @param string $field     Nombre del campo del que se quiere las reglas de validación.
     * @param int $ignore_id    ID del elemento que se está editando, si es el caso.
     * @return array
     */
    public static function getValidationRules($field = null, $ignore_id = 0) {
        if (isset($_POST['doctor_id'])) $ignore_id = 0;
        $rules = array(
            'id'                    => 'integer|min:0',
            'fecha'                 => 'required|date_format:Y-m-d',
            'inicio'                => array('regex:/(0[0-9]|1[0-2]):([0-5][0-9]) (AM|PM)/'),
            'fin'                   => array('regex:/(0[0-9]|1[0-2]):([0-5][0-9]) (AM|PM)/'),
            'inicio_submit'         => array('regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'),
            'fin_submit'            => array('regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'),
            'estado'                => 'integer',
            'doctor_id'             => 'integer|exists:doctor,id',
            'tecnico_id'            => 'integer|exists:tecnico,id',
            //'persona_id'            => ($ignore_id == 0 ? 'required|' : '') . 'integer|exists:persona,id',
            'persona_id'            => 'integer|exists:persona,id',
            'servicio_id'           => ($ignore_id == 0 ? 'required|' : '') . 'integer|exists:servicio,id',
            'equipo_id'             => 'integer|exists:equipo,id',
            'disposicion_id'        => 'integer|exists:disposicion,id'
        );
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    //RELACIONES:
    public function persona() {
        return $this->belongsTo('Persona', 'persona_id', 'id');
    }

    public function disposicion() {
        return $this->belongsTo('Disposicion', 'disposicion_id', 'id');
    }

    public function nota() {
        return $this->hasOne('Nota', 'cita_id', 'id');
    }


    //ASIGNACIONES:
    public function setInicioAttribute($value) {
        if (!empty($value)) {
            if (count(explode('-', $value)) == 3) { //Y-m-d H:i:s
                $this->attributes['inicio'] = $value;
            }
            else { //h:i a
                if (!empty($this->fecha)) {
                    $this->attributes['inicio'] = $this->fecha . ' ' . Functions::ampmto24($value) . ':00';
                } else {
                    $this->attributes['inicio'] = Session::get('input_fecha') . ' ' . Functions::ampmto24($value) . ':00';
                }
            }
        }
        else {
            $this->attributes['inicio'] = null;
        }
    }

    public function setFinAttribute($value) {
        if (!empty($value)) {
            if (count(explode('-', $value)) == 3) { //Y-m-d H:i:s
                $this->attributes['fin'] = $value;
            }
            else { //h:i a
                if (!empty($this->fecha)) {
                    $this->attributes['fin'] = $this->fecha . ' ' . Functions::ampmto24($value) . ':00';
                } else {
                    $this->attributes['fin'] = Session::get('input_fecha') . ' ' . Functions::ampmto24($value) . ':00';
                }
            }
        }
        else {
            $this->attributes['fin'] = null;
        }
    }

    public function setDoctorIdAttribute($value) {
        $this->attributes['doctor_id'] = empty($value) ? null : (int)$value;
    }

    public function setTecnicoIdAttribute($value) {
        $this->attributes['tecnico_id'] = empty($value) ? null : (int)$value;
    }


    //FILTROS:
    public function scopeLatestOnes($query) {
        return $query->where('fecha', '>', date('Y-m-d', strtotime('-1 week')));
    }

    public function scopeFromDate($query, $val) {
        $date = Functions::explodeDateTime($val, true);
        if (checkdate($date['month'], $date['day'], $date['year'])) {
            return $query->where('fecha', '>=', $val);
        }
        return $query;
    }

    public function scopeToDate($query, $val) {
        $date = Functions::explodeDateTime($val, true);
        if (checkdate($date['month'], $date['day'], $date['year'])) {
            return $query->where('fecha', '<=', $val);
        }
        return $query;
    }

    public function scopeToNow($query) {
        return $query->where('inicio', '<=', date('Y-m-d H:i:s', strtotime('-2 hours')));
    }

    public function scopeNotSent($query) {
        return $query->where('enviado', '=', 'false');
    }

    public function scopeForToday($query) {
        return $query->where('fecha', '=', date('Y-m-d'));
    }

    public function scopeForDay($query, $day) {
        return $query->where('fecha', '=', $day);
    }

    public function scopeBetween($query, $val1, $val2, $inclusive = false) {
        $eq = $inclusive ? '=' : '';
        return $query->where(function ($query) use ($val1, $val2, $eq) {
            $query->where(function ($query) use ($val1, $val2, $eq) { //top collapses
                $query->where('inicio', '>' . $eq, $val1)
                    ->where('inicio', '<', $val2);
            })->orWhere(function ($query) use ($val1, $val2, $eq) { //bottom collapses
                $query->where('fin', '>', $val1)
                    ->where('fin', '<' . $eq, $val2);
            })->orWhere(function ($query) use ($val1, $val2, $eq) { //everything collapses
                $query->where('inicio', '<' . $eq, $val1)
                    ->where('fin', '>' . $eq, $val2);
            });
        });
    }

    public function scopeNotCancelled($query) {
        return $query->where('estado', '<>', self::CANCELLED);
    }

    public function scopeCancelled($query) {
        return $query->where('estado', '=', self::CANCELLED);
    }

    public function scopeDone($query) {
        return $query->where('estado', '=', self::DONE);
    }

    public function scopeTotal($query, $val) {
        return $query->where('persona_id', '=', $val)->where('estado', '=', self::DONE); //all finished events for a patient
    }

    public static function getStatesCount($from = false, $to = false) {
        $results = DB::table('cita')->selectRaw(DB::raw('estado AS "index",COUNT(id) AS "total"'))->groupBy('estado');
        if (!empty($from)) {
            $results->where('fecha', '>=', $from);
        }
        if (!empty($to)) {
            $results->where('fecha', '<=', $to);
        }
        return $results->get();
    }

    public static function getServiceCount($from = false, $to = false) {
        $results = DB::table('cita')->selectRaw(DB::raw('servicio_id AS "index",COUNT(id) AS "total"'))->groupBy('servicio_id');
        if (!empty($from)) {
            $results->where('fecha', '>=', $from);
        }
        if (!empty($to)) {
            $results->where('fecha', '<=', $to);
        }
        return $results->get();
    }


    //GETTERS:
    public function getSearchable() {
        return $this->searchable;
    }

    public function getBooleans() {
        return $this->booleans;
    }

    public function getDeletableModels() {
        return $this->deletable_models;
    }


    public static function getGroupedItemsIds($cita_id, $item = null) {
        $ids = array();
        if ($cita_id > 0) {
            if ($item == null) {
                $item = self::find($cita_id);
            }
            if ($item) {
                $end = $item->fin;
                $items = self::where('persona_id', '=', $item->persona_id)->where('fecha', '=', $item->fecha)->where('inicio', '>=', $item->fin)->orderBy('inicio')->get( array('id', 'inicio', 'fin') );
                foreach ($items as $next) {
                    if ($next->inicio == $end) {
                        $ids[] = $next->id;
                        $end = $next->fin;
                    } else break;
                }
            }
        }
        return $ids;
    }

    public static function moveGroupedItems($ids, $start_time, $parent_id, &$items) {
        $items_moved = array();
        foreach ($ids as $id) {
            if ($id > 0) {
                $item = self::find($id);
                if ($item) {
                    $duration = strtotime($item->fin) - strtotime($item->inicio);
                    $item->inicio = $start_time;
                    $item->fin = $start_time = date('Y-m-d H:i', strtotime($start_time) + $duration);
                    $ids[] = $parent_id;
                    $error = 0;
                    if (self::isOkToPlace($item, $ids, $error)) {
                        //$item->save();
                        $items_moved[] = $item;
                    }
                    else {
                        return false;
                    }
                }
            }
        }
        $items = $items_moved;
        return true;
    }

    public static function isOkToPlace($item, $grouped_ids = null, &$error, $validate_time=true) {
        //check that the service is available for the current time
        if ($validate_time) {
            if (!self::availableServiceTime($item)) {
                $error = self::ERR_SERVICE_TIME;
                return false;
            }
        }

        //get all the events in the time range
        $overlapping = self::getOverlappingOnes($item);

        if (count($overlapping)) {
            //get the ids of the following grouped events (if any)
            if ($grouped_ids === null) {
                $grouped_ids = self::getGroupedItemsIds($item->id, $item);
            }

            foreach ($overlapping as $o) {
                if ($o->id == $item->id) continue; //not going to validate against itself
                if (in_array($o->id, $grouped_ids)) continue; //not going to validate against grouped items

                if (!self::availablePatient($item, $o)) { $error = self::ERR_PATIENT; return false; }
                if (!self::availableDoctor($item, $o)) { $error = self::ERR_DOCTOR; return false; }
                if (!self::availableTechnician($item, $o)) { $error = self::ERR_TECHNICIAN; return false; }
                if (!self::availableOffice($item, $o)) { $error = self::ERR_OFFICE; return false; }
            }

            if (!self::availableEquipment($item, $overlapping, $grouped_ids)) {
                $error = self::ERR_EQUIPMENT;
                return false;
            }
        }
        return true;
    }

    public static function availableEquipment($item, $overlapping, $grouped_ids) {
        $disposicion = Disposicion::find($item->disposicion_id);
        if ($disposicion) {
            $equipo_id = $disposicion->equipo_id;
            foreach ($overlapping as $o) {
                if ($o->id == $item->id) continue; //not going to validate against itself
                if (in_array($o->id, $grouped_ids)) continue; //not going to validate against grouped items

                if ($o->disposicion->equipo_id == $equipo_id) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function availablePatient($item1, $item2) {
        return ($item1->persona_id != $item2->persona_id);
    }

    public static function availableDoctor($item1, $item2) {
        $item1_doctor_id = $item1->disposicion->doctor_id;
        return (empty($item1_doctor_id) || $item1_doctor_id != $item2->disposicion->doctor_id);
    }

    public static function availableTechnician($item1, $item2) {
        $item1_tecnico_id = $item1->disposicion->tecnico_id;
        return (empty($item1_tecnico_id) || $item1_tecnico_id != $item2->disposicion->tecnico_id);
    }

    public static function availableOffice($item1, $item2) {
        $item1_consultorio_id = $item1->disposicion->consultorio_id;
        return (empty($item1_consultorio_id) || $item1_consultorio_id != $item2->disposicion->consultorio_id);
    }

    public static function availableServiceTime($item) {
        $disposicion = Disposicion::find($item->disposicion_id);
        if ($disposicion) {
            $servicio = $disposicion->servicio;
            if ($servicio && $servicio->validar_horario) {
                return (Horario::forDateTime($item->inicio, $item->fin, $servicio->id)->count() > 0);
            }
        }
        return true;
    }

    public static function getOverlappingOnes($item) {
        return self::notCancelled()->between($item->inicio, $item->fin, $inclusive=true)->orderBy('inicio')->get();
    }

    public function isOverlapping($start1, $end1, $start2, $end2) {
        return  ($start1 >= $start2 && $start1 < $end2) ||  //start time
                ($end1 > $start2 && $end1 < $end2) ||       //end time
                ($start1 < $start2 && $end1 > $end2) ||     //complete overlapping
                ($start1 >= $start2 && $start1 <= $start2); //inside
    }

    public static function getEquipmentsIds($item) {
        $equipments = Servicio::find($item->servicio_id);
        if ($equipments) $equipments = $equipments->equipos;
        return ($equipments ? $equipments->lists('id') : false);
    }

    public static function getRowValues(&$count, $record, $show_patient = true, $show_service = true, $show_equipment = true, $show_doctor = true, $show_state = true, $show_note = true, $output_as_table = true) {
        $count++;

        if ($show_doctor) {
            //doctor
            $doctor = Doctor::find($record->doctor_id);
            if ($doctor) {
                $doctor = $doctor->persona;
                $doctor = $doctor ? Functions::firstNameLastName($doctor->nombre, $doctor->apellido) . ' (' . $doctor->dni . ')' : '';
            }
            else {
                $doctor = '';
            }
        }

        if ($show_patient) {
            //patient
            $paciente = Persona::find($record->persona_id);
            /*if ($paciente) {
                $paciente = Functions::firstNameLastName($paciente->nombre, $paciente->apellido) . ' (' . $paciente->dni . ')';
            }
            else {
                $paciente = '';
            }*/
        }

        if ($show_service) {
            //service
            $servicio = Servicio::find($record->servicio_id);
            if ($servicio) {
                $servicio = $servicio->nombre;
            }
            else {
                $servicio = '';
            }
        }

        if ($show_equipment) {
            //equipment
            $equipo = Equipo::find($record->equipo_id);
            if ($equipo) {
                $equipo = $equipo->nombre . Functions::encloseStr($equipo->modelo, ' - ', '');
            }
            else {
                $equipo = '';
            }
        }

        if ($show_state) {
            //state
            $estado = Lang::get('citas.' . Cita::state($record->estado));
        }

        if ($show_note) {
            $nota = Nota::where('cita_id', '=', $record->id)->first();
            $nota = $nota ? $nota->contenido : '';
            $nota = $output_as_table ? nl2br($nota) : Functions::nl2br($nota, ' ');
        }
        else {
            $nota = '';
        }

        //date
        $date = Functions::longDateFormat($record->inicio);
        $start = Functions::justTime($record->inicio);

        if ($output_as_table) {
            $output = "<td>{$count}</td>";
            if ($show_state) {
                $output .= "<td>{$estado}</td>";
            }
            if ($show_patient) {
                //$output .= "<td>{$paciente}</td>";
                $nombre = ucwords(strtolower($paciente->nombre));
                $apellido = ucwords(strtolower($paciente->apellido));
                $output .= "<td>{$nombre}</td>";
                $output .= "<td>{$apellido}</td>";
                $output .= "<td>{$paciente->dni}</td>";
            }
            $output .= "<td>{$date}</td>";
            $output .= "<td>{$start}</td>";
            if ($show_service) {
                $output .= "<td>{$servicio}</td>";
            }
            if ($show_equipment) {
                $output .= "<td>{$equipo}</td>";
            }
            if ($show_doctor) {
                $output .= "<td>{$doctor}</td>";
            }
            if ($show_note) {
                $output .= "<td>{$nota}</td>";
            }
        }
        else {
            $output = array($count);
            if ($show_state) $output[] = $estado;
            if ($show_patient) {
                //$output[] = $paciente;
                $output[] = ucwords(strtolower($paciente->nombre));
                $output[] = ucwords(strtolower($paciente->apellido));
                $output[] = $paciente->dni;
            }
            $output[] = $date;
            $output[] = $start;
            if ($show_service) $output[] = $servicio;
            if ($show_equipment) $output[] = $equipo;
            if ($show_doctor) $output[] = $doctor;
            if ($show_note) $output[] = $nota;
        }
        return $output;
    }

    public static function getProperties($item, $get_note = false) {
        //finding the user
        $user = $item->usuario_id > 0 ? User::find($item->usuario_id) : '';
        if ($user) {
            $user_data = $user->persona;
            if ($user_data) {
                $user = Functions::firstNameLastName($user_data->nombre, $user_data->apellido, true);
            }
            else {
                $user = $user->nombre;
            }
        }
        else $user = '';
        
        $disposicion = Disposicion::find($item->disposicion_id);

        //finding the doctor
        $doctor = $disposicion->doctor;
        if ($doctor) {
            $doctor_id = $doctor->id;
            $persona = $doctor->persona;
            if ($persona) {
                $doctor = Functions::firstNameLastName($persona->nombre, $persona->apellido);
            }
            else {
                $doctor = $doctor->numero;
            }
        }
        else {
            $doctor_id = 0;
            $doctor = Lang::get('global.not_assigned');
        }

        //finding the technician
        $technician = $disposicion->tecnico;
        if ($technician) {
            $technician_id = $technician->id;
            $persona = $technician->persona;
            if ($persona) {
                $technician = Functions::firstNameLastName($persona->nombre, $persona->apellido);
            }
            else {
                $technician = $technician->numero;
            }
        }
        else {
            $technician_id = 0;
            $technician = Lang::get('global.not_assigned');
        }

        //finding the patient
        $patient = Persona::find($item->persona_id);//$item->persona
        if ($patient) {
            $patient = Functions::firstNameLastName($patient->nombre, $patient->apellido);
        }
        else {
            $patient = '';
        }

        //finding the equipment
        $equipment = $disposicion->equipo;
        if ($equipment) {
            $equipment_id = $equipment->id;
            $equipment = $equipment->nombre . Functions::encloseStr($equipment->modelo, ' - ', '');
        }
        else {
            $equipment_id = 0;
            $equipment = '';
        }

        //finding the office
        $office = $disposicion->consultorio;
        if ($office) {
            $office_id = $office->id;
            $office = $office->nombre;
        }
        else {
            $office_id = 0;
            $office = '';
        }

        $service = $disposicion->servicio;
        $duration = $service->duracion;
        $service_id = $service->id;
        $service = $service->nombre;

        //finding note
        $note = '';
        if ($get_note) {
            $note = $item->nota;//Nota::where('cita_id', '=', $item->id)->first();
            if ($note) {
                $note = nl2br($note->contenido);
            }
        }

        return array(
            'id' => $item->id,
            'start' => $item->inicio,
            'end' => $item->fin,
            'duration' => $duration,
            'user_id' => $item->usuario_id,
            'user' => $user,
            'disposition_id' => $item->disposicion_id,
            'doctor_id' => $doctor_id,
            'doctor' => $doctor,
            'technician_id' => $technician_id,
            'technician' => $technician,
            'patient_id' => $item->persona_id,
            'patient' => $patient,
            'service_id' => $service_id,
            'service' => $service,
            'equipment_id' => $equipment_id,
            'equipment' => $equipment,
            'office_id' => $office_id,
            'office' => $office,
            'state_id' => $item->estado,
            'state' => Lang::get('citas.' . Cita::state($item->estado)),
            'note' => $note
        );
    }

    public static function getHistory($id) {
        return ActionLog::where('objeto_id', '=', $id)->where('accion', 'LIKE', 'Cita%')->orderBy('id')->get( array('id', 'created_at', 'usuario_id', 'accion', 'objeto') );
    }

}