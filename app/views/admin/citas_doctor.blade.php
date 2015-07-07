@extends('layouts.admin')

@section('titulo')
    Panel de Administración
@stop

@section('cabecera')
    <style type="text/css">
        #modal_cita_info .modal-dialog {
            width: 900px;
        }

        #modal_cita_info div.btn-group {
            display: none;
        }
    </style>
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
                <li>
                    <a href="{{ URL::route('inicio_doctor', array('doctor_id' => $doctor_id)) }}">{{ Lang::get('global.general_inf') }}</a>
                </li>
                <li>
                    {{ Lang::get('citas.for_today_plural') }}
                </li>
            </ul>
            <!-- /BREADCRUMBS -->
            <div class="row">
                <div class="col-md-2">
                    <figure class="avatar">
                        @if (false)
                            @if (!empty($doctor->avatar))
                            <img src="{{ URL::asset('img/avatars/s/' . $doctor->avatar) }}" alt="">
                            @else
                            <img src="{{ URL::asset('img/avatars/s/default.jpg') }}" alt="">
                            @endif
                        @endif
                    </figure>
                </div>
                <div class="col-md-10">
                    <div class="clearfix">
                        <h3 class="content-title pull-left">{{ $doctor ? Functions::firstNameLastName($doctor->nombre, $doctor->apellido) : Lang::get('global.not_found') }}</h3>
                    </div>
                    <div class="description">{{ Lang::get('citas.for_today_plural') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /PAGE HEADER -->

<div class="row">
    <div id="citas" class="col-md-12">
        {{ $citas }}
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-12">
        <div class="btn-group btn-group-lg" role="group">
            <a class="btn btn-default" href="{{ URL::route('doctor_citas_impresion', array('doctor_id' => $doctor_id)) }}" target="_blank">
                <i class="fa fa-print"></i>&nbsp;
                {{ Lang::get('usuarios.view_citas_print') }}
            </a>
        </div>
    </div>
</div>

{{ $frm->modalOpen('modal_cita_info', Lang::get('citas.cita_details')) }}
    <form class="form-horizontal">
        <div id="cita_details"></div>
    </form>
{{ $frm->modalClose(null, null, false) }}
<form id="frm_get_info" action="{{ URL::route('admin_citas_info_get') }}" method="get">
    <input type="hidden" name="id" value="0">
</form>
@stop

@section('scripts')
{{ HTML::script('js/jquery-knob/js/jquery.knob.js') }}
{{ HTML::script('js/panel.js') }}
<script type="text/javascript">

    function loadCitaData(data) {
        $('#cita_details').html( data['results'] );
        if (typeof data['script'] == 'string' && data['script'].length > 0) {
            eval(data['script']);
        }
    }

    jQuery(document).ready(function() {
        App.init(); //Initialise plugins and elements

        {{ $frm->script() }}

        $('#citas').find('a').click(function() {
            var $a = $(this);
            var $frm = $('#frm_get_info');
            $frm.find('input[name=id]').val( $a.attr('data-id') );
            submitForm($frm, function($frm, data) {
                if (data['ok'] == 1) {
                    var $modal = $('#modal_cita_info');
                    loadCitaData(data);
                    $modal.modal('show');
                }
            })
        });
    });
</script>
@stop