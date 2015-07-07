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
            <!-- BREADCRUMBS -->
            <ul class="breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="{{ URL::route('admin_inicio') }}">{{ Lang::get('global.home') }}</a>
                </li>
                <li>{{ Lang::get('global.general_inf') }}</li>
            </ul>
            <!-- /BREADCRUMBS -->
            <div class="row">
                <div class="col-md-2">
                    <figure class="avatar">
                        <img src="{{ $avatar }}" alt="">
                    </figure>
                </div>
                <div class="col-md-10">
                    <div class="clearfix">
                        <h3 class="content-title pull-left">{{ $equipo->nombre }}</h3>
                    </div>
                    <div class="description">{{ Lang::get('global.general_inf') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /PAGE HEADER -->

<!-- DASHBOARD CONTENT -->
<div class="row">
    <!-- COLUMN 1 -->
    <div class="col-md-6">
        <div class="list-group">
            @foreach ($servicios as $servicio)
            <a href="{{ URL::route('horario_servicio', array('equipo_servicio_id' => $equipo->id . '-' . $servicio->id)) }}" class="list-group-item">
                <h4>{{ $servicio->nombre }}</h4>

                @if (!$servicio->especial)
                <span class="pull-right badge">{{ Lang::get('servicio.special') }}</span>
                @endif

                {{ Functions::minToHours($servicio->duracion) }}
            </a>
            @endforeach
        </div>
        {{-- $frm->lineChart(Lang::get('pacientes.per_month'), 'fa-users', $chart_data_patient_month, 'mes', 'total', Lang::get('pacientes.title_plural')) --}}
    </div>
    <!-- /COLUMN 1 -->

    <!-- COLUMN 2 -->
    <div class="col-md-6">
        <div class="row">
          <div class="col-lg-6 col-md-6">
             {{ $frm->infoCountBox('fa-check', $total_citas, Lang::get('citas.done_citas'), 'javascript:;') }}
          </div>
          <div class="col-lg-6 col-md-6">
             {{ $frm->infoCountBox('fa-calendar-o', $total_citas_today, Functions::singlePlural(Lang::get('citas.for_today_single'), Lang::get('citas.for_today_plural'), $total_citas_today), URL::route('admin_calendario')) }}
          </div>
        </div>
        <!--div class="row">
            <div class="col-md-12">
                <div class="quick-pie panel panel-default">
                    <div class="panel-body">
                        <div class="col-md-6 col-sm-6 text-center">
                            {{-- $frm->pieChart(Lang::get('citas.done'), $total_citas_done, $total_citas, '#9EB37A') --}}
                        </div>
                        <div class="col-md-6 col-sm-6 text-center">
                            {{-- $frm->pieChart(Lang::get('citas.cancelled'), $total_citas_cancelled, $total_citas, '#CA5452') --}}
                        </div>
                    </div>
                </div>
            </div>
       </div-->
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
        App.init(); //Initialise plugins and elements

        {{ $frm->script() }}
    });
</script>
@stop