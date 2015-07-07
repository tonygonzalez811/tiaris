<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 26/06/15
 * Time: 03:43 PM
 */

class Horario extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'dia',
        'inicio',
        'fin',
        'servicio_id'
    );

    protected $table = 'horario';

    protected $searchable = array(
        'dia'
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
            'dia'           => 'required|in:0,1,2,3,4,5,6',
            'inicio'        => array('required', 'regex:/(0[0-9]|1[0-2]):([0-5][0-9]) (AM|PM)/'),
            'fin'           => array('required', 'regex:/(0[0-9]|1[0-2]):([0-5][0-9]) (AM|PM)/'),
            'inicio_submit' => array('regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'),
            'fin_submit'    => array('regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'),
            'servicio_id'   => 'integer|min:1'
        );
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    public static function dia($index) {
        $days = array(1=>'mon', 2=>'tue', 3=>'wed', 4=>'thu', 5=>'fri', 6=>'sat', 7=>'sun');
        return Lang::get('global.' . $days[$index] . '_l');
    }

    //RELACIONES:
    public function servicio() {
        return $this->belongsTo('Servicio', 'servicio_id', 'id');
    }

    //ASIGNACIONES:


    //FILTROS:
    public function scopeForDateTime($query, $start, $end, $service_id) {
        $start = strtotime($start);
        $end = strtotime($end);

        $dia = date('N', $start);
        $start = date('H:i:s', $start);
        $end = date('H:i:s', $end);

        return $query->where('servicio_id', '=', $service_id)
                     ->where('dia', '=', $dia)
                     ->where('inicio', '<=', $start)
                     ->where('fin', '>=', $end);
    }

    public function scopeInDateTime($query, $start, $end, $service_id) {
        //$start = strtotime($start);
        //$end = strtotime($end);

        $dia = date('N', $start);
        $start = date('H:i:s', $start);
        $end = date('H:i:s', $end);

        return $query->where('servicio_id', '=', $service_id)
                     ->where('dia', '=', $dia)
                     ->where('inicio', '>=', $start)
                     ->where('fin', '<=', $end);
    }

    public function scopeCollapseUp($query, $start, $end, $service_id) {
        //$start = strtotime($start);
        //$end = strtotime($end);

        $dia = date('N', $start);
        $start = date('H:i:s', $start);
        //$end = date('H:i:s', $end);

        return $query->where('servicio_id', '=', $service_id)
                     ->where('dia', '=', $dia)
                     ->where('fin', '=', $start);
    }

    public function scopeCollapseDown($query, $start, $end, $service_id) {
        //$start = strtotime($start);
        //$end = strtotime($end);

        $dia = date('N', $start);
        //$start = date('H:i:s', $start);
        $end = date('H:i:s', $end);

        return $query->where('servicio_id', '=', $service_id)
                     ->where('dia', '=', $dia)
                     ->where('inicio', '=', $end);
    }




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