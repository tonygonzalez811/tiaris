<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 03/07/15
 * Time: 07:20 PM
 */

class Consultorio extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'nombre',
        'descripcion'
    );

    protected $table = 'consultorio';

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
            'nombre'        => 'required|max:63|unique:consultorio,nombre,' . (int)$ignore_id,
            'descripcion'   => 'max:127'
        );
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    //RELACIONES:
    public function equipos() {
        return $this->hasMany('Equipo', 'modalidad_id', 'id');
    }


    //ASIGNACIONES:


    //FILTROS:


    //GETTERS:
    public static function getList($with_equipments_only = false) {
        $modalidades = Modalidad::orderBy('nombre');
        if ($with_equipments_only) {
            $modalidades = $modalidades->has('equipos');
        }
        $modalidades = $modalidades->get( array('id', 'nombre', 'descripcion') );
        $modalidades_arr = array();
        foreach ($modalidades as $modalidad) {
            $modalidades_arr[$modalidad->id] = $modalidad->nombre . Functions::encloseStr($modalidad->descripcion, '  -  ', '');
        }
        return $modalidades_arr;
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


    public static function getAllInUse() {
        return DB::table('vw_servicio_equipo')->groupBy('modalidad_id')->groupBy('modalidad')->orderBy('modalidad')->lists('modalidad', 'modalidad_id');
    }

}