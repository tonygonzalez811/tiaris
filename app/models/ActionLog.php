<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 4/5/2015
 * Time: 11:21 AM
 */

class ActionLog extends Eloquent {

    public $timestamps = true;

    protected $fillable = array(
        'usuario_id',
        'accion',
        'objeto',
        'objeto_id'
    );

    protected $table = 'log';

    protected $searchable = array(
        'accion'
    );

    protected $booleans = array();

    protected $deletable_models = array();

    public static function log($action, $target = null, $target_id = null) {
        ActionLog::create(array(
            'usuario_id' => Auth::user()->id,
            'accion' => $action,
            'objeto' => $target,
            'objeto_id' => $target_id
        ));
    }

    //scopes
    public function scopeLatest($query) {
        return $query->orderBy('updated_at', 'DESC')->take(7);
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