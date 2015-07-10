<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 09/07/15
 * Time: 10:27 AM
 */

class Tiaris extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'codigo',
        'fechahora',
        'idadmision',
        'cedula',
        'apellidos',
        'nombres',
        'fechanacimiento',
        'sexo',
        'nombreestudio',
        'modalidad',
        'apedoctor',
        'nomdoctor',
        'origen',
        'procesado'
    );

    protected $table = 'listadetrabajo';

    protected $searchable = array();

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
        $rules = array();
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    //ATRIBUTOS:



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


    public static function getAllInUse() {
        return DB::table('vw_servicio_equipo')->groupBy('modalidad_id')->groupBy('modalidad')->orderBy('modalidad')->lists('modalidad', 'modalidad_id');
    }

}