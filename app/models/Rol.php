<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 06/09/14
 * Time: 11:51 PM
 */

class Rol extends Eloquent {

    public $timestamps = false;

    protected $fillable = array(
        'nombre',
        'descripcion'
    );

    protected $table = 'rol';

    //RELACIONES:
    public function users() {
        return $this->belongsToMany('User', 'usuario_rol', 'rol_id', 'usuario_id');
    }

}