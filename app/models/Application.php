<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 6/24/2015
 * Time: 10:42 AM
 */

class Application {

    /**
     * Procedimiento para crear los registros necesarios para que el sistema funcione
     */
    public static function instalar() {
        Application::crearAdminDefecto();
        Application::crearRoles();
    }


    /**
     * Crea un usuario para poder ingresar al sistema durante la instalación
     */
    private static function crearAdminDefecto() {
        $user = User::count();
        if (!$user) {
            User::create(array(
                'id'        => 1,
                'nombre'    => 'admin',
                'password'  => 'ti_0123', //<-- cambiar
                'activo'    => 1,
                'admin'     => 1
            ));
        }
    }

    /**
     * Crea los roles que se usarán para identificar a los usuarios
     */
    private static function crearRoles() {
        $roles = Rol::count();
        if (!$roles) {
            $roles = array(
                array('id' => 1, 'nombre' => 'doctor'),
                array('id' => 2, 'nombre' => 'recepcionista'),
                array('id' => 3, 'nombre' => 'paciente'),
                array('id' => 4, 'nombre' => 'tecnico')
            );

            Rol::insert($roles);
        }
    }

} 