@extends('layouts.admin')

@section('titulo')
Estadísticas
@stop

@section('cabecera')
{{ HTML::style('js/select2/select2.min.custom.css') }}
{{-- HTML::style('js/bootstrap-datepicker/css/datepicker.css') --}}
{{ HTML::style('js/pickadate/themes/default.css') }}
{{ HTML::style('js/pickadate/themes/default.date.css') }}
{{ HTML::style('js/pickadate/themes/default.time.css') }}
{{ HTML::style('js/bootstrap-switch/bootstrap-switch.min.css') }}
<style type="text/css">
    #result_holder_graph {
        width: 500px;
        height: 250px;
        margin: auto;
        left: 0;
        right: 0;
    }

    #frm_data_results {
        display: none !important;
        visibility: hidden;
    }
</style>
@stop

@section('contenido')
<?php
    $frm = new AForm;

    $servicios_search = $servicios;
    $consultorios_search = $consultorios;

    $servicios_search[0] = '&nbsp;';
    $consultorios_search[0] = '&nbsp;';
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
                <li>Estadísticas</li>
            </ul>
            <!-- /BREADCRUMBS -->
            <!-- HEAD -->
            {{-- $frm->header('Citas', $total, 'fa-calendar-o') --}}
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
        <form id="frm_data_search" class="form-horizontal" role="form" action="{{ URL::route('admin_report_citas_buscar_get') }}">
            {{ $frm->date('from', null, Lang::get('citas.from'), 'day') }}
            {{ $frm->date('to', null, Lang::get('citas.to'), 'day') }}
            {{ $frm->select('type', null, Lang::get('citas.type'), array('state' => 'Estados', 'service' => 'Tratamientos', 'office' => 'Cabinas', 'equipment' => 'Equipos')) }}
            <div class="vertical-spaced">
            {{ $frm->submit('<i class="fa fa-search"></i>&nbsp;' . Lang::get('global.search')) }}
            </div>
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
        <div id="result_holder_graph"></div>
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
                <form id="frm_data_report" class="form-horizontal" role="form" method="post" target="_blank" action="{{ URL::route('admin_report_citas_buscar_post') }}">
                    {{ $frm->hidden('export', null, '', '1') }}

                    {{ $frm->checkbox('show_notes', null, 'Mostrar Notas') }}
                    {{ $frm->checkbox('export_csv', null, 'Exportar como CSV') }}

                    {{ $frm->hidden('from', 'report_from') }}
                    {{ $frm->hidden('to', 'report_to') }}
                    {{ $frm->hidden('type', 'report_type') }}
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
{{ HTML::script('js/pickadate/picker.js') }}
{{ HTML::script('js/pickadate/picker.date.js') }}
{{ HTML::script('js/pickadate/picker.time.js') }}
{{ HTML::script('js/bootstrap-inputmask/bootstrap-inputmask.min.js') }}
<?php if (Config::get('app.locale') != 'en') : ?>
    {{ HTML::script('js/select2/select2_locale_' . Config::get('app.locale') . '.js') }}
    {{ HTML::script('js/pickadate/translations/' . Config::get('app.locale') . '.js') }}
<?php endif; ?>
{{ HTML::script('js/bootstrap-switch/bootstrap-switch.min.js') }}
{{-- HTML::script('js/jquery-knob/js/jquery.knob.js') --}}
{{ HTML::script('js/flot/jquery.flot.min.js') }}
{{ HTML::script('js/flot/jquery.flot.pie.min.js') }}
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

    /*function dropDown_selectType($a) {
        var type = $a.attr('menu-action');
        console.log('type', type);
        $('#selected_type').val( type );

        $a.closest('.dropdown').find('.caption').html( $a.html() );
    }*/

    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

        {{ $frm->script() }}

        $('#box_report_config').find('button.modal-btn-ok').click(function() {
            var $modal = $(this).closest('.modal');
            var $form = $modal.find('form').eq(0);
            $form.submit();//submitForm( $form );
            //$modal.modal('hide');
        });


        function chart6() {
            var data1 = [
                [5, 0], [10, 10], [20, 20], [30, 30], [40, 40], [50, 50], [60, 60]
            ];
            var options = {
                series:{
                    bars:{
                        show: true
                    }
                },
                bars:{
                    horizontal:true,
                    barWidth:6
                },
                grid:{
                    borderWidth: 0
                },
                    colors: ["#F38630"]
            };
            $.plot($("#chart_6"), [data1], options);
        }

    });
</script>
@stop