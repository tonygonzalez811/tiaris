<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 26/06/15
 * Time: 11:34 AM
 */

class Disposicion extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'servicio_id',
        'equipo_id',
        'doctor_id',
        'tecnico_id',
        'consultorio_id',
        'seleccionable'
    );

    protected $table = 'disposicion';

    protected $searchable = array(
        'id'
    );

    protected $booleans = array();

    protected $deletable_models = array();

    /**
     * Devuélve las reglas de validación para un campo específico o el arreglo de reglas por defecto.
     *
     * @param string $field     Nombre del campo del que se quiere las reglas de validación.
     * @param int $ignore_id    ID del elemento que se está editando, si es el caso.
     * @return array
     */
    public static function getValidationRules($field = null, $ignore_id = 0) {
        $rules = array(
            'id'                => 'integer|min:0',
            'servicio_id'       => 'integer|min:1',
            'equipo_id'         => 'integer|min:1',
            'doctor_id'         => 'integer|min:1',
            'tecnico_id'        => 'integer|min:1',
            'consultorio_id'    => 'integer|min:1'
        );
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    //RELACIONES:
    public function citas() {
        return $this->hasMany('Cita', 'disposicion_id', 'id');
    }

    public function servicio() {
        return $this->belongsTo('Servicio', 'servicio_id', 'id');
    }

    public function equipo() {
        return $this->belongsTo('Equipo', 'equipo_id', 'id');
    }

    public function doctor() {
        return $this->belongsTo('Doctor', 'doctor_id', 'id');
    }

    public function tecnico() {
        return $this->belongsTo('Tecnico', 'tecnico_id', 'id');
    }

    public function consultorio() {
        return $this->belongsTo('Consultorio', 'consultorio_id', 'id');
    }

    public function horarios() {
        return $this->hasMany('Horario', 'disposicion_id', 'id');
    }

    //ASIGNACIONES:


    //FILTROS:


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


    public static function findFor($service_id, $data, $create_if_not_exists = false) {
        // NOTE: the commented code was for allowing any user to create a disposition when validating an item was set to false

        /*$v_equipo = $v_doctor = $v_tecnico = $v_consultorio = true;
        $recheck = false;*/

        /*$service = Servicio::find($service_id);
        if ($service) {
            $v_equipo = $service->validar_equipo;
            $v_doctor = $service->validar_doctor;
            $v_tecnico = $service->validar_tecnico;
            $v_consultorio = $service->validar_consultorio;
            $recheck = !$v_equipo || !$v_doctor || !$v_tecnico || !$v_consultorio;
        }*/

        //unsets empty values
        foreach ($data as $key => $val) {
            if (empty($val)) unset($data[$key]);
        }

        //$item = self::where('servicio_id', '=', $service_id);
        $item_check = self::where('servicio_id', '=', $service_id);
        if (is_array($data)) {
            //equipo
            if (isset($data['equipo_id'])) {
                //if ($v_equipo) $item = $item->where('equipo_id', $data['equipo_id']);
                $item_check = $item_check->where('equipo_id', $data['equipo_id']);
            }
            else {
                $item_check = $item_check->whereNull('equipo_id');
                //$recheck = true;
            }
            //doctor
            if (isset($data['doctor_id'])) {
                //if ($v_doctor) $item = $item->where('doctor_id', $data['doctor_id']);
                $item_check = $item_check->where('doctor_id', $data['doctor_id']);
            }
            else {
                $item_check = $item_check->whereNull('doctor_id');
                //$recheck = true;
            }
            //tecnico
            if (isset($data['tecnico_id'])) {
                //if ($v_tecnico) $item = $item->where('tecnico_id', $data['tecnico_id']);
                $item_check = $item_check->where('tecnico_id', $data['tecnico_id']);
            }
            else {
                $item_check = $item_check->whereNull('tecnico_id');
                //$recheck = true;
            }
            //consultorio
            if (isset($data['consultorio_id'])) {
                //if ($v_consultorio) $item = $item->where('consultorio_id', $data['consultorio_id']);
                $item_check = $item_check->where('consultorio_id', $data['consultorio_id']);
            }
            else {
                $item_check = $item_check->whereNull('consultorio_id');
                //$recheck = true;
            }
        }
        if (!$create_if_not_exists) {
            //$item = $item->where('seleccionable', '=', 'true');
            $item_check = $item_check->where('seleccionable', '=', 'true');
        }

        //$item = $item->first();

        /*if ($item && $item->id > 0) {
            if (!$recheck) {
                return $item->id;
            }
            else {*/
                $item_check = $item_check->first();
                if ($item_check && $item_check->id > 0) {
                    return $item_check->id;
                }
            /*}
        }*/
        elseif (!$create_if_not_exists) return false;

        //creates the item
        if (!is_array($data)) $data = array();
        $data['servicio_id'] = $service_id;

        //sets it to 'not selectable' because it will only be available for an user who can create new dispositions
        $data['seleccionable'] = 'false';//$create_if_not_exists ? 'false' : 'true';

        $created = self::create($data);
        if ($created) {
            return $created->id;
        }
        return false;
    }

}