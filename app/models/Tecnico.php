<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 28/06/15
 * Time: 11:32 AM
 */

class Tecnico extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'cod_dicom',
        'persona_id'
    );

    protected $table = 'tecnico';

    protected $searchable = array(
        'cod_dicom'
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
            'id'            => 'integer|min:0',
            'cod_dicom'     => 'required|max:63|unique:tecnico,cod_dicom,' . $ignore_id,
            'persona_id'    => 'required|integer|exists:persona,id'
        );
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    //RELACIONES:
    public function cita() {
        return $this->hasMany('Cita', 'tecnico_id', 'id');
    }

    public function persona() {
        return $this->hasOne('Persona', 'id', 'persona_id');
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