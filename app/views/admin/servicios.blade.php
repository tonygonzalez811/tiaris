@extends('layouts.admin')

@section('titulo')
Panel de Administración
@stop

@section('cabecera')
{{ HTML::style('js/select2/select2.min.custom.css') }}
{{ HTML::style('js/bootstrap-slider/bootstrap-slider.css') }}
{{ HTML::style('js/bootstrap-switch/bootstrap-switch.min.css') }}
<style type="text/css">
    .horario-dia-lbl {
        display: inline-block;
        width: 70px;
        /*text-align: right;*/
    }

    .horario-dia-lbl.margin-top:not(:first-child) {
        margin-top: 4px;
    }
</style>
@stop

@section('contenido')
<?php
    $frm = new AForm;
    $key = 'servicio';

    $slider_create_script = '';
    $slider_edit_script = '';
?>
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
                <li>{{ Lang::get($key . '.title_plural') }}</li>
            </ul>
            <!-- /BREADCRUMBS -->
            <!-- HEAD -->
            {{ $frm->header(Lang::get($key . '.title_plural'), $total, 'fa-check-square-o') }}
            <!-- /HEAD -->
        </div>
    </div>
</div>
<!-- /PAGE HEADER -->

<!-- MAIN CONTENT -->
<div class="row">
    <div class="col-sm-12">

        <!-- SEARCH -->
        {{ $frm->panelOpen('search', Lang::get('global.search'), 'fa-search', '', array('refresh','collapse')) }}
        <form id="frm_data_search" class="form-horizontal" role="form" action="{{ URL::route('admin_' . $key . '_buscar_get') }}">
            {{ $frm->search('search', 'search', Lang::get('global.insert_search')) }}

            {{ $frm->hidden('search_query', null, 'search-query') }}
            {{ $frm->hidden('search_page', null, 'search-page') }}
            {{ Form::token() }}
        </form>
        <br>
        <form id="frm_data_results" role="form" action="{{ URL::route('admin_' . $key . '_info_get') }}">
            <div class="search-results-holder">

            </div>
            {{ Form::token() }}
        </form>
        {{ $frm->panelClose() }}


        <!-- VIEW -->
        {{ $frm->panelOpen('view', Lang::get('global.inf'), 'fa-info-circle', 'blue hidden', array('refresh','collapse','remove')) }}
        <form id="frm_data_view" class="form-horizontal" role="form" method="post" action="{{ URL::route('admin_' . $key . '_accion_post') }}">
            <div class="content">

            </div>

            {{ Form::token() }}
        </form>
        <form id="frm_info_get" method="get" action="{{ URL::route('admin_' . $key . '_info_get') }}">
            {{ Form::token() }}
        </form>
        {{ $frm->panelClose() }}


        <!-- CREATE NEW -->
        {{ $frm->panelOpen('create', Lang::get('global.new'), 'fa-plus', 'primary hidden', array('collapse','remove')) }}
        <form id="frm_data_new" class="form-horizontal" role="form" method="post" action="{{ URL::route('admin_' . $key . '_registrar_post') }}">
            {{ $frm->text('nombre', null, Lang::get('servicio.name'), '', true) }}
            {{ $frm->text('descripcion', null, Lang::get('servicio.description')) }}
            {{ $frm->slider('duracion', null, Lang::get('servicio.duration'), $duraciones, $slider_create_script, 5) }}

            <!-- horario -->
            {{ $frm->checkbox('validar_horario', null, Lang::get('servicio.validate_time'), 'with-fields') }}
            <div id="validar_horario_fields" class="fields well">
                <fieldset>
                    <legend>{{ Lang::get('servicio.timetable') }}</legend>
                </fieldset>
              <div class="alert alert-info" role="alert">{{ Lang::get('servicio.change_timetable_after') }}</div>
            </div>

            <!-- equipos -->
            {{ $frm->checkbox('validar_equipo', null, Lang::get('servicio.validate_equipment'), 'with-fields') }}
            <div id="validar_equipo_fields" class="fields well">
                <fieldset>
                    <legend>{{ Lang::get('equipo.title_plural') }}</legend>
                </fieldset>
                {{ $frm->select('modalidad_id', null, Lang::get('modalidad.title_single'), $modalidades) }}
                <div class="equipos-holder-loading form-group hidden">
                    <label class="col-md-2 control-label">{{ Lang::get('equipo.title_plural') }}</label>
                    <div class="col-md-10">
                        <label class="control-label"><i>{{ Lang::get('global.loading') }}</i></label>
                    </div>
                </div>
                <div class="equipos-holder">
                    {{ $frm->multiselect('equipos[]', 'equipos', Lang::get('equipo.title_plural'), $equipos) }}
                </div>
            </div>

            <!-- doctor -->
            {{ $frm->checkbox('validar_doctor', null, Lang::get('servicio.validate_doctor'), 'with-fields') }}
            <div id="validar_doctor_fields" class="fields well">
                <fieldset>
                    <legend>{{ Lang::get('usuarios.doctors') }}</legend>
                </fieldset>
                {{-- $frm->remoteSelect('doctores[]', 'doctores', Lang::get('usuarios.doctors'), URL::route('admin_doctores_list'), true) --}}
                {{ $frm->multiselect('doctores[]', 'doctores', Lang::get('usuarios.doctors'), $doctores) }}
            </div>

            <!-- tecnico -->
            {{ $frm->checkbox('validar_tecnico', null, Lang::get('servicio.validate_technician'), 'with-fields') }}
            <div id="validar_tecnico_fields" class="fields well">
                <fieldset>
                    <legend>{{ Lang::get('usuarios.technicians') }}</legend>
                </fieldset>
                {{-- $frm->remoteSelect('tecnicos[]', 'tecnicos', Lang::get('usuarios.technicians'), URL::route('admin_tecnicos_list'), true) --}}
                {{ $frm->multiselect('tecnicos[]', 'tecnicos', Lang::get('usuarios.technicians'), $tecnicos) }}
            </div>

            <!-- consultorio -->
            {{ $frm->checkbox('validar_consultorio', null, Lang::get('servicio.validate_office'), 'with-fields') }}
            <div id="validar_consultorio_fields" class="fields well">
                <fieldset>
                    <legend>{{ Lang::get('consultorio.title_plural') }}</legend>
                </fieldset>
                {{-- $frm->remoteSelect('consultorios[]', 'consultorios', Lang::get('consultorio.title_plural'), URL::route('admin_consultorios_list'), true) --}}
                {{ $frm->multiSelect('consultorios[]', 'consultorios', Lang::get('consultorio.title_plural'), $consultorios) }}
            </div>

            <br>
            {{ Form::token() }}
            {{ $frm->submit() }}
        </form>
        {{ $frm->panelClose() }}


        <!-- EDIT -->
        {{ $frm->panelOpen('edit', Lang::get('global.modify'), 'fa-pencil', 'orange hidden', array('collapse','remove')) }}
        <form id="frm_data_edit" class="form-horizontal" role="form" action="{{ URL::route('admin_' . $key . '_editar_post') }}">
            {{ $frm->id() }}
            {{ $frm->text('nombre', null, Lang::get('servicio.name'), '', true) }}
            {{ $frm->text('descripcion', null, Lang::get('servicio.description')) }}
            {{ $frm->slider('duracion', null, Lang::get('servicio.duration'), $duraciones, $slider_edit_script, 5) }}

            <!-- horario -->
            {{ $frm->checkbox('validar_horario', null, Lang::get('servicio.validate_time'), 'with-fields') }}
            <div id="validar_horario_edit_fields" class="fields well">
                <fieldset>
                    <legend>{{ Lang::get('servicio.timetable') }}</legend>
                </fieldset>
                <div class="alert alert-info" role="alert">{{ Lang::get('servicio.change_timetable_after') }}</div>
            </div>

            <!-- equipo -->
            {{ $frm->checkbox('validar_equipo', null, Lang::get('servicio.validate_equipment'), 'with-fields') }}
            <div id="validar_equipo_edit_fields" class="fields well">
                <fieldset>
                    <legend>{{ Lang::get('equipo.title_plural') }}</legend>
                </fieldset>
                {{ $frm->select('modalidad_id', null, Lang::get('modalidad.title_single'), $modalidades) }}
                <div class="equipos-holder-loading form-group">
                    <label class="col-md-2 control-label">{{ Lang::get('equipo.title_plural') }}</label>
                    <div class="col-md-10">
                        <label class="control-label"><i>{{ Lang::get('global.loading') }}</i></label>
                    </div>
                </div>
                <div class="equipos-holder">
                    {{ $frm->hidden('equipos_seleccionados') }}
                    {{ $frm->multiselect('equipos[]', 'equipos', Lang::get('equipo.title_plural'), $equipos) }}
                </div>
            </div>

            <!-- doctor -->
            {{ $frm->checkbox('validar_doctor', null, Lang::get('servicio.validate_doctor'), 'with-fields') }}
            <div id="validar_doctor_edit_fields" class="fields well">
                <fieldset>
                    <legend>{{ Lang::get('usuarios.doctors') }}</legend>
                </fieldset>
                {{-- $frm->remoteSelect('doctores[]', 'doctores', Lang::get('usuarios.doctors'), URL::route('admin_doctores_list'), true) --}}
                {{ $frm->multiselect('doctores[]', 'doctores', Lang::get('usuarios.doctors'), $doctores) }}
            </div>

            <!-- tecnico -->
            {{ $frm->checkbox('validar_tecnico', null, Lang::get('servicio.validate_technician'), 'with-fields') }}
            <div id="validar_tecnico_edit_fields" class="fields well">
                <fieldset>
                    <legend>{{ Lang::get('usuarios.technicians') }}</legend>
                </fieldset>
                {{-- $frm->remoteSelect('tecnicos[]', 'tecnicos', Lang::get('usuarios.technicians'), URL::route('admin_tecnicos_list'), true) --}}
                {{ $frm->multiselect('tecnicos[]', 'tecnicos', Lang::get('usuarios.technicians'), $tecnicos) }}
            </div>

            <!-- consultorio -->
            {{ $frm->checkbox('validar_consultorio', null, Lang::get('servicio.validate_office'), 'with-fields') }}
            <div id="validar_consultorio_edit_fields" class="fields well">
                <fieldset>
                    <legend>{{ Lang::get('consultorio.title_plural') }}</legend>
                </fieldset>
                {{-- $frm->remoteSelect('consultorios[]', 'consultorios', Lang::get('consultorio.title_plural'), URL::route('admin_consultorios_list'), true) --}}
                {{ $frm->multiselect('consultorios[]', 'consultorios', Lang::get('consultorio.title_plural'), $consultorios) }}
            </div>

            {{ Form::token() }}
            {{ $frm->submit(null, 'btn-warning') }}
        </form>
        <form id="frm_data_get" method="get" action="{{ URL::route('admin_' . $key . '_datos_get') }}">
            {{ Form::token() }}
        </form>
        {{ $frm->panelClose() }}

    </div>
</div>

<!-- /MAIN CONTENT -->
@stop

@section('scripts')
{{ HTML::script('js/select2/select2.js') }}
<?php if (Config::get('app.locale') != 'en') : ?>
    {{ HTML::script('js/select2/select2_locale_' . Config::get('app.locale') . '.js') }}
<?php endif; ?>
{{ HTML::script('js/bootstrap-slider/bootstrap-slider.js') }}
{{ HTML::script('js/bootstrap-switch/bootstrap-switch.min.js') }}
{{ HTML::script('js/panel.js') }}
<script>
    function beforePanelCreate() {
        setTimeout(function() {
            {{ $slider_create_script }}
        }, 100);

        checkCheckboxesState( $('#frm_data_new') );
    }

    function beforePanelEdit() {
        setTimeout(function() {
            {{ $slider_edit_script }}
        }, 100);
    }

    function afterPanelEditLoaded(data) {
        var $target = $('#equipos_edit');
        loadEquipmentsForMode(data['modalidad_id'], $target, equipmentListLoaded, false);

        checkCheckboxesState( $('#frm_data_edit') );
    }

    function checkCheckboxesState($frm) {
        //checks the states of the checkboxes
        var $chks = $frm.find('input[type=checkbox]');
        var $c;
        var state;
        $.each($chks, function(i,c) {
            $c = $(c);
            state = $c.is(':checked');
            $c.bootstrapSwitch('state', state);
            if (state) {
                $('#' + $c.attr('id') + '_fields').show();
            }
            else {
                $('#' + $c.attr('id') + '_fields').hide();
            }
        });
    }

    function equipmentListLoaded($target) {
        var ids = $target.closest('form').find('input[name=equipos_seleccionados]');
        if (ids.length) {
            ids = ids.val().split(',');
            $target.select2('val', ids);
        }
        loadingEquipments($target, false);
    }

    function loadEquipmentsForMode(mode_id, $target, fn_after_loading, show_after_loading) {
        loadingEquipments($target, true);
        $.ajax({
            type: 'GET',
            url: '{{ URL::route('get_equipments_by_mode') }}',
            dataType: 'json',
            data: { 'modalidad_id': mode_id }
        }).done(function(data) {
            console.log(data);
            if (data['ok']) {
                //var items = eval(data['data']);
                $target.select2('destroy');
                $target.html( data['data'] );
                $target.select2();
                if (typeof show_after_loading == 'undefined' || show_after_loading) {
                    loadingEquipments($target, false);
                }
                if (typeof fn_after_loading == 'function') {
                    fn_after_loading($target);
                }
            }
        }).fail(function(data) {
            console.log(data); //failed
        });
    }

    function loadingEquipments($target, state) {
        $target = $target.parent();
        if (state) {
            $target.addClass('hidden');
            $target.parent().siblings('.equipos-holder-loading').removeClass('hidden');
        }
        else {
            $target.parent().siblings('.equipos-holder-loading').addClass('hidden');
            $target.removeClass('hidden');
        }
    }

    var url_update_counter = "{{ URL::route('admin_' . $key . '_count_get') }}";

    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

        {{ $frm->script() }}


        $('.fields').hide();

        $('select[name=modalidad_id]').change(function() {
            var $mode = $(this);
            var $target = $mode.closest('form').find('select[name="equipos[]"]');
            var mode_id = $mode.val();
            loadEquipmentsForMode(mode_id, $target);
        });

        $('input[type=checkbox].with-fields').on('switchChange.bootstrapSwitch', function(e, state) {
            var $chk = $(this);
            var $fields = $chk.closest('form').find( '#' + $chk.attr('id') + '_fields' );
            if (state) {
                $fields.slideDown('fast');
            }
            else {
                $fields.slideUp('slow');
            }
        });

        $('#create_panel').find('.box-title').click(function() {
            checkCheckboxesState( $('#frm_data_new') );
        });

    });
</script>
@stop