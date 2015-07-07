<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 10/03/15
 * Time: 09:05 PM
 */

class Nota extends Eloquent {

    public $timestamps = true;

    protected $fillable = array(
        'contenido',
        'cita_id'
    );

    protected $table = 'nota';

    protected $searchable = array(
        'contenido'
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
            'contenido'     => 'required|max:511',
            'cita_id'       => 'required|integer|exists:cita,id'
        );
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    //RELACIONES:
    public function cita() {
        return $this->belongsTo('Cita', 'cita_id', 'id');
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