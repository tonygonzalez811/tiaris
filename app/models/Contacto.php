<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 05/01/15
 * Time: 10:40 AM
 */

class Contacto extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'persona_id',
        'contenido',
        'tipo'
    );

    protected $table = 'contacto';

    protected $searchable = array(
        'contenido'
    );

    protected $booleans = array(

    );

    protected $deletable_models = array();

    const PHONE = 1;
    const EMAIL = 2;

    /**
     * Devuélve las reglas de validación para un campo específico o el arreglo de reglas por defecto.
     *
     * @param string $field     Nombre del campo del que se quiere las reglas de validación.
     * @param int $ignore_id    ID del elemento que se está editando, si es el caso.
     * @return array
     */
    public static function getValidationRules($field = null, $ignore_id = 0) {
        $rules = array(
            'id'        => 'integer|min:1',
            'contacto'  => 'required|max:127',
            'tipo'      => 'required|integer|min:1'
        );
        if ($field != null) {
            return $rules[$field];
        }
        else {
            return $rules;
        }
    }

    //RELACIONES:
    public function paciente() {
        return $this->belongsTo('Persona', 'persona_id', 'id');
    }

    //FILTROS:
    public function scopeTelefonos($query) {
        return $query->where('tipo', '=', 1);
    }

    public function scopeCorreos($query) {
        return $query->where('tipo', '=', 2);
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