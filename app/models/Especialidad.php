<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 04/07/15
 * Time: 11:10 AM
 */

class Especialidad extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'nombre',
        'descripcion'
    );

    protected $table = 'especialidad';

    protected $searchable = array(
        'nombre',
        'descripcion'
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
            'nombre'        => 'required|max:63|unique:especialidad,nombre,' . (int)$ignore_id,
            'descripcion'   => 'max:127'
        );
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    //RELACIONES:
    public function doctores() {
        return $this->hasMany('Doctor', 'especialidad_id', 'id');
    }


    //ASIGNACIONES:


    //FILTROS:


    //GETTERS:
    public static function getList($with_doctors_only = false) {
        $especialidades = Especialidad::orderBy('nombre');
        if ($with_doctors_only) {
            $especialidades = $especialidades->has('doctores');
        }
        $especialidades = $especialidades->get( array('id', 'nombre', 'descripcion') );
        $especialidades_arr = array();
        foreach ($especialidades as $especialidad) {
            $especialidades_arr[$especialidad->id] = $especialidad->nombre . Functions::encloseStr($especialidad->descripcion, '  -  ', '');
        }
        return $especialidades_arr;
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