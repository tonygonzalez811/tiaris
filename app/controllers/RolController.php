<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 24/12/14
 * Time: 11:08 AM
 */

class RolController extends BaseController {

    //const PAGE_LIMIT = 5;

    const MODEL = 'Rol';

    public static function getRoles() {
        return Functions::langArray('usuarios', Rol::get(array('id','nombre'))->toArray(), 'nombre', 'id');
    }

}