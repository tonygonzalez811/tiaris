@extends('layouts.admin')

@section('titulo')
Panel de Administración
@stop

@section('cabecera')
{{ HTML::style('js/select2/select2.min.custom.css') }}
{{ HTML::style('js/bootstrap-switch/bootstrap-switch.min.css') }}

<style type="text/css">
	body.droppable .profile-avatar-wrap {
	  border: 5px dashed lightblue;
	  z-index: 9999;
	}
	.profile-avatar-wrap {
	  width: 256px/*33.33%*/;
	  float: left;
	  margin: 0 20px 5px 0;
	  position: relative;
	  pointer-events: none;
	  border: 5px solid transparent;
	}
	.profile-avatar-wrap:after {
	  /* Drag Prevention */
	  content: "";
	  position: absolute;
	  top: 0;
	  left: 0;
	  width: 100%;
	  height: 100%;
	}
	.profile-avatar-wrap img {
	  width: 100%;
	  display: block;
	}
</style>
@stop

@section('contenido')
<?php
    $frm = new AForm;
    $key = 'equipo';
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
            {{ $frm->header(Lang::get($key . '.title_plural'), $total, 'fa-plug') }}
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
        <form id="frm_data_new" class="form-horizontal with-file" enctype="multipart/form-data" role="form" method="post" action="{{ URL::route('admin_' . $key . '_registrar_post') }}">
            <div class="row">
                <div class="col-md-8">
                    {{ $frm->text('nombre', null, Lang::get('equipo.name'), '', true) }}
                    {{ $frm->text('descripcion', null, Lang::get('equipo.description')) }}
                    {{ $frm->text('modelo', null, Lang::get('equipo.model')) }}
                    {{ $frm->text('serial', null, Lang::get('equipo.serial')) }}
                    {{ $frm->text('cod_dicom', null, Lang::get('equipo.cod_dicom')) }}
                    {{ $frm->text('host', null, Lang::get('equipo.host')) }}
                    {{ $frm->select('modalidad_id', null, Lang::get('modalidad.title_single'), $modalidades) }}
                </div>
                <div class="col-md-4 text-center">
                    <div class="profile-avatar-wrap">
                        @if (!empty($field_values['avatar']))
                        <img src="{{ URL::asset('img/equipments/s/' . $field_values['avatar']) }}" id="profile-avatar" alt="">
                        @else
                        <img src="{{ URL::asset('img/equipments/s/default.jpg') }}" id="profile-avatar" alt="">
                        @endif
                    </div>
                    <div class="clearfix">
                        <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
                        <input type="file" id="avatar" name="avatar">
                    </div>
                </div>
            </div>
            <br>
            {{ Form::token() }}
            {{ $frm->submit() }}
        </form>
        {{ $frm->panelClose() }}


        <!-- EDIT -->
        {{ $frm->panelOpen('edit', Lang::get('global.modify'), 'fa-pencil', 'orange hidden', array('collapse','remove')) }}
        <form id="frm_data_edit" class="form-horizontal with-file" enctype="multipart/form-data" role="form" action="{{ URL::route('admin_' . $key . '_editar_post') }}">
            <div class="row">
                <div class="col-md-8">
                    {{ $frm->id() }}
                    {{ $frm->text('nombre', null, Lang::get('equipo.name'), '', true) }}
                    {{ $frm->text('descripcion', null, Lang::get('equipo.description')) }}
                    {{ $frm->text('modelo', null, Lang::get('equipo.model')) }}
                    {{ $frm->text('serial', null, Lang::get('equipo.serial')) }}
                    {{ $frm->text('cod_dicom', null, Lang::get('equipo.cod_dicom')) }}
                    {{ $frm->text('host', null, Lang::get('equipo.host')) }}
                    {{ $frm->select('modalidad_id', null, Lang::get('modalidad.title_single'), $modalidades) }}
                </div>
                <div class="col-md-4 text-center">
                    <div class="profile-avatar-wrap">
                        <img src="{{ URL::asset('img/equipments/s/default.jpg') }}" id="profile-avatar_edit" alt="">
                    </div>
                    <div class="clearfix">
                        <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
                        <input type="file" id="avatar_edit" name="avatar">
                    </div>
                </div>
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
{{ HTML::script('js/bootstrap-switch/bootstrap-switch.min.js') }}
{{ HTML::script('js/avatar/resample.js') }}
{{ HTML::script('js/avatar/avatar.js') }}
{{ HTML::script('js/panel.js') }}
<script type="text/javascript">
    function showImage(url) {
        $('#profile-avatar_edit').attr('src', url);
    }

    function afterPanelEditLoaded(data) {
        showImage(data['avatar_url']);
    }

    var url_update_counter = "{{ URL::route('admin_' . $key . '_count_get') }}";

    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

        {{ $frm->script() }}

    });
</script>
@stop