<?php

/*
 * 404 ERROR without including 'index.php' in the URL solution:
 *
 * [C:\wamp\bin\apache\apache2.4.9\conf\httpd.conf]
 *
 * find the line:
 * #LoadModule rewrite_module modules/mod_rewrite.so
 *
 * remove the # from the line.
 *
 *
 * Showing /public in the URL solution:
 *
 * Change your document root in your apache config from "your/path/to/laravel" to "your/path/to/laravel/public"
 *
 * another way, though not as good, is: (ACTUALLY DID NOT WORK, RETURNED A NOT FOUND ERROR)
 *
 * create a .htaccess file in your laravel root directory, then add this into the file:
 *
 *       <IfModule mod_rewrite.c>
 *           RewriteEngine On
 *
 *           RewriteRule ^(.*)$ public/$1 [L]
 *       </IfModule>
 */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

// Display all SQL executed in Eloquent
//TODO: disable this!! (!)
/*Event::listen('illuminate.query', function($query, $bindings, $times)
{
    $f = fopen('eloquent_query_log.txt', 'a');
    fwrite($f, $query . '[' . implode('|',$bindings) . '](' . $times . ')' . PHP_EOL . PHP_EOL);
    fclose($f);
    //var_dump($query);
});*/


Route::pattern('cita_id', '[0-9]+');
Route::pattern('equipo_id', '[0-9]+');


Route::get('/', array(
    'as' => 'inicio',
    'uses' => 'ApplicationController@mostrarDefault' //<-- change this to any other default homepage
));

Route::get('instalar', array(
    'as' => 'instalar',
    'uses' => 'ApplicationController@instalar'
));

Route::get('server_time', function() {
    return '<pre>' . Functions::longDateFormat(time(), true, false) . '</pre>'; //date(''Y-m-d h:i:s a'');
});


/**
 * Rutas disponibles cuando el usuario NO ha iniciado sesion
 */
Route::group(array('before' => 'guest'), function() {

    Route::get('inicio_sesion', array(
        'as' => 'inicio_sesion',
        'uses' => 'ApplicationController@mostrarInicioSesion'
    ));

    /*Route::get('user_activate/{code}', array(
        'as' => 'activate_account',
        'uses' => 'AccountController@activateAccount'
    ));*/

    Route::group(array('before' => 'csrf'), function() {

        Route::post('inicio_sesion_post', array(
            'as' => 'inicio_sesion_post',
            'uses' => 'UserController@iniciarSesionPost'
        ));

    });

});



/**
 * Rutas disponibles cuando el usuario ha iniciado sesion
 */
Route::group(array('before' => 'auth'), function() {

    /**
     * Main Admin Navigation Bar
     */
    Route::get('admin/inicio', array(
        'as' => 'admin_inicio',
        'uses' => 'UserController@paginaAdminInicio'
    ));

    Route::get('admin/mi_cuenta', array(
        'as' => 'mi_cuenta',
        'uses' => 'PersonaController@paginaMiCuenta'
    ));

    Route::get('admin/usuarios', array(
        'as' => 'admin_usuarios',
        'uses' => 'UserController@paginaAdminUsuarios'
    ));

    Route::get('admin/usuarios/grupos', array(
        'as' => 'admin_usuarios_grupos',
        'uses' => 'UserGrupoController@paginaAdmin'
    ));

    Route::get('admin/parentescos', array(
        'as' => 'admin_parentescos',
        'uses' => 'TipoParienteController@paginaAdmin'
    ));

    Route::get('admin/personas/{tipo?}', array(
        'as' => 'admin_pacientes',
        'uses' => 'PersonaController@paginaAdmin'
    ));

    Route::get('admin/reportes-pacientes', array(
        'as' => 'admin_reportes_pacientes',
        'uses' => 'PersonaController@paginaAdminReporte'
    ));

    Route::get('admin/citas', array(
        'as' => 'admin_citas',
        'uses' => 'CitaController@paginaAdmin'
    ));

    Route::get('admin/calendario', array(
        'as' => 'admin_calendario',
        'uses' => 'CitaController@paginaCalendario'
    ));

    Route::get('admin/modalidad', array(
        'as' => 'admin_modalidad',
        'uses' => 'ModalidadController@paginaAdmin'
    ));

    Route::get('admin/especialidad', array(
        'as' => 'admin_especialidad',
        'uses' => 'EspecialidadController@paginaAdmin'
    ));

    Route::get('admin/consultorios', array(
        'as' => 'admin_consultorios',
        'uses' => 'ConsultorioController@paginaAdmin'
    ));

    Route::get('admin/servicios', array(
        'as' => 'admin_servicios',
        'uses' => 'ServicioController@paginaAdmin'
    ));

    Route::get('admin/horarios', array(
        'as' => 'admin_horarios',
        'uses' => 'ServicioController@paginaAdminHorarios'
    ));

    Route::get('admin/servicio_categorias', array(
        'as' => 'admin_servicio_categorias',
        'uses' => 'ServicioCategoriaController@paginaAdmin'
    ));

    Route::get('admin/equipos', array(
        'as' => 'admin_equipos',
        'uses' => 'EquipoController@paginaAdmin'
    ));

    Route::get('admin/horario/{servicio_id}', array(
        'as' => 'horario_servicio',
        'uses' => 'HorarioController@paginaAdminServicio'
    ));

    Route::get('admin/equipo/{equipo_id}', array(
        'as' => 'inicio_equipo',
        'uses' => 'EquipoController@paginaAdminInicio'
    ));

    Route::get('admin/doctor/{doctor_id}/citas', array(
        'as' => 'doctor_citas',
        'uses' => 'UserController@paginaAdminDoctorCitas'
    ));

    Route::get('admin/doctor/{doctor_id}/citas_impresion', array(
        'as' => 'doctor_citas_impresion',
        'uses' => 'UserController@paginaAdminDoctorCitasPrint'
    ));

    Route::get('admin/log', array(
        'as' => 'admin_log',
        'uses' => 'LogController@paginaAdmin'
    ));

    Route::get('admin/log_item/{id}', array(
        'as' => 'admin_log_item',
        'uses' => 'LogController@paginaAdminItem'
    ));

    Route::get('admin/opciones', array(
        'as' => 'admin_config',
        'uses' => 'OpcionController@paginaAdminOpciones'
    ));

    Route::get('admin/estadisticas', array(
        'as' => 'admin_estadisticas',
        'uses' => 'CitaController@paginaAdminEstadisticas'
    ));


    //total registros
    Route::get('admin/usuarios/total', array(
        'as' => 'admin_usuarios_count_get',
        'uses' => 'UserController@totalGet'
    ));

    Route::get('admin/usuarios_grupos/total', array(
        'as' => 'admin_user_grupo_count_get',
        'uses' => 'UserGrupoController@totalGet'
    ));

    Route::get('admin/tipos_parentescos/total', array(
        'as' => 'admin_tipos_parentescos_count_get',
        'uses' => 'TipoParienteController@totalGet'
    ));

    Route::get('admin/pacientes/total', array(
        'as' => 'admin_pacientes_count_get',
        'uses' => 'PersonaController@totalGet'
    ));

    Route::get('admin/personas/total/{tipo?}', array(
        'as' => 'admin_personas_count_get',
        'uses' => 'PersonaController@totalPeronasGet'
    ));

    Route::get('admin/citas/total', array(
        'as' => 'admin_citas_count_get',
        'uses' => 'CitaController@totalGet'
    ));

    Route::get('admin/modalidad/total', array(
        'as' => 'admin_modalidad_count_get',
        'uses' => 'ModalidadController@totalGet'
    ));

    Route::get('admin/especialidad/total', array(
        'as' => 'admin_especialidad_count_get',
        'uses' => 'EspecialidadController@totalGet'
    ));

    Route::get('admin/servicio/total', array(
        'as' => 'admin_servicio_count_get',
        'uses' => 'ServicioController@totalGet'
    ));

    Route::get('admin/servicio_categoria/total', array(
        'as' => 'admin_servicio_categoria_count_get',
        'uses' => 'ServicioCategoriaController@totalGet'
    ));

    Route::get('admin/equipo/total', array(
        'as' => 'admin_equipo_count_get',
        'uses' => 'EquipoController@totalGet'
    ));

    Route::get('admin/consultorio/total', array(
        'as' => 'admin_consultorio_count_get',
        'uses' => 'ConsultorioController@totalGet'
    ));

    Route::get('admin/log/total', array(
        'as' => 'admin_log_count_get',
        'uses' => 'LogController@totalGet'
    ));

    //select searchs
    Route::get('admin/tipos_parentescos/list', array(
        'as' => 'admin_tipos_parentescos_list',
        'uses' => 'TipoParienteController@listSeekAlt'
    ));

    Route::get('admin/usuarios/list', array(
        'as' => 'admin_usuarios_list',
        'uses' => 'UserController@listSeek'
    ));

    Route::get('admin/pacientes/list', array(
        'as' => 'admin_pacientes_list',
        'uses' => 'PersonaController@listSeekAlt'
    ));

    Route::get('admin/doctores/list', array(
        'as' => 'admin_doctores_list',
        'uses' => 'PersonaController@listSeekDoctor'
    ));

    Route::get('admin/tecnicos/list', array(
        'as' => 'admin_tecnicos_list',
        'uses' => 'PersonaController@listSeekTecnico'
    ));

    Route::get('admin/servicios/list', array(
        'as' => 'admin_servicios_list',
        'uses' => 'ServicioController@listSeekAlt'
    ));

    Route::get('admin/equipos/list', array(
        'as' => 'admin_equipos_list',
        'uses' => 'EquipoController@listSeek'
    ));

    Route::get('admin/consultorios/list', array(
        'as' => 'admin_consultorios_list',
        'uses' => 'ConsultorioController@listSeek'
    ));

    Route::get('admin/especialidades/list', array(
        'as' => 'admin_especialidad_list',
        'uses' => 'EspecialidadController@listSeek'
    ));

    Route::get('admin/modalidad/equipos', array(
        'as' => 'get_equipments_by_mode',
        'uses' => 'EquipoController@equiposByModeGet'
    ));

    //calendar
    Route::get('calendario/citas', array(
        'as' => 'calendar_source',
        'uses' => 'CitaController@getCitas'
    ));

    Route::get('calendario_horario/{servicio_equipo_id?}', array(
        'as' => 'horario_calendar_source',
        'uses' => 'HorarioController@getHorario'
    ));

    Route::get('calendario_horario_editable/{servicio_equipo_id?}', array(
        'as' => 'horario_calendar_source_editable',
        'uses' => 'HorarioController@getHorarioEditable'
    ));

    //information requests
    Route::get('cita/dia_hora', array(
        'as' => 'cita_datetime_inf_get',
        'uses' => 'CitaController@getInfoDateTime'
    ));

    Route::get('cita/doctor', array(
        'as' => 'cita_doctor_inf_get',
        'uses' => 'CitaController@getInfoDoctor'
    ));

    Route::get('cita/tecnico', array(
        'as' => 'cita_technician_inf_get',
        'uses' => 'CitaController@getInfoTechnician'
    ));

    Route::get('cita/paciente', array(
        'as' => 'cita_patient_inf_get',
        'uses' => 'CitaController@getInfoPatient'
    ));

    Route::get('cita/servicio', array(
        'as' => 'cita_service_inf_get',
        'uses' => 'CitaController@getInfoService'
    ));

    Route::get('cita/equipo', array(
        'as' => 'cita_equipment_inf_get',
        'uses' => 'CitaController@getInfoEquipment'
    ));

    Route::get('cita/info', array(
        'as' => 'cita_all_inf_get',
        'uses' => 'CitaController@getAllInfo'
    ));

    Route::get('cita/equipo_disponible', array(
        'as' => 'get_available_equipments',
        'uses' => 'CitaController@getAvailableEquipment'
    ));

    Route::get('cita/nota_id', array(
        'as' => 'get_cita_note',
        'uses' => 'CitaController@getNoteId'
    ));

    Route::get('cita/doctor_indice', array(
        'as' => 'get_doctor_by_letter',
        'uses' => 'UserController@getDoctorByLetter'
    ));

    Route::get('cita/servicio_indice', array(
        'as' => 'get_service_by_category',
        'uses' => 'ServicioController@getServices'
    ));

    Route::get('calendario/buscar', array(
        'as' => 'calendar_search',
        'uses' => 'CitaController@findInCalendar'
    ));

    Route::get('fecha_completa', array(
        'as' => 'full_date',
        'uses' => 'CitaController@getFullDate'
    ));

    Route::get('cita/historial', array(
        'as' => 'get_cita_history',
        'uses' => 'CitaController@getCitaHistory'
    ));


    Route::get('doctores/estado', array(
        'as' => 'update_doctors_status',
        'uses' => 'UserController@getDoctorStatuses'
    ));

    Route::get('modalidades/estado', array(
        'as' => 'update_modalidades_status',
        'uses' => 'ModalidadController@getStatuses'
    ));

    /**
     * Form Actions
     */
    //Route::group(array('before' => 'csrf'), function() {

        // PAGINA USUARIOS

        //buscar
        Route::get('admin/usuarios/buscar', array(
            'as' => 'admin_usuarios_buscar_get',
            'uses' => 'UserController@buscarGet'
        ));

        //info
        Route::get('admin/usuarios/info', array(
            'as' => 'admin_usuarios_info_get',
            'uses' => 'UserController@infoGet'
        ));

        //datos para editar
        Route::get('admin/usuarios/datos', array(
            'as' => 'admin_usuarios_datos_get',
            'uses' => 'UserController@datosGet'
        ));

        //acciones (eliminar)
        Route::post('admin/usuarios/accion', array(
            'as' => 'admin_usuarios_accion_post',
            'uses' => 'UserController@accionPost'
        ));

        //editar
        Route::post('admin/usuarios/editar', array(
            'as' => 'admin_usuarios_editar_post',
            'uses' => 'UserController@editarPost'
        ));

        //registrar
        Route::post('admin/usuarios/registrar', array(
            'as' => 'admin_usuarios_registrar_post',
            'uses' => 'UserController@registrarPost'
        ));

        //cambiar contraseña
        Route::post('admin/mi_cuenta/cambiar_password', array(
            'as' => 'change_password_post',
            'uses' => 'UserController@changePasswordPost'
        ));

    
    
        // PAGINA MODALIDAD

        //buscar
        Route::get('admin/modalidad/buscar', array(
            'as' => 'admin_modalidad_buscar_get',
            'uses' => 'ModalidadController@buscarGet'
        ));

        //info
        Route::get('admin/modalidad/info', array(
            'as' => 'admin_modalidad_info_get',
            'uses' => 'ModalidadController@infoGet'
        ));

        //datos para editar
        Route::get('admin/modalidad/datos', array(
            'as' => 'admin_modalidad_datos_get',
            'uses' => 'ModalidadController@datosGet'
        ));

        //acciones (eliminar)
        Route::post('admin/modalidad/accion', array(
            'as' => 'admin_modalidad_accion_post',
            'uses' => 'ModalidadController@accionPost'
        ));

        //editar
        Route::post('admin/modalidad/editar', array(
            'as' => 'admin_modalidad_editar_post',
            'uses' => 'ModalidadController@editarPost'
        ));

        //registrar
        Route::post('admin/modalidad/registrar', array(
            'as' => 'admin_modalidad_registrar_post',
            'uses' => 'ModalidadController@registrarPost'
        ));
    
    
        // PAGINA ESPECIALIDAD

        //buscar
        Route::get('admin/especialidad/buscar', array(
            'as' => 'admin_especialidad_buscar_get',
            'uses' => 'EspecialidadController@buscarGet'
        ));

        //info
        Route::get('admin/especialidad/info', array(
            'as' => 'admin_especialidad_info_get',
            'uses' => 'EspecialidadController@infoGet'
        ));

        //datos para editar
        Route::get('admin/especialidad/datos', array(
            'as' => 'admin_especialidad_datos_get',
            'uses' => 'EspecialidadController@datosGet'
        ));

        //acciones (eliminar)
        Route::post('admin/especialidad/accion', array(
            'as' => 'admin_especialidad_accion_post',
            'uses' => 'EspecialidadController@accionPost'
        ));

        //editar
        Route::post('admin/especialidad/editar', array(
            'as' => 'admin_especialidad_editar_post',
            'uses' => 'EspecialidadController@editarPost'
        ));

        //registrar
        Route::post('admin/especialidad/registrar', array(
            'as' => 'admin_especialidad_registrar_post',
            'uses' => 'EspecialidadController@registrarPost'
        ));

        
        // PAGINA PERSONAS

        //buscar
        Route::get('admin/pacientes/buscar', array(
            'as' => 'admin_pacientes_buscar_get',
            'uses' => 'PersonaController@buscarGet'
        ));

        Route::get('admin/personas/buscar/{tipo?}', array(
            'as' => 'admin_personas_buscar_get',
            'uses' => 'PersonaController@buscarTipoGet'
        ));

        Route::get('admin/pacientes/reportes/buscar', array(
            'as' => 'admin_pacientes_buscar_alt_get',
            'uses' => 'PersonaController@buscarGetAlt'
        ));

        //info
        Route::get('admin/pacientes/info', array(
            'as' => 'admin_pacientes_info_get',
            'uses' => 'PersonaController@infoGet'
        ));

        //datos para editar
        Route::get('admin/pacientes/datos', array(
            'as' => 'admin_pacientes_datos_get',
            'uses' => 'PersonaController@datosGet'
        ));

        //acciones (eliminar)
        Route::post('admin/pacientes/accion', array(
            'as' => 'admin_pacientes_accion_post',
            'uses' => 'PersonaController@accionPost'
        ));

        //editar
        Route::post('admin/pacientes/editar', array(
            'as' => 'admin_pacientes_editar_post',
            'uses' => 'PersonaController@editarPost'
        ));

        //registrar
        Route::post('admin/pacientes/registrar', array(
            'as' => 'admin_pacientes_registrar_post',
            'uses' => 'PersonaController@registrarPost'
        ));

        //registrar pariente
        Route::post('admin/pacientes/registrar_pariente', array(
            'as' => 'admin_pacientes_registrar_pariente_post',
            'uses' => 'PersonaController@registrarParientePost'
        ));

        
        // PAGINA CITAS

        //buscar
        Route::get('admin/citas/buscar', array(
            'as' => 'admin_citas_buscar_get',
            'uses' => 'CitaController@buscarGetAlt'
        ));

        //info
        Route::get('admin/citas/info', array(
            'as' => 'admin_citas_info_get',
            'uses' => 'CitaController@infoGet'
        ));

        //datos para editar
        Route::get('admin/citas/datos', array(
            'as' => 'admin_citas_datos_get',
            'uses' => 'CitaController@datosGet'
        ));

        //acciones (eliminar)
        Route::post('admin/citas/accion', array(
            'as' => 'admin_citas_accion_post',
            'uses' => 'CitaController@accionPost'
        ));

        //editar
        Route::post('admin/citas/editar', array(
            'as' => 'admin_citas_editar_post',
            'uses' => 'CitaController@editarPost'
        ));

        //registrar
        Route::post('admin/citas/registrar', array(
            'as' => 'admin_citas_registrar_post',
            'uses' => 'CitaController@registrarPost'
        ));

        //validacion
        Route::post('admin/citas/chequear', array(
            'as' => 'admin_citas_check_availability_post',
            'uses' => 'CitaController@checkAvailabilityPost'
        ));

        //dni del paciente
        Route::post('admin/citas/editar_dni', array(
            'as' => 'admin_paciente_editar_dni_post',
            'uses' => 'PersonaController@setDniPost'
        ));

        //combinar paciente
        Route::post('admin/citas/combinar_paciente', array(
            'as' => 'patient_merge_post',
            'uses' => 'PersonaController@combinePost'
        ));

        Route::post('admin/citas/reporte.csv', array(
            'as' => 'admin_citas_buscar_post',
            'uses' => 'CitaController@buscarGetAlt'
        ));

        Route::get('admin/estadisticas/reporte', array(
            'as' => 'admin_report_citas_buscar_get',
            'uses' => 'CitaController@getEstadisticas'
        ));

        Route::post('admin/estadisticas/reporte.csv', array(
            'as' => 'admin_report_citas_buscar_post',
            'uses' => 'CitaController@getEstadisticas'
        ));

        
        // PAGINA CONSULTORIOS

        //buscar
        Route::get('admin/consultorio/buscar', array(
            'as' => 'admin_consultorio_buscar_get',
            'uses' => 'ConsultorioController@buscarGet'
        ));

        //info
        Route::get('admin/consultorio/info', array(
            'as' => 'admin_consultorio_info_get',
            'uses' => 'ConsultorioController@infoGet'
        ));

        //datos para editar
        Route::get('admin/consultorio/datos', array(
            'as' => 'admin_consultorio_datos_get',
            'uses' => 'ConsultorioController@datosGet'
        ));

        //acciones (eliminar)
        Route::post('admin/consultorio/accion', array(
            'as' => 'admin_consultorio_accion_post',
            'uses' => 'ConsultorioController@accionPost'
        ));

        //editar
        Route::post('admin/consultorio/editar', array(
            'as' => 'admin_consultorio_editar_post',
            'uses' => 'ConsultorioController@editarPost'
        ));

        //registrar
        Route::post('admin/consultorio/registrar', array(
            'as' => 'admin_consultorio_registrar_post',
            'uses' => 'ConsultorioController@registrarPost'
        ));


        // PAGINA SERVICIOS

        //buscar
        Route::get('admin/servicio/buscar', array(
            'as' => 'admin_servicio_buscar_get',
            'uses' => 'ServicioController@buscarGet'
        ));

        //info
        Route::get('admin/servicio/info', array(
            'as' => 'admin_servicio_info_get',
            'uses' => 'ServicioController@infoGet'
        ));

        //datos para editar
        Route::get('admin/servicio/datos', array(
            'as' => 'admin_servicio_datos_get',
            'uses' => 'ServicioController@datosGet'
        ));

        //acciones (eliminar)
        Route::post('admin/servicio/accion', array(
            'as' => 'admin_servicio_accion_post',
            'uses' => 'ServicioController@accionPost'
        ));

        //editar
        Route::post('admin/servicio/editar', array(
            'as' => 'admin_servicio_editar_post',
            'uses' => 'ServicioController@editarPost'
        ));

        //registrar
        Route::post('admin/servicio/registrar', array(
            'as' => 'admin_servicio_registrar_post',
            'uses' => 'ServicioController@registrarPost'
        ));


        // PAGINA CATEGORIAS SERVICIOS

        //buscar
        Route::get('admin/servicio_categoria/buscar', array(
            'as' => 'admin_servicio_categoria_buscar_get',
            'uses' => 'ServicioCategoriaController@buscarGet'
        ));

        //info
        Route::get('admin/servicio_categoria/info', array(
            'as' => 'admin_servicio_categoria_info_get',
            'uses' => 'ServicioCategoriaController@infoGet'
        ));

        //datos para editar
        Route::get('admin/servicio_categoria/datos', array(
            'as' => 'admin_servicio_categoria_datos_get',
            'uses' => 'ServicioCategoriaController@datosGet'
        ));

        //acciones (eliminar)
        Route::post('admin/servicio_categoria/accion', array(
            'as' => 'admin_servicio_categoria_accion_post',
            'uses' => 'ServicioCategoriaController@accionPost'
        ));

        //editar
        Route::post('admin/servicio_categoria/editar', array(
            'as' => 'admin_servicio_categoria_editar_post',
            'uses' => 'ServicioCategoriaController@editarPost'
        ));

        //registrar
        Route::post('admin/servicio_categoria/registrar', array(
            'as' => 'admin_servicio_categoria_registrar_post',
            'uses' => 'ServicioCategoriaController@registrarPost'
        ));


        // PAGINA EQUIPOS

        //buscar
        Route::get('admin/equipo/buscar', array(
            'as' => 'admin_equipo_buscar_get',
            'uses' => 'EquipoController@buscarGet'
        ));

        //info
        Route::get('admin/equipo/info', array(
            'as' => 'admin_equipo_info_get',
            'uses' => 'EquipoController@infoGet'
        ));

        //datos para editar
        Route::get('admin/equipo/datos', array(
            'as' => 'admin_equipo_datos_get',
            'uses' => 'EquipoController@datosGet'
        ));

        //acciones (eliminar)
        Route::post('admin/equipo/accion', array(
            'as' => 'admin_equipo_accion_post',
            'uses' => 'EquipoController@accionPost'
        ));

        //editar
        Route::post('admin/equipo/editar', array(
            'as' => 'admin_equipo_editar_post',
            'uses' => 'EquipoController@editarPost'
        ));

        //registrar
        Route::post('admin/equipo/registrar', array(
            'as' => 'admin_equipo_registrar_post',
            'uses' => 'EquipoController@registrarPost'
        ));


        // PAGINA CALENDARIO

        //acciones
        Route::post('admin/cita/accion', array(
            'as' => 'cita_actions_post',
            'uses' => 'CitaController@calendarActionPost'
        ));

        //nota
        Route::post('admin/cita/editar_nota', array(
            'as' => 'admin_cita_editar_nota_post',
            'uses' => 'NotaController@editarPost'
        ));

        //view change
        Route::post('admin/calendario/vista', array(
            'as' => 'set_calendar_view',
            'uses' => 'OpcionController@setCalendarView'
        ));

        //group view change
        Route::post('admin/calendario/grupos', array(
            'as' => 'set_calendar_groups',
            'uses' => 'OpcionController@setCalendarViewGroups'
        ));

        //group change by drag & drop
        Route::post('admin/calendario/grupo_doctor', array(
            'as' => 'set_doctor_group',
            'uses' => 'UserGrupoController@setGroup'
        ));

        //doctor filter change
        Route::post('admin/calendario/doctor', array(
            'as' => 'set_active_doctor',
            'uses' => 'UserController@setDoctor'
        ));

        //doctor filter change
        Route::get('admin/calendario/vista_impresion', array(
            'as' => 'get_print_view_calendar',
            'uses' => 'CitaController@getCitasPrint'
        ));


        // PAGINA HORARIO

        //editar
        Route::post('admin/horario/editar', array(
            'as' => 'admin_horario_editar_post',
            'uses' => 'HorarioController@editarPost'
        ));

        //acciones
        Route::post('admin/horario/accion', array(
            'as' => 'horario_actions_post',
            'uses' => 'HorarioController@calendarActionPost'
        ));

        //duplicar
        Route::post('admin/horario/duplicar', array(
            'as' => 'horario_duplicate_post',
            'uses' => 'HorarioController@calendarActionPost' //<-- change
        ));

        //delete week
        Route::post('admin/horario/eliminar', array(
            'as' => 'horario_delete',
            'uses' => 'HorarioController@deletePost'
        ));


        // PAGINA EQUIPOS

        //buscar
        Route::get('admin/log/buscar', array(
            'as' => 'admin_log_buscar_get',
            'uses' => 'LogController@buscarGetAlt'
        ));

        //info
        Route::get('admin/log/info', array(
            'as' => 'admin_log_info_get',
            'uses' => 'LogController@infoGet'
        ));


        // PAGINA OPCIONES

        //editar
        Route::post('admin/opciones/guardar', array(
            'as' => 'admin_options_post',
            'uses' => 'OpcionController@save'
        ));

    //});

    Route::get('cargar_pacientes_csv', array(
        'as' => 'cargar_pacientes_csv',
        'uses' => 'PersonaController@cargarPacientesCsv'
    ));

    Route::get('cerrar_sesion', array(
        'as' => 'cerrar_sesion',
        'uses' => 'UserController@cerrarSesion'
    ));

});