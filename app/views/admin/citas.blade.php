@extends('layouts.admin')

@section('titulo')
Panel de Administración
@stop

@section('cabecera')
{{ HTML::style('js/select2/select2.min.custom.css') }}
{{-- HTML::style('js/bootstrap-datepicker/css/datepicker.css') --}}
{{ HTML::style('js/pickadate/themes/default.css') }}
{{ HTML::style('js/pickadate/themes/default.date.css') }}
{{ HTML::style('js/pickadate/themes/default.time.css') }}
{{ HTML::style('js/bootstrap-switch/bootstrap-switch.min.css') }}
{{ HTML::style('js/bootstrap-table/bootstrap-table.css') }}
@stop

@section('contenido')
<?php
    $frm = new AForm;

    $servicios_search = $servicios;
    $equipos_search = $equipos;

    $servicios_search[0] = '&nbsp;';
    $equipos_search[0] = '&nbsp;';
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
                <li>Citas</li>
            </ul>
            <!-- /BREADCRUMBS -->
            <!-- HEAD -->
            {{ $frm->header('Citas', $total, 'fa-calendar-o', false) }}
            <!-- /HEAD -->
        </div>
    </div>
</div>
<!-- /PAGE HEADER -->

<!-- MAIN CONTENT -->
<div class="row">
    <div class="col-sm-12">

        <!-- SEARCH -->
        {{ $frm->panelOpen('search', 'Buscar', 'fa-search', '', array('collapse')) }}
        <form id="frm_data_search" class="form-horizontal" role="form" action="{{ URL::route('admin_citas_buscar_get') }}">
            {{-- $frm->search('search', 'search', 'Ingrese su búsqueda') --}}
            {{-- $frm->date('search', null, Lang::get('citas.date'), 'day') --}}
            {{ $frm->date('from', null, Lang::get('citas.from'), 'day') }}
            {{ $frm->date('to', null, Lang::get('citas.to'), 'day') }}
            {{ $frm->remoteSelect('buscar_doctor_id', null, Lang::get('citas.doctor'), URL::route('admin_doctores_list')) }}
            {{ $frm->remoteSelect('buscar_paciente_id', null, Lang::get('citas.patient'), URL::route('admin_pacientes_list')) }}
            {{ $frm->select('buscar_servicio_id', null, Lang::get('servicio.title_single'), $servicios_search) }}
            {{ $frm->select('buscar_equipo_id', null, Lang::get('equipo.title_single'), $equipos_search) }}
            {{ $frm->select('buscar_estado_id', null, Lang::get('citas.state'), array_merge(array('any' => '&nbsp;'), $estados)) }}
            <div class="vertical-spaced">
            {{ $frm->submit('<i class="fa fa-search"></i>&nbsp;' . Lang::get('global.search')) }}
            </div>

            {{ $frm->hidden('search_query', null, 'search-query') }}
            {{ $frm->hidden('search_page', null, 'search-page') }}
            {{ Form::token() }}
        </form>
        <br>
        <form id="frm_data_results" role="form" action="{{ URL::route('admin_citas_info_get') }}">
            <a href="#box_report_config" id="btn_generate_report" role="button" class="btn btn-large btn-primary pull-right hidden" data-toggle="modal">
                <i class="fa fa-file-excel-o"></i>&nbsp;
                {{ Lang::get('citas.generate_report') }}
            </a>
            <div class="search-results-holder">

            </div>
            {{ Form::token() }}
        </form>
        {{ $frm->panelClose() }}

    </div>
</div>

<!-- REPORT MODAL FORM-->
<div class="modal fade" id="box_report_config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Generar Reporte</h4>
            </div>
            <div class="modal-body">
                <form id="frm_data_report" class="form-horizontal" role="form" method="post" target="_blank" action="{{ URL::route('admin_citas_buscar_post') }}">
                    {{ $frm->hidden('export', null, '', '1') }}

                    {{ $frm->checkbox('show_notes', null, 'Mostrar Notas') }}
                    {{ $frm->checkbox('export_csv', null, 'Exportar como CSV') }}

                    {{ $frm->hidden('from', 'report_from') }}
                    {{ $frm->hidden('to', 'report_to') }}
                    {{ $frm->hidden('buscar_doctor_id', 'report_doctor_id') }}
                    {{ $frm->hidden('buscar_paciente_id', 'report_paciente_id') }}
                    {{ $frm->hidden('buscar_servicio_id', 'report_servicio_id') }}
                    {{ $frm->hidden('buscar_equipo_id', 'report_equipo_id') }}
                    {{ $frm->hidden('buscar_estado_id', 'report_estado_id') }}

                    {{ $frm->hidden('search_query', null, 'search-query') }}
                    {{ $frm->hidden('search_page', null, 'search-page') }}
                    {{ Form::token() }}
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ Lang::get('global.cancel') }}</button>
                <button type="button" class="btn btn-primary modal-btn-ok">{{ Lang::get('global.ok') }}</button>
            </div>
        </div>
    </div>
</div>
<!-- /REPORT MODAL FORM-->

<!-- /MAIN CONTENT -->
@stop

@section('scripts')
{{ HTML::script('js/select2/select2.js') }}
{{-- HTML::script('js/bootstrap-datepicker/js/bootstrap-datepicker.js') --}}
{{ HTML::script('js/pickadate/picker.js') }}
{{ HTML::script('js/pickadate/picker.date.js') }}
{{ HTML::script('js/pickadate/picker.time.js') }}
{{ HTML::script('js/bootstrap-inputmask/bootstrap-inputmask.min.js') }}
{{ HTML::script('js/bootstrap-table/bootstrap-table-all.js') }}
<?php if (Config::get('app.locale') != 'en') : ?>
    {{ HTML::script('js/select2/select2_locale_' . Config::get('app.locale') . '.js') }}
    {{ HTML::script('js/pickadate/translations/' . Config::get('app.locale') . '.js') }}
<?php endif; ?>
{{ HTML::script('js/bootstrap-switch/bootstrap-switch.min.js') }}
{{ HTML::script('js/jquery-knob/js/jquery.knob.js') }}
{{ HTML::script('js/panel.js') }}
<script>
    var url_update_counter = "{{ URL::route('admin_citas_count_get') }}";

    function afterSearchingRecords(data) {
        //if (data['total'] > 0) {}
        $('#btn_generate_report').removeClass('hidden');
        var $frm_search = $('#frm_data_search');
        var $frm_report = $('#frm_data_report');
        $.each($frm_search.find('input[type=hidden], input[type=text], select'), function(i,o) {
            var $o = $(o);
            var $z = $frm_report.find('input[name=' + $(o).attr('name') + ']');
            if ($z.length) {
                $z.val( $o.val() );
            }
        });
    }

    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

        {{ $frm->script() }}

        $('#box_report_config').find('button.modal-btn-ok').click(function() {
            var $modal = $(this).closest('.modal');
            var $form = $modal.find('form').eq(0);
            $form.submit();//submitForm( $form );
            //$modal.modal('hide');
        });

    });
</script>
@stop