@extends('layouts.admin')

@section('titulo')
Panel de Administración
@stop

@section('cabecera')
{{ HTML::style('js/select2/select2.min.custom.css') }}
{{-- HTML::style('js/bootstrap-switch/bootstrap-switch.min.css') --}}
@stop

@section('contenido')
<?php
    $frm = new AForm;
    $key = 'especialidad';
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
            {{ $frm->header(Lang::get($key . '.title_plural'), $total, 'fa-medkit') }}
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
            <form id="frm_data_search" role="form" action="{{ URL::route('admin_' . $key . '_buscar_get') }}">
                {{ $frm->search('search', 'search', 'Ingrese su búsqueda') }}
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
                {{ $frm->text('nombre', null, Lang::get($key . '.name'), '', true) }}
                {{ $frm->text('descripcion', null, Lang::get($key . '.description')) }}
                <br><br><br>
                {{ Form::token() }}
                {{ $frm->submit() }}
            </form>
        {{ $frm->panelClose() }}


        <!-- EDIT -->
        {{ $frm->panelOpen('edit', Lang::get('global.modify'), 'fa-pencil', 'orange hidden', array('collapse','remove')) }}
            <form id="frm_data_edit" class="form-horizontal" role="form" action="{{ URL::route('admin_' . $key . '_editar_post') }}">
                {{ $frm->id() }}
                {{ $frm->text('nombre', null, Lang::get($key . '.name'), '', true) }}
                {{ $frm->text('descripcion', null, Lang::get($key . '.description')) }}

                {{ Form::token() }}
                {{ $frm->submit(Lang::get('global.save'), 'btn-warning') }}
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
{{-- HTML::script('js/bootstrap-switch/bootstrap-switch.min.js') --}}
{{ HTML::script('js/panel.js') }}
<script>
    var url_update_counter = "{{ URL::route('admin_' . $key . '_count_get') }}";

    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

        {{ $frm->script() }}
    });
</script>
@stop