@extends('layouts.print')

@section('titulo')
    Citas de {{ $doctor_name }} para el {{ $date }}
@stop

@section('cabecera')
    <style type="text/css">

    </style>
@stop

@section('contenido')
<div class="container">
    <div class="row">
        <div class="col-md-12 text-center">
            <h3>{{ $doctor_name }} - {{ $date }}</h3>
        </div>
    </div>
    <div class="row">
        <div id="citas" class="col-md-12">
            {{ $citas }}
        </div>
    </div>
</div>
@stop

@section('scripts')
<script type="text/javascript">

    jQuery(document).ready(function() {
        setTimeout(function() {
            window.print();
        }, 500);
    });
</script>
@stop