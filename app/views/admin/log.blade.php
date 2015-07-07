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
    $key = 'log';
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
                <li>{{ Lang::get('log.title_plural') }}</li>
            </ul>
            <!-- /BREADCRUMBS -->
            <!-- HEAD -->
            {{ $frm->header(Lang::get('log.title_plural'), $total, 'fa-file-text-o', false) }}
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
            {{-- $frm->search('search', 'search', Lang::get('global.insert_search')) --}}
            {{ $frm->date('search', null, Lang::get('log.date_from'), 'day') }}
            {{ $frm->date('search_date_to', null, Lang::get('log.date_to'), 'day') }}
            {{ $frm->remoteSelect('buscar_usuario_id', null, Lang::get('usuarios.title_single'), URL::route('admin_usuarios_list')) }}
            <div class="vertical-spaced">
            {{ $frm->submit('<i class="fa fa-search"></i>&nbsp;' . Lang::get('global.search')) }}
            </div>

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
        <form id="frm_data_view" class="form-horizontal" role="form" method="post" action="">
            <div class="content">

            </div>

            {{ Form::token() }}
        </form>
        <form id="frm_info_get" method="get" action="{{ URL::route('admin_' . $key . '_info_get') }}">
            {{ Form::token() }}
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
<script>
    var url_update_counter = "{{ URL::route('admin_' . $key . '_count_get') }}";

    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

        {{ $frm->script() }}

        @if (Session::has('id') && Session::get('id') > 0)
        Panel.search.collapse();
        Panel.view.load({{ (int)Session::get('id') }});
        Panel.view.show();
        @endif

    });
</script>
@stop