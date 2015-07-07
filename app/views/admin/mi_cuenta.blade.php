@extends('layouts.admin')

@section('titulo')
Panel de Administración
@stop

@section('cabecera')
{{-- HTML::style('js/dropzone/dropzone.min.css') --}}
{{ HTML::style('js/pickadate/themes/default.css') }}
{{ HTML::style('js/pickadate/themes/default.date.css') }}
{{ HTML::style('js/select2/select2.min.custom.css') }}

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
	if (isset($field_values) && is_array($field_values)) {
		$frm->setValues( $field_values );
		$record_id = $field_values['id'];
	}
	else {
		$record_id = '';
	}
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
                <li>{{ Lang::get('usuarios.my_account') }}</li>
            </ul>
            <!-- /BREADCRUMBS -->
            <!-- HEAD -->
            {{-- $frm->header('Personas', $total, 'fa-wheelchair') --}}
            <!-- /HEAD -->
        </div>
    </div>
</div>
<!-- /PAGE HEADER -->

<!-- MAIN CONTENT -->
<div class="row">
    <div class="col-sm-12">

        <!-- EDIT -->
        {{ $frm->panelOpen('edit', Lang::get('global.modify'), 'fa-pencil', 'orange', array('collapse')) }}
        <form id="frm_data_edit_profile" class="form-horizontal" role="form" enctype="multipart/form-data" method="post" action="{{ URL::route('admin_pacientes_editar_post') }}">
	        <div class="row">
	        	<div class="col-md-8">
		            {{ $frm->id( $record_id ) }}
		            
		            {{ $frm->text('nombre', null, Lang::get('pacientes.name'), "", true) }}
		            {{ $frm->text('apellido', null, Lang::get('pacientes.lastname'), "", true) }}
		            {{-- $frm->text('dni', null, Lang::get('pacientes.dni'), "", true, array('[vejVEJ]{1}-{1}[0-9]{7,9}', 'Ej. V-123456789')); --}}
                    {{ $frm->dni('dni', null, Lang::get('pacientes.dni'), "", true); }}
		            {{ $frm->date('fecha_nacimiento', null, Lang::get('pacientes.birthdate')) }}
		            {{ $frm->select('sexo', null, Lang::get('pacientes.gender'), $genders) }}
		            {{ $frm->select('estado_civil', null, Lang::get('pacientes.marital_status'), $marital_statuses) }}
		            {{ $frm->text('direccion', null, Lang::get('pacientes.address')) }}
		            {{ $frm->tagSelect('telefonos', null, Lang::get('pacientes.phone')) }}
		            {{ $frm->tagSelect('correos', null, Lang::get('pacientes.email')) }}
		            {{ $frm->hidden('telefonos_check', 'telefonos_check', "", $field_values['telefonos']) }}
		            {{ $frm->hidden('correos_check', 'correos_check', "", $field_values['correos']) }}
		            {{ $frm->hidden('my_account', null, '', '1') }}

		            @if (User::is(User::ROL_DOCTOR))
		            <div id="doctor_data">
		                <fieldset>
    		                <legend>{{ Lang::get('usuarios.doctor') }}</legend>
                            {{ $frm->select('especialidad_id', null, Lang::get('usuarios.specialty'), $especialidades) }}
                            {{ $frm->text('numero', null, Lang::get('usuarios.dr_num')) }}
		                </fieldset>
		            </div>
		            @endif
		            @if (User::is(User::ROL_TECHNICIAN))
		            <div id="technician_data">
		                <fieldset>
                            <legend>{{ Lang::get('usuarios.tecnico') }}</legend>
                            {{ $frm->text('cod_dicom', null, Lang::get('usuarios.cod_dicom')) }}
                            </fieldset>
                    </div>
                    @endif

		            {{ Form::token() }}
			    </div>
			    <div class="col-md-4 text-center">
					<div class="profile-avatar-wrap">
						@if (!empty($field_values['avatar']))
						<img src="{{ URL::asset('img/avatars/s/' . $field_values['avatar']) }}" id="profile-avatar" alt="">
						@else
						<img src="{{ URL::asset('img/avatars/s/default.jpg') }}" id="profile-avatar" alt="">
						@endif
					</div>
					<div class="clearfix">
						<input type="hidden" name="MAX_FILE_SIZE" value="500000" />
						<input type="file" id="avatar" name="avatar">
					</div>
				</div>
			</div>
			<div class="row">
	        	<div class="col-md-8">
	        		<br>
					{{ $frm->submit('Guardar', 'btn-warning') }}
				</div>
			</div>
        </form>
        {{ $frm->panelClose() }}


        <!-- CHANGE PASSWORD -->
        {{ $frm->panelOpen('change_pass', Lang::get('usuarios.change_password'), 'fa-key', 'red', array('collapse')) }}
        <form id="frm_data_change_pass" class="form-horizontal" role="form" method="post" action="{{ URL::route('change_password_post') }}">
            {{ $frm->password('password_current', null, Lang::get('usuarios.current_password'), "", true) }}
            {{ $frm->password('password', null, Lang::get('usuarios.password_new'), "", true) }}
            {{ $frm->password('password2', null, Lang::get('usuarios.password_again'), "", true) }}
            {{ Form::token() }}
            <br>
            {{ $frm->submit('Guardar', 'btn-danger') }}
        </form>
        {{ $frm->panelClose() }}

    </div>
</div>

<!-- /MAIN CONTENT -->
@stop

@section('scripts')
{{-- HTML::script('js/dropzone/dropzone.min.js') --}}
{{ HTML::script('js/avatar/resample.js') }}
{{ HTML::script('js/avatar/avatar.js') }}
{{ HTML::script('js/select2/select2.js') }}
{{ HTML::script('js/pickadate/picker.js') }}
{{ HTML::script('js/pickadate/picker.date.js') }}
<?php if (Config::get('app.locale') != 'en') : ?>
{{ HTML::script('js/select2/select2_locale_' . Config::get('app.locale') . '.js') }}
{{ HTML::script('js/pickadate/translations/' . Config::get('app.locale') . '.js') }}
<?php endif; ?>
{{ HTML::script('js/bootstrap-inputmask/bootstrap-inputmask.min.js') }}
{{ HTML::script('js/panel.js') }}
<script>
    //var url_update_counter = "{{ URL::route('admin_pacientes_count_get') }}";

    function afterUpdatingRecords() {

    }

    function beforePanelCreate() {

    }

    function save() {
        Panel.edit.status.saving();
        var $frm = $('#frm_data_edit_profile');
        var url = $frm.attr('action');

        var form_data = new FormData($frm[0]);

        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            data: form_data, // serialized form elements with multipart enctype allowed.
            processData: false,
            contentType: false
        }).done(function(data) {
            if (data['ok']) {
                if (typeof data['created_id'] != 'undefined') {
                    $frm.find('input[name=id]').val( data['created_id'] );
                }
                Panel.edit.status.saved();
            }
            else {
                Panel.edit.status.error(data['err']);
            }
        }).fail(function(data) {
            console.log(data); //failed
            Panel.edit.status.error(data['responseText'].substr(0, 200));
        });
    }

    function changePassword() {
        var $panel = $('#change_pass_panel');
        Panel.status.saving( $panel );
        var $frm = $('#frm_data_change_pass');
        var url = $frm.attr('action');

        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            data: $frm.serialize()
        }).done(function(data) {
            if (data['ok']) {
                Panel.status.saved($panel, data['msg']);
                Panel.resetForm($frm);
            }
            else {
                Panel.status.error($panel, data['err']);
            }
        }).fail(function(data) {
            console.log(data); //failed
            Panel.status.error($panel, data['responseText'].substr(0, 200));
        });
    }

    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

		$('#frm_data_edit_profile').submit(function(e) {
            setTimeout(save(), 100);
            e.preventDefault();
            return false;
        });

		$('#frm_data_change_pass').submit(function(e) {
            setTimeout(changePassword(), 100);
            e.preventDefault();
            return false;
        });

        {{ $frm->script() }}

    });
</script>
@stop