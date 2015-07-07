<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 26/04/15
 * Time: 5:54 PM
 */

class UserGrupo extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'nombre'
    );

    protected $table = 'grupo';

    protected $searchable = array(
        'nombre'
    );

    protected $booleans = array(

    );

    protected $deletable_models = array(

    );

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
            'nombre'    => 'required|max:45|unique:grupo,nombre,' . (int)$ignore_id
        );
        if ($field != null) {
            return $rules[$field];
        }
        else {
            return $rules;
        }
    }

    //RELACIONES:
    public function usuarios() {
        return $this->hasMany('User', 'grupo_id', 'id');
    }


    //SCOPES:


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