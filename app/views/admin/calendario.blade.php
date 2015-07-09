@extends('layouts.admin')

@section('titulo')
Calendario
@stop

@section('cabecera')
{{ HTML::style('js/select2/select2.min.custom.css') }}
{{-- HTML::style('js/bootstrap-datepicker/css/datepicker.css') --}}
{{ HTML::style('js/pickadate/themes/default.css') }}
{{ HTML::style('js/pickadate/themes/default.date.css') }}
{{ HTML::style('js/pickadate/themes/default.time.css') }}
{{ HTML::style('js/fullcalendar/fullcalendar.min.css') }}
{{ HTML::style('js/gritter/css/jquery.gritter.css') }}
<style type="text/css">
    body {
        overflow: hidden;
    }
    #content {
        background: #fff url({{ URL::asset('img/bg/squairy_light.png') }}) repeat;
        /*background: #fff url({{ URL::asset('img/bg/gray_jean.png') }}) repeat;*/
    }

    .fc-view-container {
        background-color: #fff;
    }

    #filter_holder {
        overflow-y: auto;
    }
    /*#filter_holder::-webkit-scrollbar { / * no firefox :( * /
        display: none;
    }*/

    .filter-accordion .panel {
        background: none !important;
        border: 0 none !important;
        box-shadow: none !important;
    }

    .filter-accordion h4 a {
        text-decoration: none;
    }

    .filter-accordion .list-group-item:first-child {
        border-radius: 0;
    }

    .bring-to-front {
        z-index: 1000 !important;
    }

    .ampm-separation {
        border-top-style: double !important;
        border-top-width: 4px !important;
    }

    a.fc-event.availability {
        opacity: .3;
        border-radius: 0;
        border: 0;
        z-index: -200 !important;
        pointer-events: none !important;
        width: 100% !important;
    }

    a.fc-event.availability .fc-time {
        display: none;
    }

    @if (!Auth::user()->admin)
    /*a.fc-event.state3 {
        pointer-events: none !important;
    }*/
    @endif

    .fc-slats tr:not(.fc-minor) td {
        border-top-width: 2px;
        /*border-top-color: #aaa;*/
    }

    .fc-slats tr.fc-half td {
        border-top-width: 1px;
        border-top-style: solid;
    }

    .grouped-event {
        display: inline-block;
        width: 100%;
    }

    .grouped-event:hover {
        background-color: rgba(0,0,0,.1);
        /*display: inline-block;
        width: 100%;*/
    }

    .list-group-item-large {
        height: 60px;
        padding-left: 0;
        padding-right: 0;
    }

    .cita-info {
        font-size: 15px;
    }

    .fc-center {
        position: relative;
    }

    #total_citas {
        position: absolute;
        margin: auto;
        left: 0;
        right: 0;
        top: 30px;
        color: #aaa;
        font-weight: lighter;
    }

    #loading_overlay {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 50%;
        text-align: center;
        margin-top: 25%;
        z-index: 999;
    }

    .grouped-event .divider {
        border-top: 1px solid #0fa8ad;
        width: 25%;
    }

    .btn-toolbar-small {
        margin-top: 10px;
    }

    h4.text-muted:not(.error) {
        color: #999 !important;
    }
    h4.text-muted.error {
        color: #f00 !important;
    }

    .list-group.main .list-group-item {
        padding: 4px;
    }
</style>
@stop

@section('contenido')
<?php
    $user = Auth::user();
    $frm = new AForm;
?>
<?php if (false) : ?>
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
                <li>Calendario</li>
            </ul>
            <!-- /BREADCRUMBS -->
            <!-- HEAD -->
            {{-- $frm->header('Calendario', null, 'fa-calendar') --}}
            <!-- /HEAD -->
        </div>
    </div>
</div>
<!-- /PAGE HEADER -->
<?php else : ?>
<br>
<?php endif; ?>

<!-- MAIN CONTENT -->
<div class="row">
    <div class="col-sm-12">

        <!-- CALENDAR -->
        <div class="row">
            <div class="col-md-2">
                @if (User::canViewAllCitas())
                <div class="input-group">
                     <input type="text" value="" class="form-control" placeholder="{{ Lang::get('citas.search_by_dni') }}" id="search_event_query" />
                     <span class="input-group-btn">
                        <a href="#" id="search_event_btn" class="btn btn-default"><i class="fa fa-search"></i></a>
                     </span>
                </div>

                <div class="divide-20"></div>
                @endif

                <div id="filter_holder">
                    <!-- modalidades -->
                    {{ $frm->accordionOpen('filter_mode_accordion') }}
                        {{ $frm->accordionItemOpen(Lang::get('modalidad.title_single')) }}
                            <div class="list-group">
                                @foreach ($modalidades as $id => $nombre)
                                    <a href="#" class="list-group-item group-filter filter-mode" attr-id="{{ $id }}">
                                        {{ $nombre }}
                                        <span class="badge hidden">0</span>
                                    </a>
                                @endforeach
                            </div>
                        {{ $frm->accordionItemClose() }}
                    {{ $frm->accordionClose() }}

                    <!-- estados -->
                    {{ $frm->accordionOpen('filter_state_accordion') }}
                        {{ $frm->accordionItemOpen(Lang::get('citas.state')) }}
                            <div class="list-group">
                                @foreach ($estados as $id => $nombre)
                                    <a href="#" class="list-group-item group-filter filter-state" attr-id="{{ $id }}">
                                        {{ $nombre }}
                                        <span class="badge hidden">0</span>
                                    </a>
                                @endforeach
                            </div>
                        {{ $frm->accordionItemClose() }}
                    {{ $frm->accordionClose() }}

                    <!-- equipos -->
                    {{ $frm->accordionOpen('filter_equipment_accordion') }}
                        {{ $frm->accordionItemOpen(Lang::get('equipo.title_single')) }}
                            <div class="list-group">
                                @foreach ($equipos as $id => $nombre)
                                    <a href="#" class="list-group-item group-filter filter-equipment" attr-id="{{ $id }}">
                                        {{ $nombre }}
                                        <span class="badge hidden">0</span>
                                    </a>
                                @endforeach
                            </div>
                        {{ $frm->accordionItemClose() }}
                    {{ $frm->accordionClose() }}

                    <!-- doctores -->
                    {{ $frm->accordionOpen('filter_doctor_accordion') }}
                        {{ $frm->accordionItemOpen(Lang::get('citas.doctor')) }}
                            <div class="list-group">
                                @foreach ($doctores as $id => $nombre)
                                    <a href="#" class="list-group-item group-filter filter-doctor" attr-id="{{ $id }}">
                                        {{ $nombre }}
                                        <span class="badge hidden">0</span>
                                    </a>
                                @endforeach
                            </div>
                        {{ $frm->accordionItemClose() }}
                    {{ $frm->accordionClose() }}

                    <!-- tecnicos -->
                    {{ $frm->accordionOpen('filter_technician_accordion') }}
                        {{ $frm->accordionItemOpen(Lang::get('citas.technician')) }}
                            <div class="list-group">
                                @foreach ($tecnicos as $id => $nombre)
                                    <a href="#" class="list-group-item group-filter filter-technician" attr-id="{{ $id }}">
                                        {{ $nombre }}
                                        <span class="badge hidden">0</span>
                                    </a>
                                @endforeach
                            </div>
                        {{ $frm->accordionItemClose() }}
                    {{ $frm->accordionClose() }}
                </div>
            </div>

            <div class="col-md-10 calendar-holder">
                <div class='full-calendar' id="main_calendar"></div>
                <div id="loading_overlay">
                    <!-- loading animation -->
                    <?php include( public_path() . '/img/loader.html' ) ?>
                    <!-- /loading animation -->
                </div>
            </div>
        </div>
        {{-- $frm->panelClose() --}}

    </div>
</div>

<!-- NEW EVENT FORM -->
{{ $frm->modalOpen('new_event_form', Lang::get('citas.new_event')) }}
   <form id="frm_data_new_event" class="form-horizontal" role="form" method="post" autocomplete="off" action="{{ URL::route('admin_citas_registrar_post') }}">
        <input type="hidden" name="id" id="cita_id" value="0">
        <div class="list-group main">
            <!-- date & time -->
            <a href="#" class="list-group-item" data-toggle="modal" data-target="#new_event_date_time_modal">
                <div class="row form-item datetime">
                    <div class="col-sm-2 col-xs-2">
                        <div class="status-icon" id="icon_time">
                            <i class="fa fa-4x fa-clock-o"></i>
                        </div>
                    </div>
                    <div class="col-sm-10 col-xs-10">
                        <h4 class="list-group-item-heading text-muted" id="cita_date_time">...</h4>
                        <p class="list-group-item-text" id="cita_date_time_remaining"></p>
                        {{ $frm->hidden('fecha', 'fecha_hidden') }}
                        {{ $frm->hidden('inicio', 'inicio_hidden') }}
                        {{ $frm->hidden('fin', 'fin_hidden') }}
                    </div>
                </div>
            </a>

            <!-- doctor -->
            <a href="#" class="list-group-item" data-toggle="modal" data-target="#new_event_doctor_modal">
                <div class="row form-item">
                    <div class="col-sm-2 col-xs-2">
                        <div class="status-icon" id="icon_doctor">
                            <i class="fa fa-4x fa-user-md"></i>
                        </div>
                    </div>
                    <div class="col-sm-10 col-xs-10">
                        <div class="row">
                            <div class="col-sm-10">
                                <h4 class="list-group-item-heading text-muted" id="cita_doctor_name" data-select_lbl="{{ Lang::get('citas.select_doctor') }}">{{ Lang::get('citas.select_doctor') }}</h4>
                                <p class="list-group-item-text" id="cita_doctor_inf"></p>
                                {{ $frm->hidden('doctor_id', 'doctor_id_hidden') }}
                            </div>
                            <div class="col-sm-2 hidden-xs">
                                <img class="avatar-thumb" id="cita_doctor_avatar" src="{{ URL::asset('img/avatars/s/default.jpg') }}" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- technician -->
            <a href="#" class="list-group-item" data-toggle="modal" data-target="#new_event_technician_modal">
                <div class="row form-item">
                    <div class="col-sm-2 col-xs-2">
                        <div class="status-icon" id="icon_technician">
                            <i class="fa fa-4x fa-user"></i>
                        </div>
                    </div>
                    <div class="col-sm-10 col-xs-10">
                        <div class="row">
                            <div class="col-sm-10">
                                <h4 class="list-group-item-heading text-muted" id="cita_technician_name" data-select_lbl="{{ Lang::get('citas.select_technician') }}">{{ Lang::get('citas.select_technician') }}</h4>
                                <p class="list-group-item-text" id="cita_technician_inf"></p>
                                {{ $frm->hidden('tecnico_id', 'tecnico_id_hidden') }}
                            </div>
                            <div class="col-sm-2 hidden-xs">
                                <img class="avatar-thumb" id="cita_technician_avatar" src="{{ URL::asset('img/avatars/s/default.jpg') }}" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- patient -->
            <a href="#" id="open_patients_modal" class="list-group-item" data-toggle="modal" data-target="#new_event_patient_modal">
                <div class="row form-item patient">
                    <div class="col-sm-2 col-xs-2">
                        <div class="status-icon" id="icon_patient">
                            <i class="fa fa-4x fa-male"></i>
                        </div>
                    </div>
                    <div class="col-sm-10 col-xs-10">
                        <h4 class="list-group-item-heading text-muted" id="cita_patient_name" data-select_lbl="{{ Lang::get('citas.select_patient') }}">{{ Lang::get('citas.select_patient') }}</h4>
                        <p class="list-group-item-text" id="cita_patient_inf"></p>
                        {{ $frm->hidden('persona_id', 'persona_id_hidden') }}
                    </div>
                </div>
            </a>

            <!-- services -->
            <a href="#" class="list-group-item" data-toggle="modal" data-target="#new_event_service_modal">
                <div class="row form-item">
                    <div class="col-sm-2 col-xs-2">
                        <div class="status-icon" id="icon_service">
                            <i class="fa fa-4x fa-heartbeat"></i>
                        </div>
                    </div>
                    <div class="col-sm-10 col-xs-10">
                        <h4 class="list-group-item-heading text-muted" id="cita_service_name" data-select_lbl="{{ Lang::get('citas.select_service') }}">{{ Lang::get('citas.select_service') }}</h4>
                        <p class="list-group-item-text" id="cita_service_inf"></p>
                        {{ $frm->hidden('servicio_id', 'servicio_id_hidden') }}
                    </div>
                </div>
            </a>

            <!-- equipment -->
            <a href="#" id="open_equipments_modal" class="list-group-item" data-toggle="modal" data-target="#new_event_equipment_modal">
                <div class="row form-item">
                    <div class="col-sm-2 col-xs-2">
                        <div class="status-icon" id="icon_equipment">
                            <i class="fa fa-4x fa-plug"></i>
                        </div>
                    </div>
                    <div class="col-sm-10 col-xs-10">
                        <h4 class="list-group-item-heading text-muted" id="cita_equipment_name" data-select_lbl="{{ Lang::get('citas.select_equipment') }}">{{ Lang::get('citas.select_equipment') }}</h4>
                        <p class="list-group-item-text" id="cita_equipment_inf"></p>
                        {{ $frm->hidden('equipo_id', 'equipo_id_hidden') }}
                    </div>
                </div>
            </a>

            <!-- office -->
            <a href="#" id="open_offices_modal" class="list-group-item" data-toggle="modal" data-target="#new_event_office_modal">
                <div class="row form-item">
                    <div class="col-sm-2 col-xs-2">
                        <div class="status-icon" id="icon_office">
                            <!--i class="fa fa-4x fa-cube"></i-->
                            <figure class="icon door"></figure>
                        </div>
                    </div>
                    <div class="col-sm-10 col-xs-10">
                        <h4 class="list-group-item-heading text-muted" id="cita_office_name" data-select_lbl="{{ Lang::get('citas.select_office') }}">{{ Lang::get('citas.select_office') }}</h4>
                        <p class="list-group-item-text" id="cita_office_inf"></p>
                        {{ $frm->hidden('consultorio_id', 'consultorio_id_hidden') }}
                    </div>
                </div>
            </a>
        </div>
        <input type="hidden" name="warning_key" id="warning_key">
        <input type="hidden" name="ignore_warning" id="ignore_warning_submit" value="0">
        <input type="hidden" name="ignore_warning_all" id="ignore_warning_all_submit" value="0">

        {{ Form::token() }}
    </form>
<?php
    $custom_footer = <<<EOT
    <div class="alert alert-danger alert-dismissible modal-alert hidden" role="alert">
        <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <i class="fa fa-exclamation-circle"></i>&nbsp; 
        <span class="sr-only">Error:</span>
        <span class="msg"></span>
EOT;
    if ($user->admin) {
        $lbl_ignore = Lang::get('citas.ignore_warning');
        $lbl_ignore_all = Lang::get('citas.ignore_all_warnings');
        $custom_footer .= <<<EOT
        <div class="hidden" id="warning_ignore_options">
            <br>
            <br><input type="checkbox" id="ignore_warning" value="1"><label for="ignore_warning">&nbsp;{$lbl_ignore}</label>
            <br><input type="checkbox" id="ignore_warning_all" value="1"><label for="ignore_warning_all">&nbsp;{$lbl_ignore_all}</label>
        </div>
EOT;
    }
    $custom_footer .= <<<EOT
    </div>
EOT;
?>
{{ $frm->modalClose(null, null, true, $custom_footer) }}
<!-- /NEW EVENT FORM-->

    <!-- NEW DATE TIME FORM -->
    {{ $frm->modalOpen('new_event_date_time_modal', Lang::get('citas.set_date_time')) }}
        <form id="frm_new_event_date_time_inf" class="form-horizontal" role="form" method="get" autocomplete="off" action="{{ URL::route('cita_datetime_inf_get') }}">
            {{ $frm->date('fecha', null, Lang::get('citas.date'), 'day') }}
            {{ $frm->time('inicio', null, Lang::get('citas.time_start')) }}
            {{ $frm->time('fin', null, Lang::get('citas.time_end'), 'hidden') }}
        </form>
    {{ $frm->modalClose() }}
    <!-- /NEW DATE TIME FORM -->

    <!-- NEW DOCTOR FORM -->
    {{ $frm->modalOpen('new_event_doctor_modal', Lang::get('citas.set') . ' ' . Lang::get('usuarios.doctor')) }}
        <form id="frm_new_event_doctor_inf" class="form-horizontal" role="form" method="get" autocomplete="off" action="{{ URL::route('cita_doctor_inf_get') }}">
            {{ $frm->remoteSelect('doctor_id', null, Lang::get('citas.doctor'), URL::route('admin_doctores_list')) }}
        </form>
        <div id="doctors_by_letter_holder"></div>
    {{ $frm->modalClose() }}
    <!-- /NEW DOCTOR FORM -->

    <!-- NEW TECHNICIAN FORM -->
    {{ $frm->modalOpen('new_event_technician_modal', Lang::get('citas.set') . ' ' . Lang::get('usuarios.tecnico')) }}
        <form id="frm_new_event_technician_inf" class="form-horizontal" role="form" method="get" autocomplete="off" action="{{ URL::route('cita_technician_inf_get') }}">
            {{ $frm->remoteSelect('tecnico_id', null, Lang::get('citas.technician'), URL::route('admin_tecnicos_list')) }}
        </form>
        <div id="technicians_by_letter_holder"></div>
    {{ $frm->modalClose() }}
    <!-- /NEW TECHNICIAN FORM -->

    <!-- NEW PATIENT FORM -->
    {{ $frm->modalOpen('new_event_patient_modal', Lang::get('citas.set') . ' ' . Lang::get('pacientes.title_single')) }}
        <form id="frm_new_event_patient_inf" class="form-horizontal" role="form" method="get" autocomplete="off" action="{{ URL::route('cita_patient_inf_get') }}">
            {{ $frm->remoteSelect('persona_id', null, Lang::get('citas.patient'), URL::route('admin_pacientes_list')) }}
        </form>
        <br>
        <div id="new_patient">
            <h4 class="text-center">{{ Lang::get('citas.new_patient') }}</h4>
            <form id="frm_data_new_patient" class="form-horizontal" role="form" method="post" autocomplete="off" action="{{ URL::route('admin_pacientes_registrar_post') }}">
                {{ $frm->text('nombre', null, Lang::get('pacientes.name'), "", true) }}
                {{ $frm->text('apellido', null, Lang::get('pacientes.lastname'), "", true) }}
                {{ $frm->dni('dni', null, Lang::get('pacientes.dni'), "", true); }}
                {{ $frm->date('fecha_nacimiento', null, Lang::get('pacientes.birthdate')) }}
                {{ $frm->select('sexo', null, Lang::get('pacientes.gender'), $genders) }}
                {{ $frm->select('estado_civil', null, Lang::get('pacientes.marital_status'), $marital_statuses) }}
                {{ $frm->text('direccion', null, Lang::get('pacientes.address')) }}
                {{ $frm->tagSelect('telefonos', null, Lang::get('pacientes.phone')) }}
                {{ $frm->tagSelect('correos', null, Lang::get('pacientes.email')) }}
                {{ Form::token() }}
            </form>
        </div>
        <div id="patient_inf">
            <form id="frm_data_edit_patient" class="form-horizontal" role="form" method="post" autocomplete="off" action="{{ URL::route('admin_pacientes_editar_post') }}">
                {{ $frm->id() }}
                {{ $frm->hidden('changed', null, 'changed', '0') }}
                <?php if (!User::canEditDeletePersonas()) $frm->setDisabled() ?>
                {{ $frm->text('nombre', null, Lang::get('pacientes.name'), "", true) }}
                {{ $frm->text('apellido', null, Lang::get('pacientes.lastname'), "", true) }}
                {{ $frm->dni('dni', null, Lang::get('pacientes.dni'), "", true); }}
                <?php $frm->setDisabled(false) ?>
                {{ $frm->date('fecha_nacimiento', null, Lang::get('pacientes.birthdate')) }}
                {{ $frm->select('sexo', null, Lang::get('pacientes.gender'), $genders) }}
                {{ $frm->select('estado_civil', null, Lang::get('pacientes.marital_status'), $marital_statuses) }}
                {{ $frm->text('direccion', null, Lang::get('pacientes.address')) }}
                {{ $frm->tagSelect('telefonos', null, Lang::get('pacientes.phone')) }}
                {{ $frm->tagSelect('correos', null, Lang::get('pacientes.email')) }}
                {{ Form::token() }}
            </form>
        </div>
    {{ $frm->modalClose() }}
    <!-- /NEW PATIENT FORM -->

    <!-- NEW SERVICE FORM -->
    {{ $frm->modalOpen('new_event_service_modal', Lang::get('citas.set') . ' ' . Lang::get('servicio.title_single')) }}
        <form id="frm_new_event_service_inf" class="form-horizontal" role="form" method="get" autocomplete="off" action="{{ URL::route('cita_service_inf_get') }}">
            {{ $frm->remoteSelect('servicio_id', null, Lang::get('citas.service'), URL::route('admin_servicios_list')) }}
            <nav>
                <ul class="pagination pagination-sm">
                    @foreach ($modalidades as $mod_id => $modalidad)
                    <li>
                        <a class="service-category-index" attr-id="{{ $mod_id }}" href="#">{{ $modalidad }}</a>
                    </li>
                    @endforeach
                    <li>
                        <a class="service-category-index" attr-id="0" href="#"><i>{{ Lang::get('servicio.all_categories') }}</i></a>
                    </li>
                </ul>
            </nav>
        </form>
        <div id="service_by_category_holder"></div>
    {{ $frm->modalClose() }}
    <!-- /NEW SERVICE FORM -->

    <!-- NEW EQUIPMENT FORM -->
    {{ $frm->modalOpen('new_event_equipment_modal', Lang::get('citas.set') . ' ' . Lang::get('equipo.title_single')) }}
        <form id="frm_new_event_equipment_inf" class="form-horizontal" role="form" method="get" autocomplete="off" action="{{ URL::route('cita_equipment_inf_get') }}">
            @if (Auth::user()->admin)
            {{ $frm->remoteSelect('equipo_id', null, Lang::get('equipo.title_single'), URL::route('admin_equipos_list')) }}
            @endif
        </form>
        <div id="available_equipments_holder"></div>
    {{ $frm->modalClose(Auth::user()->admin ? null : false) }}
    <!-- /NEW EQUIPMENT FORM -->

    <!-- NEW OFFICE FORM -->
    {{ $frm->modalOpen('new_event_office_modal', Lang::get('citas.set') . ' ' . Lang::get('consultorio.title_single')) }}
        <form id="frm_new_event_office_inf" class="form-horizontal" role="form" method="get" autocomplete="off" action="{{ URL::route('cita_office_inf_get') }}">
            @if (Auth::user()->admin)
            {{ $frm->remoteSelect('consultorio_id', null, Lang::get('consultorio.title_single'), URL::route('admin_consultorios_list')) }}
            @endif
        </form>
        <div id="available_offices_holder"></div>
    {{ $frm->modalClose(Auth::user()->admin ? null : false) }}
    <!-- /NEW OFFICE FORM -->

<!-- ACTIONS FORM -->
<?php
    $doctor_lbl = Lang::get('citas.doctor');
    $technician_lbl = Lang::get('citas.technician');
    $header = <<<EOT
    <div class="cita-info text-muted">
        <div class="pull-left">
            <i class="fa fa-calendar-o"></i> <span class="fecha"></span>&nbsp;
            (<span class="hora"></span>)
        </div>
        <div class="pull-right">
            <span class="servicio"></span>
        </div>
        <div class="clearfix"></div>

        <div class="pull-left">
            &nbsp;&nbsp;&nbsp;&nbsp;
            {$doctor_lbl}: <span class="doctor"></span>
        </div>
        <div class="pull-right">
            <span class="paciente"></span>&nbsp;&nbsp;&nbsp;&nbsp;
        </div>
        <div class="clearfix"></div>

        <div class="pull-left">
            &nbsp;&nbsp;&nbsp;&nbsp;
            {$technician_lbl}: <span class="tecnico"></span>
        </div>
        <div class="pull-right">
            <span class="oficina"></span>&nbsp;&nbsp;&nbsp;&nbsp;
        </div>
        <div class="clearfix"></div>
    </div>
EOT;
    //unset($doctor_lbl);
?>
{{ $frm->modalOpen('actions_modal', /*Lang::get('citas.actions')*/$header) }}
    <div class="btn-toolbar" role="toolbar">
        <div id="states" class="btn-group btn-group-lg" role="group">
            <button id="state{{ Cita::UNCONFIRMED }}" type="button" class="btn btn-default" attr-state_id="{{ Cita::UNCONFIRMED }}" attr-type="warning">
                <i class="fa fa-4x fa-circle-o"></i>
                <span>{{ Lang::get('citas.unconfirmed') }}</span>
            </button>
            <button id="state{{ Cita::CONFIRMED }}" type="button" class="btn btn-default{{ !User::canConfirmOrCancelCita($user) ? ' disabled' : '' }}" attr-state_id="{{ Cita::CONFIRMED }}" attr-type="primary" attr-confirm_nexts="0">
                <i class="fa fa-4x fa-check-circle-o"></i>
                <span>{{ Lang::get('citas.confirmed') }}</span>
            </button>
            <button id="state{{ Cita::CANCELLED }}" type="button" class="btn btn-default{{ !User::canConfirmOrCancelCita($user) ? ' disabled' : '' }}" attr-state_id="{{ Cita::CANCELLED }}" attr-type="danger" attr-confirm_nexts="0">
                <i class="fa fa-4x fa-user-times"></i>
                <span>{{ Lang::get('citas.cancelled') }}</span>
            </button>
            <button id="state{{ Cita::DONE }}" type="button" class="btn btn-default{{ !User::canChangeCitaStateToDone($user) ? ' disabled' : '' }}" attr-state_id="{{ Cita::DONE }}" attr-type="success">
                <i class="fa fa-4x fa-check"></i>
                <span>{{ Lang::get('citas.done') }}</span>
            </button>
        </div>
        <div class="btn-group btn-group-lg" role="group">
            <button id="add_note" type="button" class="btn btn-default">
                <i class="fa fa-4x fa-comment"></i>
                <span>{{ Lang::get('citas.add_note') }}</span>
            </button>
            @if (User::canAddCitas($user))
            <button id="edit_cita" type="button" class="btn btn-default">
                <i class="fa fa-4x fa-pencil"></i>
                <span>{{ Lang::get('citas.edit') }}</span>
            </button>
            @endif
            @if (User::canDeleteCitas($user))
            <button id="delete_cita" type="button" class="btn btn-default">
                <i class="fa fa-4x fa-trash"></i>
                <span>{{ Lang::get('global.delete') }}</span>
            </button>
            @endif
        </div>
    </div>
    @if (Auth::user()->admin)
    <div class="pull-right btn-toolbar-small" role="toolbar">
        <div class="btn-group btn-group-xs" role="group">
            <a class="btn btn-info view_history" href="#">{{ Lang::get('citas.view_history') }}</a>&nbsp;&nbsp;
        </div>
    </div>
    <div class="clearfix"></div>
    @endif
    <form id="frm_action" class="hidden" role="form" method="post" autocomplete="off" action="{{ URL::route('cita_actions_post') }}">
        {{ $frm->hidden('cita_id', 'cita_id_action') }}
        {{ $frm->hidden('action', 'cita_action') }}
        {{ $frm->hidden('val', 'action_val') }}
        {{ $frm->hidden('grouped_nexts') }}
        {{ $frm->hidden('grouped_nexts_apply') }}
        {{ Form::token() }}
    </form>
    @if (User::canDeleteCitas($user))
    <form id="frm_action_delete" class="hidden" role="form" method="post" autocomplete="off" action="{{ URL::route('admin_citas_accion_post') }}">
        {{ $frm->hidden('id', 'cita_id_delete') }}
        {{ $frm->hidden('action', 'delete_action', '', 'action_delete') }}
        {{ Form::token() }}
    </form>
    @endif
{{ $frm->modalClose(null, null, false) }}
<!-- /ACTIONS FORM -->

@if (Auth::user()->admin)
{{ $frm->modalOpen('history_modal', Lang::get('log.title_plural')) }}
    <div class="list-group" id="history_list"></div>
{{ $frm->modalClose(null, null, false) }}
@endif

<!-- PATIENT DNI MODAL -->
{{ $frm->modalOpen('patient_dni_modal', Lang::get('citas.patient_has_no_dni')) }}
<p>{{ Lang::get('citas.to_continue_insert_patient_dni') }}</p>
 <form id="frm_patient_dni" class="form-horizontal" role="form" method="post" autocomplete="off" action="{{ URL::route('admin_paciente_editar_dni_post') }}">
    {{ $frm->dni('dni', 'dni_required', Lang::get('pacientes.dni')) }}
    {{ $frm->hidden('persona_id', 'cita_id_note') }}
    {{ Form::token() }}
</form>
<div id="matched_patient" class="hidden">
    <p>{{ Lang::get('pacientes.matched_patient') }}</p>
    <div class="list-group">
        <a href="#" data-matched="0" data-current="0" class="list-group-item"></a>
    </div>
</div>
{{ $frm->modalClose() }}

<!-- NOTE FORM -->
{{ $frm->modalOpen('note_modal', Lang::get('citas.notes')) }}
    <form id="frm_note" class="form-horizontal" role="form" method="post" autocomplete="off" action="{{ URL::route('admin_cita_editar_nota_post') }}">
        <?php $frm->displayLabels(false); ?>
        {{ $frm->hidden('id', 'note_id') }}
        {{ $frm->textarea('contenido', null, '') }}
        {{ $frm->hidden('cita_id', 'cita_id_note') }}
        {{ Form::token() }}
    </form>
{{ $frm->modalClose() }}
<!-- /NOTE FORM -->

<!-- MOVE EVENT FORM -->
<form id="frm_data_move" class="form-horizontal hidden" role="form" method="post" autocomplete="off" action="{{ URL::route('admin_citas_editar_post') }}">
    {{ $frm->id() }}
    {{ $frm->date('fecha', null, Lang::get('citas.date'), 'day') }}
    {{ $frm->time('inicio', null, Lang::get('citas.time_start')) }}
    {{ $frm->time('fin', null, Lang::get('citas.time_end')) }}
    {{ $frm->hidden('grouped_events') }}
    {{ Form::token() }}
</form>
<!-- /MOVE EVENT FORM -->

<!-- GET EDIT EVENT FORM -->
<form id="frm_get_data_edit" class="hidden" role="form" method="get" autocomplete="off" action="{{ URL::route('cita_all_inf_get') }}">
    <input type="hidden" name="id" value="0">
</form>
<!-- /GET EDIT EVENT FORM -->

<!-- GET AVAILABILITY FORM -->
<form id="frm_get_availability" class="hidden" role="form" method="get" autocomplete="off" action="{{ URL::route('horario_calendar_source') }}">
    <input type="hidden" name="disposicion_id" value="0">
    <input type="hidden" name="start" value="">
    <input type="hidden" name="end" value="">
</form>
<!-- /GET AVAILABILITY FORM -->

<!-- SEARCH EVENT FORM -->
<form id="frm_get_search" class="hidden" role="form" method="get" autocomplete="off" action="{{ URL::route('calendar_search') }}">
    <input type="hidden" name="query" value="">
</form>
<!-- /SEARCH EVENT FORM -->

<!-- PRINT VIEW -->
<form id="frm_print_view" class="hidden" role="form" method="get" autocomplete="off" target="_blank" action="{{ URL::route('get_print_view_calendar') }}">
    <input type="hidden" name="day" value="">
</form>
<!-- /PRINT VIEW -->

{{ $frm->date('goto_date', null, null, 'day', 'hidden') }}

<!-- /MAIN CONTENT -->
@stop

@section('scripts')
{{ HTML::script('js/select2/select2.js') }}
{{-- HTML::script('js/bootstrap-datepicker/js/bootstrap-datepicker.js') --}}
{{ HTML::script('js/pickadate/picker.js') }}
{{ HTML::script('js/pickadate/picker.date.js') }}
{{ HTML::script('js/pickadate/picker.time.js') }}
{{ HTML::script('js/bootstrap-inputmask/bootstrap-inputmask.min.js') }}
{{ HTML::script('js/jquery-easing/jquery.easing.min.js') }}
{{ HTML::script('js/fullcalendar/lib/moment.min.js') }}
{{ HTML::script('js/fullcalendar/fullcalendar.js') }} <!-- customized -->
<?php if (Config::get('app.locale') != 'en') : ?>
    {{ HTML::script('js/select2/select2_locale_' . Config::get('app.locale') . '.js') }}
    {{ HTML::script('js/pickadate/translations/' . Config::get('app.locale') . '.js') }}
    {{ HTML::script('js/fullcalendar/lang/' . Config::get('app.locale') . '.js') }}
<?php endif; ?>
{{ HTML::script('js/gritter/js/jquery.gritter.min.js') }}
{{ HTML::script('js/panel.js') }}
<script type="text/javascript">
    var url_update_counter = "{{ URL::route('admin_citas_count_get') }}";

    var cita_ID;

    var availability_items;

    var creating_new_event = false;
    var showing_availability = false;
    var loading_availability_timer = false;
    var availability_source = '';

    var cal_top = 0;
    var loading_new_view = false;

    var _update_doctor_filter = null;
    var _update_patient_events = null;
    var _clear_patien_events = null;

    var _hovered_event;


    function setDateTime(start, end) {
        var $frm = $('#frm_new_event_date_time_inf');
        //setting date
        setDatePicker($frm.find('#fecha'), start._d);
        //setting start
        setTimePicker($frm.find('#inicio'), start._d);
        //setting end
        setTimePicker($frm.find('#fin'), end._d);

        submitForm( $frm, submitDateTimeFormDone, null, 'GET' );
    }

    function setDoctor() {
        var $actives = $('a.filter-doctor.active');
        if ($actives.length == 1) {
            var $frm = $('#frm_new_event_doctor_inf');
            var id = $actives.eq(0).attr('attr-id');
            $frm.find('input[name=doctor_id]').val(id);
            submitForm( $frm, submitDoctorFormDone, null, 'GET' );
        }
        else {
            /*var data = {};
            data['doctor_name_inf'] = '{{-- Lang::get('global.select') --}}';
            data['avatar_inf'] = '{{-- URL::asset('img/avatars/s/default.jpg') --}}';
            data['doctor_id'] = 0;
            submitDoctorFormDone(null, data);*/
        }
    }

    function hideNewEventPlaceHolder() {
        //$main_calendar.fullCalendar( 'removeEvents', 0); //<-- creates a bug where events get duplicated on the calendar
        $('a.stateundefined').remove();
    }

    function fn_new_event(start, end, allDay) {
        @if (!Auth::user()->admin)
        var mins = start._d.getUTCMinutes();
        if (mins > 0 && mins != 30) {
            start._d.setUTCMinutes(mins > 30 ? 30 : 0)
        }
        @endif
        window.creating_new_event = true;
        var $cal = $main_calendar;
        $cal.fullCalendar('renderEvent', {
                id: 0,
                title: '*',
                start: start,
                end: end
            },
            true // make the event "stick"
        );
        $cal.fullCalendar('unselect');
        var $modal = $('#new_event_form');
        $modal.find('input[name=id]').val('0');
        setDateTime(start, end);
        setDoctor();
        $modal.find('.modal-alert').hide();
        $modal.modal('show');
    }

    function fn_drop_event(event) {
        console.log(event);
        if (event.end == null) {
            $main_calendar.fullCalendar('refetchEvents');
            return false;
        }
        var $frm = $('#frm_data_move');
        //setting id
        $frm.find('.record-id').val( event['id'] );
        //setting group ids
        var group_ids = $('.fa-event.event' + event['id']).find('.grouped-event');
        var ids = '';
        $.each(group_ids, function(i,o) {
            ids += ((parseInt($(o).attr('attr-id')) || 0) + ',');
        });
        $frm.find('#grouped_events').val(ids);
        //setting date
        setDatePicker($frm.find('#fecha_edit'), event['start']._d);
        //setting start
        setTimePicker($frm.find('#inicio_edit'), event['start']._d);
        //setting end
        if (!event['allDay']) {
            setTimePicker($frm.find('#fin_edit'), event['end']._d);
        }
        else {
            setTimePicker($frm.find('#fin_edit'), null);
        }

        submitForm($frm, function($frm, data) {
            if (data['ok'] != 1) {
                //alert(data['err']);
                $.gritter.add({
                	title: 'Advertencia',
                	text: data['err'],
                    image: '{{ URL::asset('img/noti_error.png') }}'
                });
                $main_calendar.fullCalendar('refetchEvents');
            }
            else {
                $('.fc-event').remove();
                $main_calendar.fullCalendar('refetchEvents');
            }
        });
    }

    function updateCountPer(name) {
        //updates count of events per filter
        var $a = $('a.filter-' + name);
        $.each($a, function(i, o) {
            var id = $(o).attr('attr-id') || '0';
            window['total_' + name + id] = 0;
        });
        var $events = $('a.fc-event');
        var id;
        var $o;
        $.each($events, function(i, o) {
            $o = $(o);
            if (!$o.hasClass('availability') && !$o.hasClass('event-faded')) {
                if ($o.hasClass('has-many')) {
                    var $gv = $o.find('.grouped-event');
                    $.each($gv, function(j, u) {
                        var $u = $(u);
                        id = $u.attr('attr-' + name);
                        window['total_' + name + id] = (parseInt(window['total_' + name + id]) || 0) + 1;
                    });
                }
                else {
                    id = $o.find('input.' + name + '_id').val();
                    window['total_' + name + id] = (parseInt(window['total_' + name + id]) || 0) + 1;
                }
                window['color_' + name + id] = $o.find('input.doctor_color').val();//$o.css('background-color');
            }
        });
        $.each($a, function(i, o) {
            var $o = $(o);
            var id = $o.attr('attr-id') || '0';
            var total = window['total_' + name + id];
            var color = window['color_' + name + id];
            if (total > 0 && id >= 0) {
                if (name == 'mode') {
                    $o.find('span.badge').html(total).css('background-color', color).css('color', '#fff').removeClass('hidden');
                }
                else {
                    $o.find('span.badge').html(total).removeClass('hidden');
                }
            }
            else {
                if (name == 'state') {
                    $o.find('span.badge').addClass('hidden');
                }
                else {
                    $o.find('span.badge').parent().addClass('hidden');
                }
            }
        });
    }

    function updateCountAfterFilter() {
        /*setTimeout(function() {
            updateCountPer('doctor');
        }, 500);*/
    }

    function countTotalCitas() {
        var $events = $('.fc-event');
        var t = 0;
        var n = 0;
        $.each($events, function(i, e) {
            n = $(e).find('.grouped-event').length;
            t += (n == 0 ? 1 : n);
        });
        $('#total_citas').html( t + ' ' + (t == 1 ? 'cita' : 'citas') );
    }

    function fn_render_event(event) {
        //console.log('rendered: ' + event.id);
    }

    function fn_render_all_events(view) {
        setGroupedEventsHeight();

        hideNewEventPlaceHolder();
        if (!window.creating_new_event) {
            updateCountPer('doctor');
            if (_update_doctor_filter == null) {
                _update_doctor_filter = setTimeout(function() {
                    //updateCountPer('doctor');
                    highlightActive('doctor');
                    _update_doctor_filter = null;
                }, 1000);
            }
            
            updateCountPer('mode');
            highlightActive('mode');

            updateCountPer('state');
            highlightActive('state');

            updateCountPer('equipment');
            highlightActive('equipment');

            updateCountPer('technician');
            highlightActive('technician');
            
            bindEventClick();
    		$('.tip').tooltip();
            $('.tip-right').tooltip({placement : 'right'});
            //styling morning / afternoon separation
            $main_calendar.find('#hour_12').siblings('.fc-widget-content').addClass('ampm-separation');
            if (window['cal_top'] > 0) {
                $main_calendar.find('.fc-scroller').eq(0).scrollTop( window['cal_top'] );
            }
            window['loading_new_view'] = false;
            //gets the calendar top after scrolling
            $main_calendar.find('.fc-scroller').scroll(function() {
                if (!window['loading_new_view']) {
                    window['cal_top'] = $main_calendar.find('.fc-scroller').eq(0).scrollTop();
                }
            });

            //patients events hover
            bindEventHover();
        }
        else {
            window.creating_new_event = false;
        }
        //highlighting today
        var $today = $('.fc-today');
        if ($today.length) {
            $('.fc-day-header:nth-child(' + ($today.index() + 1) + ')').addClass('today-header');
        }
        addNewMode(false);

        countTotalCitas();
    }

    function submitDateTimeFormDone($frm, data) {
        $('#cita_date_time').html( data['fecha_inf'] + ' &nbsp; <span class="badge">' + data['hora_inf'] + '</span>' ).removeClass('text-muted');
        $('#cita_date_time_remaining').html( data['restante'] );

        $('#fecha_hidden').val( data['fecha'] );
        $('#inicio_hidden').val( data['inicio'] );
        $('#fin_hidden').val( data['fin'] );
    }

    function submitDoctorFormDone($frm, data) {
        $('#cita_doctor_name').html( data['doctor_name_inf'] ).removeClass('text-muted');
        //$('#cita_doctor_inf').html(  );
        $('#cita_doctor_avatar').attr('src', data['avatar_inf']);

        $('#doctor_id_hidden').val( data['doctor_id'] );
    }

    function submitTechnicianFormDone($frm, data) {
        $('#cita_technician_name').html( data['technician_name_inf'] ).removeClass('text-muted');
        $('#cita_technician_avatar').attr('src', data['avatar_inf_technician']);

        $('#tecnico_id_hidden').val( data['tecnico_id'] );
    }

    function submitPatientFormDone($frm, data) {
        $('#cita_patient_name').html( data['patient_name_inf'] ).removeClass('text-muted');
        $('#cita_patient_inf').html( data['record_inf'] + '  (' + data['num_citas_inf'] + ')');

        $('#persona_id_hidden').val( data['persona_id'] );

        $('#persona_id').select2('data', {id:data['persona_id'], text:data['patient_name_inf'].split('<span')[0].trim()});
    }

    function submitNewPatientFormDone($frm, data) {
        submitFormDoneDefault($frm, data);
        if (data['ok']) {
            $('#persona_id').val( data['created_id'] );
            var $inf_frm = $('#frm_new_event_patient_inf');
            submitForm( $inf_frm, submitPatientFormDone, null, 'GET' );
            $inf_frm.closest('.modal').modal('hide');
            Panel.resetForm( $frm );
        }
    }

    function submitEditPatientFormDone($frm, data) {
        submitFormDoneDefault($frm, data);
        if (data['ok']) {
            var $inf_frm = $('#frm_new_event_patient_inf');
            submitForm( $inf_frm, submitPatientFormDone, null, 'GET' );
            $inf_frm.closest('.modal').modal('hide');
        }
    }

    function submitServiceFormDone($frm, data, autoload_equipment, autoload_office) {
        autoload_equipment = typeof autoload_equipment == 'boolean' ? autoload_equipment : true;
        autoload_office = typeof autoload_office == 'boolean' ? autoload_office : true;
        $('#cita_service_name').html( data['service_name_inf'] ).removeClass('text-muted');
        $('#cita_service_inf').html( data['duration_inf'] );

        $('#servicio_id_hidden').val( data['servicio_id'] );

        if (autoload_equipment) {
            $('#cita_equipment_name').html( '{{ Lang::get('global.loading') }}' ).removeClass('error').addClass('text-muted');
            $('#equipo_id_hidden').val('');

            $('#cita_office_name').html( '{{ Lang::get('global.loading') }}' ).removeClass('error').addClass('text-muted');
            $('#consultorio_id_hidden').val('');
        }
        var fecha = $('#fecha_hidden').val();
        var inicio = $('#inicio_hidden').val();
        getAvailableEquipments( data['servicio_id'], fecha, inicio, autoload_equipment );
        getAvailableOffices( data['servicio_id'], fecha, inicio, autoload_office );
    }

    function submitEquipmentFormDone($frm, data) {
        $('#cita_equipment_name').html( data['equipment_name_inf'] ).removeClass('text-muted error');
        $('#cita_equipment_inf').html('');

        $('#equipo_id_hidden').val( data['equipo_id'] );
    }

    function submitOfficeFormDone($frm, data) {
        $('#cita_office_name').html( data['office_name_inf'] ).removeClass('text-muted error');
        $('#cita_office_inf').html('');

        $('#consultorio_id_hidden').val( data['consultorio_id'] );
    }

    function submitNoteFormDone($frm, data) {
        submitFormDoneDefault($frm, data);
        if (data['ok'] == 1) {
            $frm.closest('.modal').modal('hide');
            $main_calendar.fullCalendar('refetchEvents');
        }
    }

    function submitDniFormDone($frm, data) {
        submitFormDoneDefault($frm, data);
        var $matched = $('#matched_patient');
        if (data['ok'] == 1) {
            $frm.closest('.modal').modal('hide');
            setState(cita_ID, {{ Cita::CONFIRMED }});
            $matched.addClass('hidden');
        }
        else {
            if (typeof data['matched_id'] != 'undefined') {
                var $a = $matched.find('a').eq(0);
                $a.html( data['patient_name'] + '<br><span class="text-muted">{{ Lang::get('pacientes.record_date') }}: ' + data['patient_record_date'] + '</span>' );
                $a.attr('data-matched', data['matched_id']);
                $a.attr('data-current', data['current_id']);
                $matched.removeClass('hidden');
            }
        }
    }

    function submitFormDone($frm, data) {
        submitFormDoneDefault($frm, data);
        $('.status-icon').removeClass('bad');
        if (data['ok'] == 1) {
            $('.fc-event').remove();
            $main_calendar.fullCalendar('refetchEvents');
            resetIgnoreWarningCheckboxes();
            clearForm(false);
        }
        else {
            var $ignore_options = $('#warning_ignore_options');
            var $warning_key = $('#warning_key');
            if (data['bad']) {
                $('#icon_' + data['bad']).addClass('bad');
                if ($ignore_options.length && (typeof data['allow_ignore'] == 'undefined' || data['allow_ignore'] != 0)) {
                    $ignore_options.removeClass('hidden');
                    $warning_key.val( data['warning_key'] );
                }
            }
            else {
                if ($ignore_options.length) {
                    $ignore_options.addClass('hidden');
                    $warning_key.val('0');
                }
            }
        }
    }

    function resetIgnoreWarningCheckboxes() {
        $('#warning_key').val(0);
        $('#ignore_warning_submit').val(0);
        $('#ignore_warning_all_submit').val(0);
        $('#ignore_warning').prop('checked', false);
        $('#ignore_warning_all').prop('checked', false);
    }

    function selectEquipment(id, msg) {
        var $frm = $('#frm_new_event_equipment_inf');
//      $frm.find('input[name=equipo_id]').select2('val', id);
        $frm.find('input[name=equipo_id]').val(id);
        if (id > 0) {
            submitForm($frm, submitEquipmentFormDone, null, 'GET');
        }
        else {
            var exclamation = '<span class="pull-right"><i class="fa fa-exclamation-triangle"></i></span>';
            $('#cita_equipment_name').html( exclamation + (typeof msg == 'string' ? msg : '') ).addClass('text-muted error');
            $('#cita_equipment_inf').html('');
            $('#equipo_id_hidden').val('');
        }
    }

    function selectOffice(id, msg) {
        var $frm = $('#frm_new_event_office_inf');
        $frm.find('input[name=consultorio_id]').val(id);
        if (id > 0) {
            submitForm($frm, submitOfficeFormDone, null, 'GET');
        }
        else {
            var exclamation = '<span class="pull-right"><i class="fa fa-exclamation-triangle"></i></span>';
            $('#cita_office_name').html( exclamation + (typeof msg == 'string' ? msg : '') ).addClass('text-muted error');
            $('#cita_office_inf').html('');
            $('#consultorio_id_hidden').val('');
        }
    }

    function bindEquipmentButtons() {
        $('.equipment-btn').click(function() {
            var $btn = $(this);
            var id = parseInt($btn.attr('attr-id')) || 0;
            if (id > 0) {
                selectEquipment(id);
                $btn.closest('.modal').modal('hide');
            }
        });
    }

    function bindOfficesButtons() {
        $('.office-btn').click(function() {
            var $btn = $(this);
            var id = parseInt($btn.attr('attr-id')) || 0;
            if (id > 0) {
                selectOffice(id);
                $btn.closest('.modal').modal('hide');
            }
        });
    }

    function getAvailableEquipments(service_id, cdate, start, autoselect) {
        var $holder = $('#available_equipments_holder');
        if ((parseInt(service_id) || 0) > 0) {
            $.ajax({
                type: 'GET',
                url: '{{ URL::route('get_available_equipments') }}',
                dataType: 'json',
                data: { servicio_id: service_id, fecha: cdate, inicio: start, ignore_cita_id: $('#frm_data_new_event').find('#cita_id').val() }
            }).done(function(data) {
                console.log(data);
                if (data['ok']) {
                    $holder.html( data['btns_list'] );
                    if ($('.equipment-btn').length == 0) {
                        if (parseInt($('#servicio_id_hidden').val()) > 0) {
                            $holder.html('<p class="text-center">{{ Lang::get('servicio.no_equipments_attached_to_service') }}</p>');
                        }
                        else {
                            $holder.html('<p class="text-center">{{ Lang::get('servicio.no_equipments_select_service_first') }}</p>');
                        }
                    }
                    else {
                        bindEquipmentButtons();
                    }
                    if (typeof autoselect == 'boolean' && autoselect) {
                        if (typeof data['available'] != 'undefined') {
                            selectEquipment( data['available'], data['msg'] );
                        }
                        else {
                            var $lbl = $('#cita_equipment_name');
                            $lbl.html( $lbl.attr('data-select_lbl') );
                        }
                    }
                }
                else {
                    $holder.html('');
                }
            }).fail(function(data) {
                console.log(data); //failed
            });
        }
    }

    function getAvailableOffices(service_id, cdate, start, autoselect) {
        var $holder = $('#available_offices_holder');
        if ((parseInt(service_id) || 0) > 0) {
            $.ajax({
                type: 'GET',
                url: '{{ URL::route('get_available_offices') }}',
                dataType: 'json',
                data: { servicio_id: service_id, fecha: cdate, inicio: start, ignore_cita_id: $('#frm_data_new_event').find('#cita_id').val() }
            }).done(function(data) {
                console.log(data);
                if (data['ok']) {
                    $holder.html( data['btns_list'] );
                    if ($('.office-btn').length == 0) {
                        if (parseInt($('#servicio_id_hidden').val()) > 0) {
                            $holder.html('<p class="text-center">{{ Lang::get('servicio.no_offices_attached_to_service') }}</p>');
                        }
                        else {
                            $holder.html('<p class="text-center">{{ Lang::get('servicio.no_offices_select_service_first') }}</p>');
                        }
                    }
                    else {
                        bindOfficesButtons();
                    }
                    if (typeof autoselect == 'boolean' && autoselect) {
                        if (typeof data['available'] != 'undefined') {
                            selectOffice( data['available'], data['msg'] );
                        }
                        else {
                            var $lbl = $('#cita_office_name');
                            $lbl.html( $lbl.attr('data-select_lbl') );
                        }
                    }
                }
                else {
                    $holder.html('');
                }
            }).fail(function(data) {
                console.log(data); //failed
            });
        }
    }

    function highlightActive(name) {
        var $actives = $('a.filter-' + name + '.active');
        if ($actives.length == 0) {
            $('a.fc-event').removeClass('event-faded wide');
        }
        else {
            setTimeout(function() {
                $('a.group-filter').not('.filter-' + name)/*.not('.filter-doctor')*/.removeClass('active');
                $('a.fc-event').addClass('event-faded');
                $.each($actives, function(i, o) {
                    var $o = $(o);
                    var $events = $('a.fc-event');
                    $.each($events, function(j, e) {
                        var $e = $(e);
                        if ($e.find('input.' + name + '_id').val() == $o.attr('attr-id') || ($e.hasClass('has-many') && $e.find('.grouped-event.' + name + $o.attr('attr-id')).length > 0)) {
                            $e.removeClass('event-faded');
                            if ($actives.length == 1) {
                                if (name == 'doctor' || name == 'equipment' || name == 'office') {
                                    $e.addClass('wide');
                                }
                                else {
                                    $e.removeClass('wide');
                                }
                            }
                            else {
                                $e.removeClass('wide');
                            }
                        }
                    });
                });
            }, 100);
        }
    }

    function removeActive() {
        $('a.group-filter').removeClass('active');
    }

    function showPatientsEvents(patient_id) {
        var $events = $('a.fc-event');
        $.each($events, function(j, e) {
            var $e = $(e);
            if (patient_id != false && $e.find('input.patient_id').val() == patient_id) {
                $e.addClass('emphasized');
            }
            else {
                $e.removeClass('emphasized');
            }
        });
    }

    function twoDigits(number) {
        if (number < 10) {
            return '0' + number;
        }
        return number;
    }

    function loadAvailability(id) {
        //remove old ones
        $('a.fc-event.availability').remove();
        $main_calendar.fullCalendar('removeEvents', 0);
        if (window['availability_source'].length) {
            $main_calendar.fullCalendar( 'removeEventSource', window['availability_source'] );
        }

        if (id > 0) {
            window['availability_source'] = '{{ URL::route('horario_calendar_source') }}/' + id;

            $main_calendar.fullCalendar( 'addEventSource', window['availability_source'] );
        }
    }

    function showHideNewPatient() {
        var persona_id = parseInt($('#persona_id').val()) || 0;
        var $new_patient_holder = $('#new_patient');
        var $patient_inf_holder = $('#patient_inf');

        if (persona_id > 0) {
            $.ajax({
                type: 'GET',
                url: '{{ URL::route('admin_pacientes_datos_get') }}',
                dataType: 'json',
                data: {id: persona_id}
            }).done(function(data) {
                if (data['ok']) {
                    var $frm = $('#frm_data_edit_patient');
                    Panel.objectToInputs($frm, data);
                    $frm.find('input.changed').val('0');
                }
            });
            $new_patient_holder.slideUp();
            $patient_inf_holder.slideDown();
        }
        else {
            $patient_inf_holder.slideUp();
            $new_patient_holder.removeClass('hidden').slideDown();
        }
    }

    function bindEventClick() {
        $('a.fc-event').click(function() {
            var $e = $(this);
            cita_ID = parseInt($e.find('input.id').val()) || 0;
            if (cita_ID > 0) {
                getState(cita_ID);
                var $modal = $('#actions_modal');
                $modal.css('visibility', 'hidden');
                $modal.modal('show');
                setTimeout(function() {
                    setActionsModalWidth();
                    $modal.css('visibility', 'visible');
                }, 300);
            }
            var $states = $('#states');
            $states.find('#state{{ Cita::CANCELLED }}').attr('attr-confirm_nexts', '0');
            $states.find('#state{{ Cita::CONFIRMED }}').attr('attr-confirm_nexts', '0');
        });
        $('.grouped-event').click(function(e) {
            var $e = $(this);
            var $ge;
            var $frm_action;
            var next_state = -1;
            var nexts = '';
            e.preventDefault();
            cita_ID = parseInt($(this).attr('attr-id')) || 0;
            if (cita_ID > 0) {
                getState(cita_ID);
                var $modal = $('#actions_modal');
                $modal.css('visibility', 'hidden');
                $modal.modal('show');
                setTimeout(function() {
                    setActionsModalWidth();
                    $modal.css('visibility', 'visible');
                }, 300);
                //grouped events siblings
                $ge = $e.nextAll('.grouped-event');
                $.each($ge, function(i, e) {
                    if (next_state == -1) {
                        next_state = parseInt($(e).attr('attr-state'));
                        var $states = $('#states');
                        var ask_confirmation = (next_state == {{ Cita::UNCONFIRMED}} || next_state == {{ Cita::CONFIRMED }}) ? '1' : '0';
                        $states.find('#state{{ Cita::CANCELLED }}').attr('attr-confirm_nexts', ask_confirmation);
                        $states.find('#state{{ Cita::CONFIRMED }}').attr('attr-confirm_nexts', ask_confirmation);
                    }
                    nexts += ($(e).attr('attr-id') + ',');
                });
                $frm_action = $('#frm_action');
                $frm_action.find('input[name=grouped_nexts]').val( nexts );
                $frm_action.find('input[name=grouped_nexts_apply]').val('0');
            }
            return false;
        });
    }

    function showState(state, time_diff) {
        var $states = $('#states');
        var $btns = $states.find('button');
        time_diff = typeof time_diff == 'undefined' ? 0 : parseInt(time_diff);
        $btns.removeClass('active btn-primary btn-danger btn-success btn-warning').addClass('btn-default');
        var $btn = $('#state' + state);
        if ($btn.length) {
            $btn.removeClass('btn-default disabled').addClass('active btn-' + $btn.attr('attr-type'));
        }
        @if (!User::canUndoCitaState())
            if (state == {{ Cita::DONE }} || (state == {{ Cita::CANCELLED }} && time_diff < 0)) {
                $btns.not('#state' + state).addClass('disabled');
            }
            else {
                @if (User::canConfirmOrCancelCita())
                $btns.removeClass('disabled');
                @endif
            }
            //disable edit if state equal done (1), confirmed (2) or cancelled (3)
            var $btn_edit = $('#edit_cita');
            var $btn_done = $('#state{{ Cita::DONE }}');
            if (state != {{ Cita::UNCONFIRMED }}) {
                $btn_edit.addClass('disabled');
                if (state != {{ Cita::CANCELLED }}) {
                    if (typeof time_diff == 'undefined' || parseInt(time_diff) < 0) {
                        $btn_done.removeClass('disabled');
                    }
                    else {
                        $btn_done.addClass('disabled');
                    }
                }
            }
            else {
                $btn_edit.removeClass('disabled');
                $btn_done.addClass('disabled');
            }
        @endif
    }

    function setState(cita_id, state) {
        showState(state);
        var $frm = $('#frm_action');
        $frm.find('input[name=cita_id]').val( cita_id );
        $frm.find('input[name=action]').val( 'set_state' );
        $frm.find('input[name=val]').val( state );
        submitForm( $frm, function($frm, data) {
            if (data['ok']) {
                showState( data['state'], data['time_diff'] );
                if (typeof data['dni_required'] != 'undefined') {
                    //patient doesn't have a dni in the system, one should be required
                    var persona_id = parseInt(data['dni_required']) || 0;
                    if (persona_id > 0) {
                        var $modal = $('#patient_dni_modal');
                        $modal.find('input[name=persona_id]').val( persona_id );
                        $modal.modal('show');
                    }
                }
                else {
                    /*var $events = $('a.fc-event').not('.has-many');
                    $.each($events, function(i, e) {
                        var $e = $(e);
                        if ($e.find('input.id').val() == data['cita_id']) {
                            $e.removeClass('state0 state1 state2 state3').addClass('state' + data['state']);
                            $e.find('input.state_id').val( data['state'] );
                            updateCountPer('state');
                            return false;
                        }
                    });*/
                    $main_calendar.fullCalendar('refetchEvents');
                }
                if (isset(data['msg']) && data['msg'].length > 0) {
                    $.gritter.add({
                        title: 'Nota',
                        text: data['msg'],
                        image: '{{ URL::asset('img/noti_info.png') }}'
                    });
                }
            }
        });
    }

    function getState(cita_id) {
        var $frm = $('#frm_action');
        $frm.find('input[name=cita_id]').val( cita_id );
        $frm.find('input[name=action]').val( 'get_state' );
        submitForm( $frm, function($frm, data) {
            if (data['ok']) {
                showState( data['state'], data['time_diff'] );
                showCitaInfoForState( data['cita'] );
            }
        });
    }

    function showCitaInfoForState(data) {
        var $inf = $('#actions_modal').find('.cita-info');
        $inf.find('.fecha').html( data['date'] );
        $inf.find('.hora').html( data['range'] );
        $inf.find('.paciente').html( data['patient'] );
        $inf.find('.doctor').html( data['doctor'] );
        $inf.find('.tecnico').html( data['technician'] );
        $inf.find('.oficina').html( data['office'] );
        $inf.find('.servicio').html( data['service'] + (data['equipment'].length ? (' (' + data['equipment'] + ')') : '') );
    }

    @if (User::canDeleteCitas($user))
    function delCita(cita_id) {
        var $frm = $('#frm_action_delete');
        $frm.find('input[name=id]').val( cita_id );
        //$frm.find('input[name=action]').val( 'action_delete' );
        submitForm( $frm, function($frm, data) {
            if (data['ok'] && data['deleted']) {
                $('.fc-events').remove();
                $main_calendar.fullCalendar('refetchEvents');
                /*var $events = $('a.fc-event');
                $.each($events, function(i, e) {
                    var $e = $(e);
                    if ($e.find('input.id').val() == data['record']) {
                        $e.remove();
                        $main_calendar.fullCalendar('removeEvents', cita_id);
                        return false;
                    }
                });*/
            }
        });
    }
    @endif

    function bindDoctorByLetter() {
        var $holder = $('#doctors_by_letter_holder');
        $holder.find('a').click(function() {
            var $a = $(this);
            var id = parseInt($a.attr('data-id')) || 0;
            if (id > 0) {
                var $frm = $('#frm_new_event_doctor_inf');
                $frm.find('input[name=doctor_id]').val(id);
                submitForm( $frm, function($frm, data) {
                    submitDoctorFormDone($frm, data);
                    $holder.closest('.modal').modal('hide');
                }, null, 'GET');
            }
        });
    }

    function bindServiceByCategory() {
        var $holder = $('#service_by_category_holder');
        $holder.find('a').click(function() {
            var $a = $(this);
            var id = parseInt($a.attr('data-id')) || 0;
            if (id > 0) {
                var $frm = $('#frm_new_event_service_inf');
                $frm.find('input[name=servicio_id]').val(id);
                submitForm( $frm, function($frm, data) {
                    submitServiceFormDone($frm, data);
                    $holder.closest('.modal').modal('hide');
                }, null, 'GET');
            }
        });
    }

    function emphasizeEvent(event_id) {
        var $events = $('a.fc-event');
        $.each($events, function(i, o) {
            var $o = $(o);
            var id = $o.find('input.id').val();
            if (id == event_id) {
                setTimeout(function() {
                    $o.addClass('bring-to-front animated tada');
                }, 1000);
                setTimeout(function() {
                    $o.removeClass('bring-to-front animated tada');
                }, 4000);
                return false;
            }
        });
    }

    function gotoDate(date) {
        if (typeof date != 'undefined' && date.length) {
            var $cal = $main_calendar;
            var top = $cal.find('.fc-scroller').eq(0).scrollTop();
            $cal.fullCalendar('gotoDate', date);
            $cal.find('.fc-scroller').eq(0).scrollTop(top);
            $cal.fullCalendar('scrollTo', parseInt(date.split('T')[1]), $cal);
        }
    }

    function doFind(query) {
        if (query.length > 0) {
            var $frm = $('#frm_get_search');
            $frm.find('input[name=query]').val( query );
            submitForm($frm, function($frm, data) {
                if (data['ok'] == 1) {
                    removeActive();
                    gotoDate( data['fecha'] );
                    setTimeout(function() {
                        emphasizeEvent( data['cita_id'] );
                    }, 2000);
                }
            }, null, 'GET');
        }
    }
    
    function setActionsModalWidth() {
        var $modal = $('#actions_modal').find('.modal-dialog');
        var $btns = $modal.find('.btn-toolbar').find('.btn-group');
        var width = 0;
        
        $.each($btns, function(i, o) {
          width += $(o).outerWidth();
        });

        if (width > 0) $modal.width( width + 50 );
    }

    /*function checkAvailability() {
        var $frm = $('#frm_data_new_event');
        var url = '{{ URL::route('admin_citas_check_availability_post') }}';
        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            data: $frm.serialize() // serializes the form's elements.
        }).done(function(data) {
            console.log(data);
            if (data['ok']) {

            }
        }).fail(function(data) {
            console.log(data); //failed
        });
    }*/


    function clearForm(clear_patient) {
        clear_patient = typeof clear_patient != 'boolean' ? true : clear_patient;
        //var select_lbl = '{{-- Lang::get('global.select') --}}';
        var $e;
        var $frm = $('#frm_data_new_event');
        var $titles = $frm.find('h4').not('#cita_date_time').not('#cita_patient_name');
        $.each($titles, function(i,e) {
            $e = $(e);
            $e.html( $e.attr('data-select_lbl') ).addClass('text-muted');
        });
        $frm.find('.form-item').not('.datetime').not('.patient').find('p').html('');
        $frm.find('.form-item').not('.datetime').not('.patient').find('input').val('');
        $frm.find('#cita_doctor_avatar').attr('src', '{{ URL::asset('img/avatars/s/default.jpg') }}');
        $frm.find('#cita_technician_avatar').attr('src', '{{ URL::asset('img/avatars/s/default.jpg') }}');
        $frm.find('.status-icon').removeClass('bad');

        if (clear_patient) {
            var $p = $frm.find('#cita_patient_name');
            $p.addClass('text-muted').html( $p.attr('data-select_lbl') );
            $frm.find('.form-item.patient').find('p').html('');
            $frm.find('.form-item.patient').find('input').val('');
            $('#persona_id').val('').select2('data', null);
        }
    }

    function bindEventHover() {
        $('.fc-event').on('mouseenter', function() {
            clearTimeout(_clear_patien_events);
            _clear_patien_events = null;
            if (_update_patient_events == null) {
                var id = $(this).find('input.patient_id').val();
                _update_patient_events = setTimeout(function() {
                    showPatientsEvents(id);
                    _update_patient_events = null;
                }, 1500);
            }
        }).on('mouseleave', function() {
            clearTimeout(_update_patient_events);
            _update_patient_events = null;
            if (_clear_patien_events == null) {
                _clear_patien_events = setTimeout(function() {
                    showPatientsEvents(false);
                    _clear_patien_events = null;
                }, 200);
            }
        });
    }

    function addNewMode(active) {
        if (active) {
            $main_calendar.addClass('add-new-mode');
            $main_calendar.find('.fc-event').addClass('add-new-mode');
            $('#add_event_btn').addClass('fc-state-active');
        }
        else {
            $main_calendar.removeClass('add-new-mode');
            $main_calendar.find('.fc-event').removeClass('add-new-mode');
            $('#add_event_btn').removeClass('fc-state-active');
        }
    }

    function setGroupVisibilities() {
        var $groups = $('.toggle-visibility');
        var states = [];
        $.each($groups, function(i,e) {
            var $e = $(e);
            var obj = { id : $e.attr('data-id'), state : ($e.hasClass('not-visible') ? 0 : 1) };
            states.push( obj );
        });
        //updates in server
        $.ajax({
            type: 'POST',
            url: '{{ URL::route('set_calendar_groups') }}',
            data: { 'groups' : states }
        }).done(function(data) {
            $('.fc-event').remove();
            $main_calendar.fullCalendar('refetchEvents');
        });
    }

    function highlightService(id) {
        console.log('service ' + id);
        if (id > 0) {
            var $events = $('a.fc-event');
            $.each($events, function(j, e) {
                var $e = $(e);
                if ($e.find('input.service_id').val() == id || ($e.hasClass('has-many') && $e.find('.grouped-event.service' + id).length > 0)) {
                    $e.removeClass('event-faded');
                }
                else {
                    $e.addClass('event-faded');
                }
            });
        }
        else {
            highlightActive('doctor');
            highlightActive('state');
            highlightActive('equipment');
        }
    }

    function showLoading(show) {
        var $loading = $('#loading_overlay');
        if (show) {
            $loading.fadeIn();
        }
        else {
            $loading.fadeOut();
        }
    }

    function requestMergePatient(matched_id, current_id) {
        var url = '{{ URL::route('patient_merge_post') }}';
        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            data: { 'matched_id': matched_id, 'current_id': current_id, '_token': $('input[name=_token]').eq(0).val() }
        }).done(function(data) {
            console.log(data);
            if (data['ok']) {
                $('#patient_dni_modal').modal('hide');
                $('#matched_patient').addClass('hidden');
                getState(cita_ID);
                $main_calendar.fullCalendar('refetchEvents');
            }
            else {
                alert(data['err']);
            }
        }).fail(function(data) {
            console.log(data); //failed
        });
    }

    function getInvervalHeight() {
        return $('.fc-slats').find('tr').eq(0).height();
    }

    function setGroupedEventsHeight() {
        var interval_height = getInvervalHeight();
        var $ge = $('.grouped-event');
        var $e;
        var duration;
        $.each($ge, function(i, e) {
            $e = $(e);
            duration = $e.attr('attr-duration');
            if (duration > 0) {
                if ($e.hasClass('first-in-group')) {
                    $e.height( ((duration / 10) - 1) * interval_height );
                }
                else {
                    $e.height( (duration / 10) * interval_height );
                }
            }
        });
    }

    function loadHistory(cita_id) {
        viewHistory('{{ Lang::get('global.loading') }}', true);
        $.ajax({
            type: 'GET',
            url: '{{ URL::route('get_cita_history') }}',
            dataType: 'json',
            data: {'cita_id': cita_id}
        }).done(function(data) {
            console.log(data);
            if (data['ok']) {
                viewHistory(data['html']);
            }
        }).fail(function(data) {
            console.log('error', data); //failed
            viewHistory('');
        });
    }

    function viewHistory(data, show_modal) {
        show_modal = typeof show_modal != 'boolean' ? false : show_modal;
        $('#history_list').html(data);
        if (show_modal) {
            $('#history_modal').modal('show');
        }
    }

    var $main_calendar = $('#main_calendar');

    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

        {{ $frm->script() }}

        /* initialize the calendar
        -----------------------------------------------------------------*/
        $main_calendar.fullCalendar({
            'lang': '{{ Config::get('app.locale') }}',
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            selectable: {{ !$read_only ? 'true' : 'false' }},
            selectHelper: {{ !$read_only ? 'true' : 'false' }},
            selectConstraint: {
                start: '00:00',
                end: '23:59',
                dow: [ {{ $options['days_to_show_str'] }} ]
            },
            eventStartEditable: {{ !$read_only ? 'true' : 'false' }},
            eventDurationEditable: false,
            //lazyFetching: false,
            firstDay: 1,
            weekends: true,
            allDaySlot: true,
            allDayHtml: '',
            allDayText: '',
            defaultView: '{{ Cookie::get('calendar_view', 'agendaWeek') }}',
            defaultDate: '{{ Cookie::get('calendar_day', date('Y-m-d')) }}',
            timeFormat: 'h(:mm)t',
            axisFormat: 'h(:mm)t',
            displayEventEnd: false,
            slotDuration: '00:10:00',
            slotEventOverlap: true,
            hiddenDays: [{{ $options['days_to_hide_str'] }}],
            businessHours: {
                start: '{{ $options['start_time'] }}',
                end: '{{ $options['end_time'] }}',
                dow: [ {{ $options['days_to_show_str'] }} ]
                // days of week. an array of zero-based day of week integers (0=Sunday)
            },
            minTime: '{{ $options['min_time'] }}',
            maxTime: '{{ $options['max_time'] }}',
            events: '{{ URL::route('calendar_source') }}',
            @if (!$read_only)
            select: function(start, end, allDay) {
                if (typeof fn_new_event == 'function') {
                    fn_new_event(start, end, allDay);
                }
            },
            eventDragStart: function( event, jsEvent, ui, view ) {

            },
            eventDrop: function( event, delta, revertFunc, jsEvent, ui, view ) {
                if (typeof fn_drop_event == 'function') {
                    fn_drop_event(event);
                }
            },
            @endif
            eventRender: function( event, element, view ) {
                if (typeof fn_render_event == 'function') {
                    fn_render_event(event);
                }
            },
            eventAfterAllRender: function( view ) {
                if (typeof fn_render_all_events == 'function') {
                    fn_render_all_events(view);
                }
                showLoading(false);
            },
            viewDestroy: function() {
                $('#total_citas').html('');
                window['loading_new_view'] = true;
                addNewMode(false);
                showLoading(true);
            },
            viewRender: function(view, element) {
                $.ajax({
                    type: 'POST',
                    url: '{{ URL::route('set_calendar_view') }}',
                    data: { 'view' : view.name, 'day' : view.intervalStart.format() }
                });
                var $print_view_btn = $('#print_cal_btn');
                if (view.name == 'agendaDay') {
                    $print_view_btn.show();
                }
                else {
                    $print_view_btn.hide();
                }
            },
            editable: {{ $read_only ? 'false' : 'true' }},
            droppable: false/*, // this allows things to be dropped onto the calendar !!!
            drop: function(date, allDay) { // this function is called when something is dropped

                // retrieve the dropped element's stored Event Object
                var originalEventObject = $(this).data('eventObject');

                // we need to copy it, so that multiple events don't have a reference to the same object
                var copiedEventObject = $.extend({}, originalEventObject);

                // assign it the date that was reported
                copiedEventObject.start = date;
                copiedEventObject.allDay = allDay;

                // render the event on the calendar
                // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
                $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);

                // is the "remove after drop" checkbox checked?
                if ($('#drop-remove').is(':checked')) {
                    // if so, remove the element from the "Draggable Events" list
                    $(this).remove();
                }

            },
            events: (typeof getCalendarEvents == 'function' ? getCalendarEvents() : [])*/
        });
        //----- End calendar Initialization -----

        $(window).resize(function() {
            var $cal = $main_calendar;
            var height = Math.floor( $(this).height() - $cal.offset().top ) - 80;
            $cal.fullCalendar('option', 'contentHeight', height);

            var $fil_hol = $('#filter_holder');
            $fil_hol.height( height );
        }).resize();

        //go to date button & new event button
        var buttons = '<button id="goto_date_btn" class="fc-button fc-state-default fc-corner-left fc-corner-right" type="button">{{ Lang::get('citas.goto') }}</button>';
        @if (!User::is('doctor') || Auth::user()->admin)
        buttons += '<button id="add_event_btn" class="fc-button fc-state-default fc-corner-left fc-corner-right" type="button"><i class="fa fa-plus"></i> {{ Lang::get('citas.add_new') }}</button>';//+
                   //'<button id="print_cal_btn" class="fc-button fc-state-default fc-corner-left fc-corner-right" type="button"><i class="fa fa-table"></i> {{-- Lang::get('citas.print_view') --}}</button>';
        @endif
        $main_calendar.find('.fc-toolbar').find('.fc-left').append($(buttons));
        
        $('#goto_date_btn').click(function() {
            setTimeout(function() {
                $('#goto_date_edit').pickadate('picker').open();
            }, 300);
        }).mouseenter(function() {
            $(this).addClass('fc-state-hover');
        }).mouseleave(function() {
            $(this).removeClass('fc-state-hover');
        });

        $('#add_event_btn').click(function() {
            addNewMode( !$(this).hasClass('fc-state-active') );
        }).mouseenter(function() {
            $(this).addClass('fc-state-hover');
        }).mouseleave(function() {
            $(this).removeClass('fc-state-hover');
        });

        $('#print_cal_btn').click(function() {
            var $frm = $('#frm_print_view');
            var d = $main_calendar.fullCalendar('getView').start._d;
            $frm.find('input[name=day]').val( d.getUTCFullYear() + '-' + twoDigits(d.getUTCMonth()+1) + '-' + twoDigits(d.getUTCDate()) );
            $frm.submit();
        }).mouseenter(function() {
            $(this).addClass('fc-state-hover');
        }).mouseleave(function() {
            $(this).removeClass('fc-state-hover');
        });

        $('#goto_date_edit').pickadate('picker').on('set', function() {
            var $dp = $('#goto_date_edit').pickadate('picker');
            gotoDate( $dp.get('select', 'yyyy-mm-dd') );
        });


        $('#new_event_form').on('hidden.bs.modal', function() {
            hideNewEventPlaceHolder();
             var $form = $(this).find('form').eq(0);
            if (parseInt($form.find('input[name=id]').val()) > 0) {
                clearForm();
            }
        }).find('button.modal-btn-ok').click(function() {
            var $form = $(this).closest('.modal').find('form').eq(0);
            //new one
            if (parseInt($form.find('input[name=id]').val()) == 0) {
                submitForm( $form, submitFormDone );
            }
            //existing one
            else {
                submitForm( $form, submitFormDone, null, null, null, '{{ URL::route('admin_citas_editar_post') }}' );
            }
        });

        // getting date time inf
        $('#new_event_date_time_modal').find('button.modal-btn-ok').click(function() {
            var $modal = $(this).closest('.modal');
            var $form = $modal.find('form').eq(0);
            submitForm( $form, submitDateTimeFormDone, null, 'GET' );
            $modal.modal('hide');
        });

        // getting doctor inf
        $('#new_event_doctor_modal').find('button.modal-btn-ok').click(function() {
            var $modal = $(this).closest('.modal');
            var $form = $modal.find('form').eq(0);
            submitForm( $form, submitDoctorFormDone, null, 'GET' );
            $modal.modal('hide');
        });

        // getting technician inf
        $('#new_event_technician_modal').find('button.modal-btn-ok').click(function() {
            var $modal = $(this).closest('.modal');
            var $form = $modal.find('form').eq(0);
            submitForm( $form, submitTechnicianFormDone, null, 'GET' );
            $modal.modal('hide');
        });

        // getting patient inf
        $('#new_event_patient_modal').find('button.modal-btn-ok').click(function() {
            var persona_id = parseInt($('#persona_id').val()) || 0;
            var $frm;
            if (persona_id > 0) {
                $frm = $('#frm_data_edit_patient');
                if ((parseInt($frm.find('input.changed').val()) || 0) == 1) {
                    submitForm( $frm, submitEditPatientFormDone );
                }
                else {
                    var $modal = $(this).closest('.modal');
                    $frm = $modal.find('form').eq(0);
                    submitForm( $frm, submitPatientFormDone, null, 'GET' );
                    $modal.modal('hide');
                }
            }
            else {
                $frm = $('#frm_data_new_patient');
                submitForm( $frm, submitNewPatientFormDone );
            }
        });
        $('#frm_new_event_patient_inf').submit(function(e) {
            var persona_id = parseInt($('#persona_id').val()) || 0;
            if (persona_id > 0) {
                var $frm = $(this);
                submitForm( $frm, submitPatientFormDone, null, 'GET' );
                $frm.closest('.modal').modal('hide');
            }
            e.preventDefault();
            return false;
        });
        $('#frm_data_new_patient').submit(function(e) {
            var persona_id = parseInt($('#persona_id').val()) || 0;
            if (persona_id <= 0) {
                var $frm = $(this);
                submitForm( $frm, submitNewPatientFormDone );
            }
            e.preventDefault();
            return false;
        });
        /*var $frm_data_edit_patient = */$('#frm_data_edit_patient')/*;
        $frm_data_edit_patient*/.find('input,select').change(function() {
            $(this).closest('form').find('input.changed').val('1');
        });
        /*$frm_data_edit_patient.find('select').change(function() {
            $(this).closest('form').find('input.changed').val('1');
        });*/

        // getting service inf
        $('#new_event_service_modal').find('button.modal-btn-ok').click(function() {
            var $modal = $(this).closest('.modal');
            var $form = $modal.find('form').eq(0);
            submitForm( $form, submitServiceFormDone, null, 'GET' );
            $modal.modal('hide');
        });
        $('#frm_new_event_service_inf').submit(function(e) {
            var $form = $(this);
            submitForm( $form, submitServiceFormDone, null, 'GET' );
            $form.closest('.modal').modal('hide');
            e.preventDefault();
            return false;
        });

        // getting office inf
        $('#new_event_office_modal').find('button.modal-btn-ok').click(function() {
            var $modal = $(this).closest('.modal');
            var $form = $modal.find('form').eq(0);
            submitForm( $form, submitOfficeFormDone, null, 'GET' );
            $modal.modal('hide');
        });

        // saving note
        $('#note_modal').find('button.modal-btn-ok').click(function() {
            var $modal = $(this).closest('.modal');
            var $form = $modal.find('form').eq(0);
            submitForm( $form, submitNoteFormDone );
            $modal.modal('hide');
        });

        // saving dni
        $('#patient_dni_modal').find('button.modal-btn-ok').click(function() {
            var $modal = $(this).closest('.modal');
            var $form = $modal.find('form').eq(0);
            submitForm( $form, submitDniFormDone );
        });
        $('#frm_patient_dni').submit(function(e) {
            var $form = $(this);
            submitForm( $form, submitDniFormDone );
            e.preventDefault();
            return false;
        });

        $('#open_patients_modal').click(function() {
            //$('#persona_id').select2('val', '');
            showHideNewPatient();
        });

        $('#open_equipments_modal').click(function() {
            //getAvailableEquipments( $('#servicio_id_hidden').val(), $('#fecha_hidden').val(), $('#hora_inicio_hidden').val() );
        });

        $('#persona_id').on('change', function() {
            showHideNewPatient();
        });

        $('#servicio_id').on('change', function() {
            $('#service_by_category_holder').html('');
        });

        $('#ignore_warning').change(function() {
            $('#ignore_warning_submit').val( $(this).is(':checked') ? '1' : '0' );
        });

        $('#ignore_warning_all').change(function() {
            $('#ignore_warning_all_submit').val( $(this).is(':checked') ? '1' : '0' );
        });

        $('#states').find('button').click(function() {
            var $btn = $(this);
            var state = $btn.attr('attr-state_id');
            if (state == {{ Cita::DONE }} || state == {{ Cita::CANCELLED }}) {
                if (!confirm('{{ Lang::get('citas.confirm_change_state') }}')) {
                    return false;
                }
            }
            if ($btn.attr('attr-confirm_nexts') == '1') {
                var apply_to_nexts = confirm('{{ Lang::get('citas.confirm_apply_to_nexts') }}');
                $('#frm_action').find('input[name=grouped_nexts_apply]').val(apply_to_nexts ? '1' : '0');
            }
            setState(cita_ID, state);
        });

        $('#edit_cita').click(function() {
            var $btn = $(this);
            var $frm = $('#frm_get_data_edit');
            $btn.addClass('disabled');
            $frm.find('input[name=id]').val(cita_ID);
            submitForm( $frm, function($frm, data) {
                var $modal = $('#new_event_form');
                $modal.find('input[name=id]').val( data['cita_id'] );
                submitDateTimeFormDone($frm, data);
                submitDoctorFormDone($frm, data);
                submitTechnicianFormDone($frm, data);
                submitPatientFormDone($frm, data);
                submitServiceFormDone($frm, data, false, false);
                submitEquipmentFormDone($frm, data);
                submitOfficeFormDone($frm, data);
                $modal.modal('show');

                $btn.closest('.modal').modal('hide');
                $btn.removeClass('disabled');

                $frm = $('#new_event_date_time_modal');
                //setting date
                setDatePicker($frm.find('#fecha'), data['fecha']);
                //setting start
                setTimePicker($frm.find('#inicio'), data['inicio']);
                //setting end
                setTimePicker($frm.find('#fin'), data['fin']);
            });

        });

        $('#delete_cita').click(function() {
            if (confirm('¿Está seguro que quiere eliminar la cita?')) {
                var $btn = $(this);
                $btn.addClass('disabled');
                delCita(cita_ID);
                $btn.closest('.modal').modal('hide');
                $btn.removeClass('disabled');
            }
        });

        $('#add_note').click(function() {
            var $btn = $(this);
            var $modal = $('#note_modal');

            $btn.closest('.modal').modal('hide');

            $.ajax({
                type: 'GET',
                url: '{{ URL::route('get_cita_note') }}',
                dataType: 'json',
                data: { 'cita_id' : cita_ID }
            }).done(function(data) {
                if (data['ok'] == 1) {
                    $modal.find('input[name=id]').val(parseInt(data['nota_id']) || 0);
                    $modal.find('textarea[name=contenido]').val( data['nota'] );
                }
            }).fail(function(data) {
                console.log(data); //failed
            });


            $modal.find('input[name=cita_id]').val(cita_ID);
            $modal.modal('show');
        });

        $('a.doctor-letter-index').click(function(e) {
            var letter = $(this).html();

            $.ajax({
                type: 'GET',
                url: '{{ URL::route('get_doctor_by_letter') }}',
                dataType: 'json',
                data: { 'letter' : letter }
            }).done(function(data) {
                if (data['ok'] == 1) {
                    $('#doctors_by_letter_holder').html( data['html'] );
                    bindDoctorByLetter();
                }
            }).fail(function(data) {
                console.log(data); //failed
            });

            e.preventDefault();
            return false;
        });

        $('a.service-category-index').click(function(e) {
            var cat = $(this).attr('attr-id');

            $.ajax({
                type: 'GET',
                url: '{{ URL::route('get_service_by_category') }}',
                dataType: 'json',
                data: { 'category_id':cat, 'date':$('#fecha_hidden').val() }
            }).done(function(data) {
                if (data['ok'] == 1) {
                    $('#service_by_category_holder').html( data['html'] );
                    bindServiceByCategory();
                }
            }).fail(function(data) {
                console.log(data); //failed
            });

            e.preventDefault();
            return false;
        });

        $('a.filter-doctor').click(function(e) {
            var view = $main_calendar.fullCalendar('getView').name;
            var $a = $(this);
            var id = parseInt($a.attr('attr-id')) || 0;

            if (!$a.hasClass('active')) {
                clearForm();
                removeActive();
                $a.addClass('active');
            }
            else {
                removeActive();
            }

            //$a.toggleClass('active').siblings().removeClass('active');

            /*$.ajax({
                type: 'POST',
                url: '{{-- URL::route('set_active_doctor') --}}',
                data: { 'user_id' : $('a.filter-doctor.active').attr('attr-id') || 0 }
            }).done(function() {
                if (view == 'agendaWeek' || view == 'agendaDay') {
                    loadAvailability($a.hasClass('active') ? id : 0);
                }
                $('.fc-event').remove();
                $main_calendar.fullCalendar('refetchEvents');
            });*/

            $a.siblings().removeClass('active');
            highlightActive('doctor');
            e.preventDefault();
            return false;
        });

        $('a.filter-technician').click(function(e) {
            var $a = $(this);
            var id = $a.attr('attr-id');
            $a.toggleClass('active').siblings().removeClass('active');
            highlightActive('technician');
            updateCountAfterFilter();
            e.preventDefault();
            return false;
        });

        $('a.filter-mode').click(function(e) {
            var $a = $(this);
            var id = $a.attr('attr-id');
            $a.toggleClass('active').siblings().removeClass('active');
            highlightActive('mode');
            updateCountAfterFilter();
            e.preventDefault();
            return false;
        });

        $('a.filter-state').click(function(e) {
            var $a = $(this);
            var id = $a.attr('attr-id');
            $a.toggleClass('active').siblings().removeClass('active');
            highlightActive('state');
            updateCountAfterFilter();
            e.preventDefault();
            return false;
        });

        $('a.filter-equipment').click(function(e) {
            var $a = $(this);
            var id = $a.attr('attr-id');
            $a.toggleClass('active').siblings().removeClass('active');
            highlightActive('equipment');
            updateCountAfterFilter();
            e.preventDefault();
            return false;
        });

        $('#service_id_filter').change(function() {
            var $o = $(this);
            var id = parseInt($o.val()) || 0;
            highlightService(id);
            var close = $('#s2id_service_id_filter').find('.select2-search-choice-close');
            if (id > 0) {
                close.show();
            }
            else {
                close.hide();
            }
            updateCountAfterFilter();
        }).val('');

        $('#search_event_btn').click(function(e) {
            var query = $('#search_event_query').val();
            if (query.length > 0) {
                doFind( query );
            }
            e.preventDefault();
            return false;
        });

        $('#search_event_query').keydown(function (e) {
            if (e.keyCode == 13) {
                $('#search_event_btn').click();
            }
        });


        $('.toggle-visibility').click(function(e) {
            var $a = $(this);
            $a.toggleClass('not-visible');
            if ($a.hasClass('not-visible')) {
                $a.find('i').removeClass('fa-eye').addClass('fa-eye-slash');
            }
            else {
                $a.find('i').removeClass('fa-eye-slash').addClass('fa-eye');
            }
            setGroupVisibilities();
            e.preventDefault();
            return false;
        });

        //groups drag & dropping
        $('.group-filter').on('dragstart', function(e) {
            e.originalEvent.dataTransfer.setData('text', $(this).attr('id'));
        });
        $('.filter-accordion').on('dragover', function(e) {
            e.preventDefault();
        }).on('drop', function(e) {
            e.preventDefault();
            var item_id = e.originalEvent.dataTransfer.getData('text');
            var $item = $('#' + item_id);
            var $target = $(e.target).closest('.filter-accordion').eq(0);
            $item.detach().appendTo( $target.find('.list-group').eq(0) );
           $.ajax({
                type: 'POST',
                url: '{{ URL::route('set_doctor_group') }}',
                dataType: 'json',
                data: { 'user_id' : $item.attr('attr-id'), 'group_id' : $target.attr('attr-id') }
            })/*.done(function(data) {
                if (data['ok'] == 1) {

                }
            }).fail(function(data) {
                console.log(data); //failed
            })*/;
        });

        $('#matched_patient').find('a').click(function(e) {
            e.preventDefault();
            if (confirm('{{ Lang::get('pacientes.combine_confirm') }}')) {
                var $a = $(this);
                requestMergePatient($a.attr('data-matched'), $a.attr('data-current'));
            }
        });

        $('.view_history').click(function(e) {
            e.preventDefault();
            loadHistory(cita_ID);
            return false;
        });

        //auto refreshes every 10 minutes
        setInterval(function() {
            var modals_opened = $('.modal.fade.in').length;
            if (!modals_opened) {
                $main_calendar.fullCalendar('refetchEvents');
            }
        }, 600000);

    });
</script>
@stop