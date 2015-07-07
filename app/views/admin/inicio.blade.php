@extends('layouts.admin')

@section('titulo')
    Panel de Administración
@stop

@section('contenido')
<?php $frm = new AForm; ?>
<!-- PAGE HEADER-->
<div class="row">
    <div class="col-sm-12">
        <div class="page-header">
            <div class="clearfix">
                <h3 class="content-title pull-left">Inicio</h3>
            </div>
            <div class="description">{{ Lang::get('global.general_inf') }}</div>
        </div>
    </div>
</div>
<!-- /PAGE HEADER -->

<!-- DASHBOARD CONTENT -->
<div class="row">
    <!-- COLUMN 1 -->
    <div class="col-md-5">
        <div class="row">
          <div class="col-lg-6">
             {{ $frm->infoCountBox('fa-user', $total_patients, Lang::get('pacientes.title_plural'), User::canAdminPersonas() ? URL::route('admin_pacientes') : 'javascript:;') }}
          </div>
          <div class="col-lg-6">
             {{ $frm->infoCountBox('fa-calendar-o', $total_citas_today, Functions::singlePlural(Lang::get('citas.for_today_single'), Lang::get('citas.for_today_plural'), $total_citas_today), URL::route('admin_calendario')) }}
          </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="quick-pie panel panel-default">
                    <div class="panel-body">
                        <div class="col-md-6 text-center">
                            {{ $frm->pieChart(Lang::get('citas.done'), $total_citas_done, $total_citas, '#9EB37A') }}
                        </div>
                        <div class="col-md-6 text-center">
                            {{ $frm->pieChart(Lang::get('citas.cancelled'), $total_citas_cancelled, $total_citas, '#CA5452') }}
                        </div>
                    </div>
                </div>
            </div>
       </div>
       <!--div class="row">
            <div class="col-md-12">
                <a href="{{ URL::route('admin_estadisticas') }}">Ver más estadísticas</a>
            </div>
       </div-->
    </div>
    <!-- /COLUMN 1 -->
    
    <!-- COLUMN 2 -->
    <div class="col-md-7">
        {{ $frm->lineChart(Lang::get('pacientes.per_month'), 'fa-users', $chart_data_patient_month, 'mes', 'total', Lang::get('pacientes.title_plural')) }}
    </div>
    <!-- /COLUMN 2 -->
</div>
<!-- /DASHBOARD CONTENT -->
@stop

@section('scripts')
{{ HTML::script('js/jquery-easing/jquery.easing.min.js') }}
{{ HTML::script('js/easypiechart/jquery.easypiechart.min.js') }}
{{ HTML::script('js/flot/jquery.flot.min.js') }}
<script type="text/javascript">
    jQuery(document).ready(function() {
        //App.setPage("index");  //Set current page
        App.init(); //Initialise plugins and elements

        {{ $frm->script() }}
    });
</script>
@stop