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
        'consultorio_id'
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
            'tecnico_id'         => 'integer|min:1',
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
        $item = self::where('servicio_id', '=', $service_id);
        if (is_array($data)) {
            if (isset($data['equipo_id']) && $data['equipo_id']) $item = $item->where('equipo_id', $data['equipo_id']);
            if (isset($data['doctor_id']) && $data['doctor_id']) $item = $item->where('doctor_id', $data['doctor_id']);
            if (isset($data['tecnico_id']) && $data['tecnico_id']) $item = $item->where('tecnico_id', $data['tecnico_id']);
            if (isset($data['consultorio_id']) && $data['consultorio_id']) $item = $item->where('consultorio_id', $data['consultorio_id']);
            if (!$create_if_not_exists) $item = $item->where('seleccionable', '=', true);
        }

        $item = $item->first();

        if ($item && $item->id > 0) {
            return $item->id;
        }
        elseif ($create_if_not_exists && is_array($data) && count($data)) {
            $data['servicio_id'] = $service_id;

            //sets it to 'not selectable' because it will only be available for an user who can create new dispositions
            $data['seleccionable'] = false;

            $created = self::create($data);
            if ($created) {
                return $created->id;
            }
        }
        return false;
    }

}