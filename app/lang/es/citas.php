<?php
return array(
    'title_single' => 'Cita',
    'title_plural' => 'Citas',
	'calendar' => 'Calendario',
    'date' => 'Fecha',
    'time_start' => 'Hora inicio',
    'time_end' => 'Hora fin',
    'state' => 'Estado',
    'doctor' => 'Doctor',
    'patient' => 'Paciente',
    'service' => 'Servicio',
    'office' => 'Cabina',
    'record_date' => 'Fecha de registro',
    'record_date_alt' => 'Registrado el',
    'goto' => 'Ir a...',
    'add_new' => 'Agregar',
    'print_view' => 'Vista de impresión',
    'for_today_single' => 'Cita para hoy',
    'for_today_plural' => 'Citas para hoy',
    'cita_details' => 'Detalles de la Cita',
    'search_by_dni' => 'C.I. o nombre',
    'from' => 'Desde',
    'to' => 'Hasta',
    'generate_report' => 'Generar Reporte',

    //states
    'realizada' => 'Realizada',
    'por_confirmar' => 'Por confirmar',
    'confirmada' => 'Confirmada',
    'cancelada' => 'Cancelada',

    'unconfirmed' => 'Por confirmar',
    'confirmed' => 'Confirmada',
    'cancelled' => 'Cancelada',
    'done' => 'Realizada',

    'done_citas' => 'Citas realizadas',

    'add_note' => 'Notas',
    'edit' => 'Editar',
    'notes' => 'Notas',

    'new_event' => 'Nueva Cita',
    'new_patient' => 'Nuevo Registro',
    'set_date_time' => 'Establecer fecha y hora',
    'set' => 'Asignar',
    'actions' => 'Acciones',
    'in' => 'En',
    'available' => 'Disponible',
    'not_available' => 'No disponible',
    'type' => 'Tipo',

    'select_doctor' => '(Doctor)',
    'select_technician' => '(Técnico)',
    'select_patient' => '(Paciente)',
    'select_service' => '(Servicio)',
    'select_equipment' => '(Equipo)',

    'no_time' => 'Sin hora definida',
    'passed_time' => 'Fecha pasada',

    'no_group' => '(Sin grupo)',

    'view_history' => 'ver historial',

    'ignore_warning' => 'Ignorar esta advertencia',
    'ignore_all_warnings' => 'Ignorar todas las advertencias',

    'confirm_change_state' => '¿Está seguro que quiere cambiar el estado de la cita?',
    'patient_has_no_dni' => 'El paciente no tiene una cédula registrada',
    'to_continue_insert_patient_dni' => 'Para continuar debe registrar la cédula de identidad del paciente',
    'confirm_apply_to_nexts' => '¿Quiere aplicar el estado también a las citas próximas del paciente?',
    'not_yet_started' => 'Esta acción debe realizarse posterior a la fecha y hora de la cita',

    //errors
    'time_mismatch' => 'El intervalo de tiempo elegido es inválido',
    'overlap_doctor' => 'El doctor ya tiene una cita registrada durante el intervalo seleccionado',
    'overlap_technician' => 'El técnico ya tiene una cita registrada durante el intervalo seleccionado',
    'unavailable_doctor' => 'El doctor no está disponible para la hora y fecha seleccionada',
    'unavailable_service' => 'El servicio no está disponible para la hora y fecha seleccionada',
    'overlap_equipment' => 'No hay un equipo disponible para realizar el servicio seleccionado',
    'overlap_patient' => 'El paciente ya tiene una cita registrada en la hora seleccionada',
    'drag_collapse' => 'La cita no se puede mover a la fecha y hora especificada',
);