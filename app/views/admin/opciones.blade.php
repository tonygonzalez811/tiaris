@extends('layouts.admin')

@section('titulo')
Panel de Administración
@stop

@section('cabecera')
{{ HTML::style('js/select2/select2.min.custom.css') }}
{{ HTML::style('js/pickadate/themes/default.css') }}
{{ HTML::style('js/pickadate/themes/default.date.css') }}
{{ HTML::style('js/pickadate/themes/default.time.css') }}
@stop

@section('contenido')
<?php
    $frm = new AForm;
    if (isset($field_values) && is_array($field_values)) {
        $frm->setValues( $field_values );
    }
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
                <li>{{ Lang::get('opcion.title_plural') }}</li>
            </ul>
            <!-- /BREADCRUMBS -->
            <!-- HEAD -->
            {{-- $frm->header('Calendario', null, 'fa-calendar') --}}
            <!-- /HEAD -->
        </div>
    </div>
</div>
<!-- /PAGE HEADER -->

<!-- MAIN CONTENT -->
<div class="row">
    <div class="col-sm-12">

        <!-- OPTIONS -->
        {{ $frm->panelOpen('config', Lang::get('global.settings'), 'fa-cog', '', array('collapse')) }}
        <form id="frm_data_options" class="form-horizontal" role="form" method="post" action="{{ URL::route('admin_options_post') }}">
            {{ $frm->time('start_time', null, Lang::get('opcion.start_time')) }}
            {{ $frm->time('end_time', null, Lang::get('opcion.end_time')) }}
            
            {{ $frm->multiselect('days_to_show[]', 'days_to_show', Lang::get('opcion.days_to_show'), $days, null, null, $field_values['days_to_show']) }}
            {{ $frm->time('min_time', null, Lang::get('opcion.min_time')) }}
            {{ $frm->time('max_time', null, Lang::get('opcion.max_time')) }}
            
            {{ Form::token() }}
            {{ $frm->submit() }}
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
{{ HTML::script('js/pickadate/picker.time.js') }}
{{ HTML::script('js/bootstrap-inputmask/bootstrap-inputmask.min.js') }}
<?php if (Config::get('app.locale') != 'en') : ?>
    {{ HTML::script('js/select2/select2_locale_' . Config::get('app.locale') . '.js') }}
    {{ HTML::script('js/pickadate/translations/' . Config::get('app.locale') . '.js') }}
<?php endif; ?>
{{ HTML::script('js/panel.js') }}
<script type="text/javascript">

    function saveSettings($frm, data) {
        if (data['ok'] == 1) {
            var $panel = $('#config_panel');
            Panel.status.saved( $panel );
        }
        else {
            Panel.status.error( $panel, data['err'] );
        }
    }
    
    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

        {{ $frm->script() }}

        $('#frm_data_options').submit(function(e) {
            var $panel = $('#config_panel');
            Panel.status.saving( $panel );
            submitForm($(this), saveSettings);

            e.preventDefault();
            return false;
        });
    });
</script>
@stop