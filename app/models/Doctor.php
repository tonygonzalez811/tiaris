<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 28/06/15
 * Time: 11:23 AM
 */

class Doctor extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'especialidad_id',
        'numero',
        'persona_id'
    );

    protected $table = 'doctor';

    protected $searchable = array(
        'numero'
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
            'especialidad_id'   => 'required|integer|min:1',
            'numero'            => 'required|max:63|unique:doctor,numero,' . $ignore_id,
            'persona_id'        => 'required|integer|exists:persona,id'
        );
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    //RELACIONES:
    public function cita() {
        return $this->hasMany('Cita', 'doctor_id', 'id');
    }

    public function persona() {
        return $this->hasOne('Persona', 'id', 'persona_id');
    }

    public function especialidad() {
        return $this->belongsTo('Especialidad', 'especialidad_id', 'id');
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

}