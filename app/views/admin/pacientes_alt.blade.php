@extends('layouts.admin')

@section('titulo')
Panel de Administración
@stop

@section('cabecera')
{{ HTML::style('js/select2/select2.min.custom.css') }}
{{ HTML::style('js/pickadate/themes/default.css') }}
{{ HTML::style('js/pickadate/themes/default.date.css') }}
{{ HTML::style('js/bootstrap-switch/bootstrap-switch.min.css') }}
{{ HTML::style('js/bootstrap-table/bootstrap-table.css') }}
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
                <li>{{ Lang::get('pacientes.title_plural') }}</li>
            </ul>
            <!-- /BREADCRUMBS -->
            <!-- HEAD -->
            {{ $frm->header(Lang::get('pacientes.title_plural'), $total, 'fa-users', false) }}
            <!-- /HEAD -->
        </div>
    </div>
</div>
<!-- /PAGE HEADER -->

<!-- MAIN CONTENT -->
<div class="row">
    <div class="col-sm-12">

        <!-- SEARCH -->
        {{ $frm->panelOpen('search', 'Buscar', 'fa-search', '', array('refresh','collapse')) }}
        <form id="frm_data_search" class="form-horizontal" role="form" action="{{ URL::route('admin_pacientes_buscar_alt_get') }}">
            {{-- $frm->search('search', 'search', 'Ingrese su búsqueda') --}}
            <?php $frm->setIncludeEmptyOption(true) ?>
            {{ $frm->hidden('search', 'search') }}
            {{ $frm->number('search_min_age', null, Lang::get('reportes.min_age')) }}
            {{ $frm->number('search_max_age', null, Lang::get('reportes.max_age')) }}
            {{ $frm->date('search_birthdate', null, Lang::get('reportes.birthdate')) }}
            {{ $frm->select('search_gender', null, Lang::get('pacientes.gender'), $genders) }}
            {{ $frm->multiselect('search_marital_status[]', 'search_marital_status', Lang::get('pacientes.marital_status'), $marital_statuses) }}
            {{ $frm->checkbox('search_with_email', null, Lang::get('reportes.with_email')) }}
            <div class="vertical-spaced">
                {{ $frm->submit('<i class="fa fa-search"></i>&nbsp;' . Lang::get('global.search')) }}
            </div>

            {{ $frm->hidden('search_query', null, 'search-query') }}
            {{ $frm->hidden('search_page', null, 'search-page') }}
            {{ Form::token() }}
            <?php $frm->setIncludeEmptyOption(false) ?>
        </form>
        <br>
        <form id="frm_data_results" role="form" action="{{ URL::route('admin_pacientes_info_get') }}">
            <div class="search-results-holder">
                
            </div>
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
<?php if (Config::get('app.locale') != 'en') : ?>
{{ HTML::script('js/select2/select2_locale_' . Config::get('app.locale') . '.js') }}
{{ HTML::script('js/pickadate/translations/' . Config::get('app.locale') . '.js') }}
<?php endif; ?>
{{ HTML::script('js/bootstrap-switch/bootstrap-switch.min.js') }}
{{ HTML::script('js/bootstrap-table/bootstrap-table-all.js') }}
{{ HTML::script('js/bootstrap-inputmask/bootstrap-inputmask.min.js') }}
{{ HTML::script('js/panel.js') }}
<script>
    var url_update_counter = "{{ URL::route('admin_pacientes_count_get') }}";

    function afterUpdatingRecords() {

    }

    function beforePanelCreate() {

    }

    function copyToClipboard(text) {
        window.prompt("Copiar: Ctrl+C", text);
    }

    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

        {{ $frm->script() }}

    });
</script>
@stop