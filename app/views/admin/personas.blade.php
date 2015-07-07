@extends('layouts.admin')

@section('titulo')
Panel de Administración
@stop

@section('cabecera')
{{ HTML::style('js/select2/select2.min.custom.css') }}
{{ HTML::style('js/pickadate/themes/default.css') }}
{{ HTML::style('js/pickadate/themes/default.date.css') }}
{{ HTML::style('js/bootstrap-switch/bootstrap-switch.min.css') }}
@stop

@section('contenido')
<?php $frm = new AForm; ?>
<!-- PAGE HEADER-->
<div class="row">
    <div class="col-sm-12">
        <div class="page-header">
            <!-- BREADCRUMBS -->
            <ul class="breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="{{ URL::route('admin_inicio') }}">{{ Lang::get('global.home') }}</a>
                </li>
                <li>{{ $title }}</li>
            </ul>
            <!-- /BREADCRUMBS -->
            <!-- HEAD -->
            {{ $frm->header($title, $total, 'fa-users', User::canAdminPersonas()) }}
            <!-- /HEAD -->
        </div>
    </div>
</div>
<!-- /PAGE HEADER -->

<!-- MAIN CONTENT -->
<div class="row">
    <div class="col-sm-12">

        <!-- SEARCH -->
        {{ $frm->panelOpen('search', 'Buscar', 'fa-search', '', array('refresh','collapse')) }}
        <form id="frm_data_search" role="form" action="{{ URL::route('admin_personas_buscar_get', array('tipo' => $tipo)) }}">
            {{ $frm->search('search', 'search', 'Ingrese su búsqueda') }}
            {{ $frm->hidden('search_query', null, 'search-query') }}
            {{ $frm->hidden('search_page', null, 'search-page') }}
            {{ Form::token() }}
        </form>
        <br>
        <form id="frm_data_results" role="form" action="{{ URL::route('admin_pacientes_info_get') }}">
            <div class="search-results-holder">
                <!--div class="list-group search-results">

                </div-->
            </div>
            {{ Form::token() }}
        </form>
        {{ $frm->panelClose() }}


        <!-- VIEW -->
        {{ $frm->panelOpen('view', 'Información', 'fa-info-circle', 'blue hidden', array('refresh','collapse','remove')) }}
        <form id="frm_data_view" class="form-horizontal" role="form" method="post" action="{{ URL::route('admin_pacientes_accion_post') }}">
            <div class="content">

            </div>
            {{ Form::token() }}
        </form>
        <form id="frm_info_get" method="get" action="{{ URL::route('admin_pacientes_info_get') }}">
            {{ Form::token() }}
        </form>
        {{ $frm->panelClose() }}


        <!-- CREATE NEW -->
        {{ $frm->panelOpen('create', 'Nuevo', 'fa-plus', 'primary hidden', array('collapse','remove')) }}
        <form id="frm_data_new" class="form-horizontal" role="form" method="post" action="{{ URL::route('admin_pacientes_registrar_post') }}">
            {{ $frm->text('nombre', null, Lang::get('pacientes.name'), "", true) }}
            {{ $frm->text('apellido', null, Lang::get('pacientes.lastname'), "", true) }}
            {{-- $frm->text('dni', null, Lang::get('pacientes.dni'), "", true, array('[vejVEJ]{1}-{1}[0-9]{7,9}', 'Ej. V-123456789')); --}}
            {{ $frm->dni('dni', null, Lang::get('pacientes.dni'), "", false); }}
            {{ $frm->date('fecha_nacimiento', null, Lang::get('pacientes.birthdate')) }}
            {{ $frm->select('sexo', null, Lang::get('pacientes.gender'), $genders) }}
            {{ $frm->select('estado_civil', null, Lang::get('pacientes.marital_status'), $marital_statuses) }}
            {{ $frm->text('direccion', null, Lang::get('pacientes.address')) }}
            {{ $frm->tagSelect('telefonos', null, Lang::get('pacientes.phone')) }}
            {{ $frm->tagSelect('correos', null, Lang::get('pacientes.email')) }}

            @if ($tipo == User::ROL_DOCTOR)
            <div class="doctor-holder">
                {{ $frm->select('especialidad', null, Lang::get('usuarios.specialty'), $especialidades) }}
                {{ $frm->text('numero', null, Lang::get('usuarios.dr_num')) }}
            </div>
            @endif

            @if ($tipo == User::ROL_TECHNICIAN)
            <div class="doctor-holder">
                {{ $frm->text('cod_dicom', null, Lang::get('usuarios.cod_dicom')) }}
            </div>
            @endif

            @if ($tipo == User::ROL_DOCTOR || $tipo == User::ROL_TECHNICIAN)
            <div class="usuario-holder">
                <fieldset>
                    <legend>{{ Lang::get('usuarios.title_single') }}</legend>
                </fieldset>
                {{ $frm->hidden('tipo', null, 'static-value', $tipo) }}
                {{ $frm->username('user_nombre', null, Lang::get('usuarios.username'), '', true) }}
                {{ $frm->password('password', null, Lang::get('usuarios.password')) }}
                {{ $frm->password('password2', null, Lang::get('usuarios.password_again')) }}
                {{ $frm->checkbox('admin', null, Lang::get('usuarios.admin')) }}
                {{ $frm->hidden('activo', null, 'static-value', '1') }}
                {{-- $frm->multiselect('roles[]', 'user_roles', Lang::get('usuarios.roles'), $roles) --}}
                {{ $frm->hidden('roles[]', 'user_roles', 'static-value') }}
            </div>
            @endif
            <br><br><br>
            {{ Form::token() }}
            {{ $frm->submit('Guardar') }}
        </form>
        {{ $frm->panelClose() }}


        <!-- EDIT -->
        {{ $frm->panelOpen('edit', 'Modificar', 'fa-pencil', 'orange hidden', array('collapse','remove')) }}
        <form id="frm_data_edit" class="form-horizontal" role="form" action="{{ URL::route('admin_pacientes_editar_post') }}">
            {{ $frm->id() }}
            <?php if (!User::canEditDeletePersonas()) $frm->setDisabled() ?>
            {{ $frm->text('nombre', null, Lang::get('pacientes.name'), "", true) }}
            {{ $frm->text('apellido', null, Lang::get('pacientes.lastname'), "", true) }}
            {{-- $frm->text('dni', null, Lang::get('pacientes.dni'), "", true, array('[vejVEJ]{1}-{1}[0-9]{7,9}', 'Ej. V-123456789')); --}}
            {{ $frm->dni('dni', null, Lang::get('pacientes.dni'), "", false); }}
            <?php $frm->setDisabled(false) ?>

            {{ $frm->date('fecha_nacimiento', null, Lang::get('pacientes.birthdate')) }}
            {{ $frm->select('sexo', null, Lang::get('pacientes.gender'), $genders) }}
            {{ $frm->select('estado_civil', null, Lang::get('pacientes.marital_status'), $marital_statuses) }}
            {{ $frm->text('direccion', null, Lang::get('pacientes.address')) }}
            {{ $frm->tagSelect('telefonos', null, Lang::get('pacientes.phone')) }}
            {{ $frm->tagSelect('correos', null, Lang::get('pacientes.email')) }}

            {{ Form::token() }}
            {{ $frm->submit('Guardar', 'btn-warning') }}
        </form>
        <form id="frm_data_get" method="get" action="{{ URL::route('admin_pacientes_datos_get') }}">
            {{ Form::token() }}
        </form>
        {{ $frm->panelClose() }}

    </div>
</div>
<!-- /MAIN CONTENT -->
@stop

@section('scripts')
{{ HTML::script('js/select2/select2.js') }}
{{ HTML::script('js/pickadate/picker.js') }}
{{ HTML::script('js/pickadate/picker.date.js') }}
<?php if (Config::get('app.locale') != 'en') : ?>
{{ HTML::script('js/select2/select2_locale_' . Config::get('app.locale') . '.js') }}
{{ HTML::script('js/pickadate/translations/' . Config::get('app.locale') . '.js') }}
<?php endif; ?>
{{ HTML::script('js/bootstrap-inputmask/bootstrap-inputmask.min.js') }}
{{ HTML::script('js/bootstrap-switch/bootstrap-switch.min.js') }}
{{ HTML::script('js/panel.js') }}
<script>
    var url_update_counter = "{{ URL::route('admin_personas_count_get', array('tipo' => $tipo)) }}";

    function updateUserName() {
        var nombre = $('#nombre').val();
        var apellido = $('#apellido').val();
        $('#user_nombre').val( (apellido.length == 0 ? nombre.split(' ')[0].toLowerCase() : nombre.charAt(0).toLowerCase()) + apellido.split(' ')[0].toLowerCase() );
    }

    function afterUpdatingRecords() {

    }

    function beforePanelCreate() {
        //$('#user_roles').select2('val', '{{-- $tipo --}}');
        $('#user_roles').val('{{ $tipo }}');
    }

    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

        {{ $frm->script() }}

        $('#nombre').change(function() {
            updateUserName();
        });
        $('#apellido').change(function() {
            updateUserName();
        });

    });
</script>
@stop