<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 26/02/15
 * Time: 02:57 PM
 */

class Servicio extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'nombre',
        'descripcion',
        'duracion',
        'validar_horario',
        'validar_equipo',
        'validar_doctor',
        'validar_tecnico',
        'validar_consultorio'
    );

    protected $table = 'servicio';

    protected $searchable = array(
        'nombre',
        'descripcion'
    );

    protected $booleans = array(
        'validar_horario',
        'validar_equipo',
        'validar_doctor',
        'validar_tecnico',
        'validar_consultorio'
    );

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
            'id'                    => 'integer|min:1',
            'nombre'                => 'required|max:63',
            'descripcion'           => 'max:127',
            'duracion'              => 'integer|min:0|max:1440',
            'validar_horario'       => 'in:on,1,0',
            'validar_equipo'        => 'in:on,1,0',
            'validar_doctor'        => 'in:on,1,0',
            'validar_tecnico'       => 'in:on,1,0',
            'validar_consultorio'   => 'in:on,1,0',
        );
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    //RELACIONES:
    public function equipos() {
        return $this->belongsToMany('Equipo', 'disposicion', 'servicio_id', 'equipo_id');
    }

    public function doctores() {
        return $this->belongsToMany('Doctor', 'disposicion', 'servicio_id', 'doctor_id');
    }

    public function tecnicos() {
        return $this->belongsToMany('Tecnico', 'disposicion', 'servicio_id', 'tecnico_id');
    }

    public function consultorios() {
        return $this->belongsToMany('Consultorio', 'disposicion', 'servicio_id', 'consultorio_id');
    }

    public function horarios() {
        return $this->hasMany('Horario', 'servicio_id', 'id');
    }


    //ATRIBUTOS:
    public function getNombreAttribute()
    {
        return ucwords($this->attributes['nombre']);
    }

    //FILTROS:


    //GETTERS:
    public static function getWithEquipments() {
        return array();//DB::table('servicios_equipos')->get();
    }

    public function getSearchable() {
        return $this->searchable;
    }

    public function getBooleans() {
        return $this->booleans;
    }

    public function getDeletableModels() {
        return $this->deletable_models;
    }

}