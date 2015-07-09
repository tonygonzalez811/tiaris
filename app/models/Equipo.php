<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 27/02/15
 * Time: 03:30 PM
 */

class Equipo extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'nombre',
        'descripcion',
        'modelo',
        'serial',
        'cod_dicom',
        'host',
        'modalidad_id'
    );

    protected $table = 'equipo';

    protected $searchable = array(
        'nombre',
        'descripcion',
        'modelo',
        'serial',
        'cod_dicom',
        'host'
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
            'id'            => 'integer|min:1',
            'nombre'        => 'required|max:63',
            'descripcion'   => 'max:127',
            'modelo'        => 'max:63',
            'serial'        => 'max:31',
            'cod_dicom'     => 'max:31',
            'host'          => 'max:31',
            'avatar'        => 'image',
            'modalidad_id'  => 'integer|min:1|exists:modalidad,id'
        );
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    //RELACIONES:
    public function modalidad() {
        return $this->belongsTo('Modalidad', 'modalidad_id', 'id');
    }

    public function servicios() {
        return $this->belongsToMany('Servicio', 'disposicion', 'equipo_id', 'servicio_id');
    }


    //ASIGNACIONES:


    //FILTROS:
    public function scopeConModalidad($query, $val) {
        return $query->where('modalidad_id', '=', (int)$val);
    }

    //GETTERS:
    public static function getList($modalidad_id = null) {
        $equipos = self::orderBy('nombre');
        if ($modalidad_id !== null) {
            $equipos = $equipos->conModalidad($modalidad_id);
        }
        $equipos = $equipos->get( array('id', 'nombre', 'modelo') );

        $equipos_arr = array();
        foreach ($equipos as $equipo) {
            $equipos_arr[] = $equipo->nombre . Functions::encloseStr($equipo->modelo, ' - ', '');
        }
        return $equipos_arr;
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

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('avatar');

}