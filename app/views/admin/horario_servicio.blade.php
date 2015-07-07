@extends('layouts.admin')

@section('titulo')
Panel de Administración
@stop

@section('cabecera')
{{ HTML::style('js/select2/select2.min.custom.css') }}
{{ HTML::style('js/pickadate/themes/default.css') }}
{{ HTML::style('js/pickadate/themes/default.date.css') }}
{{ HTML::style('js/pickadate/themes/default.time.css') }}
{{ HTML::style('js/fullcalendar/fullcalendar.min.css') }}
<style type="text/css">

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
                    {{ Lang::get('servicio.timetable') }}
                </li>
            </ul>
            <!-- /BREADCRUMBS -->
            <div class="row">
                <div class="col-md-12">
                    <div class="clearfix">
                        <h3 class="content-title pull-left">{{ $servicio->nombre }}</h3>
                    </div>
                    <div class="description">{{ Lang::get('servicio.disponibility') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /PAGE HEADER -->

<!-- MAIN CONTENT -->
<div class="row">
    <div class="col-sm-12">

        <!-- CALENDAR -->
        {{ $frm->panelOpen('calendar', Lang::get('horario.availability_of') . ' ' . $servicio->nombre, 'fa-calendar-o', '', array('collapse')) }}
        <div class="row">
            <div class="col-md-12 calendar-holder">
                <div class='full-calendar' id="horario_calendar"></div>
            </div>
        </div>
        {{ $frm->panelClose() }}

    </div>
</div>

<!-- CREATE EVENT FORM -->
<form id="frm_data" class="form-horizontal hidden" role="form" method="post" autocomplete="off" action="{{ URL::route('admin_horario_editar_post') }}">
    <input type="hidden" name="id" id="horario_id" value="0">
    <input type="hidden" name="inicio" id="inicio">
    <input type="hidden" name="fin" id="fin">
    <input type="hidden" name="servicio_id" id="servicio_id" value="{{ $servicio->id }}">
    {{ Form::token() }}
</form>
<!-- /CREATE EVENT FORM -->


<!-- ACTIONS FORM -->
{{ $frm->modalOpen('actions_modal', Lang::get('citas.actions')) }}
    <div class="btn-toolbar" role="toolbar">
        <!--div id="states" class="btn-group btn-group-lg" role="group">
            <button id="state0" type="button" class="btn btn-default" attr-state_id="0" attr-type="danger">
                <i class="fa fa-4x fa-minus-circle"></i>
                <span>{{ Lang::get('horario.disable') }}</span>
            </button>
        </div-->
        <div class="btn-group btn-group-lg" role="group">
            <!--button id="duplicate_horario" type="button" class="btn btn-default">
                <i class="fa fa-4x fa-copy"></i>
                <span>{{-- Lang::get('horario.duplicate') --}}</span>
            </button-->
            <button id="delete_horario" type="button" class="btn btn-default">
                <i class="fa fa-4x fa-trash"></i>
                <span>{{ Lang::get('horario.delete') }}</span>
            </button>
        </div>
    </div>
    <form id="frm_action" class="form-horizontal hidden" role="form" method="post" autocomplete="off" action="{{ URL::route('horario_actions_post') }}">
        <input type="hidden" name="horario_id" id="horario_id_action" value="0">
        <input type="hidden" name="action" id="action">
        <input type="hidden" name="val" id="val">
        <input type="hidden" name="servicio_id" value="{{ $servicio->id }}">
        {{ Form::token() }}
    </form>
{{ $frm->modalClose(null, null, false) }}
<!-- /ACTIONS FORM -->

<!-- DUPLICATE MODAL -->
{{ $frm->modalOpen('duplicate_modal', Lang::get('horario.duplicate_to')) }}
    <form id="frm_duplicate" class="form-horizontal" role="form" method="post" autocomplete="off" action="{{ URL::route('horario_duplicate_post') }}">
        {{ $frm->date('fecha', null, Lang::get('global.date')) }}
        <input type="hidden" name="horario_id" id="horario_id_duplicate" value="0">
        <input type="hidden" name="servicio_id" value="{{ $servicio->id }}">
        {{ Form::token() }}
    </form>
{{ $frm->modalClose() }}
<!-- /DUPLICATE MODAL -->

<!-- DELETE FORM -->
<form id="frm_delete" class="hidden" role="form" method="post" autocomplete="off" action="{{ URL::route('horario_delete') }}">
    <input type="hidden" name="start" value="">
    <input type="hidden" name="end" value="">
    <input type="hidden" name="all" value="0">
    <input type="hidden" name="servicio_id" value="{{ $servicio->id }}">
    {{ Form::token() }}
</form>
<!-- /DELETE FORM -->

{{ $frm->date('goto_date', null, null, 'day', 'hidden') }}

<!-- /MAIN CONTENT -->
@stop

@section('scripts')
{{ HTML::script('js/select2/select2.js') }}
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
{{ HTML::script('js/panel.js') }}
<script type="text/javascript">
    var url_update_counter = "{{ URL::route('admin_citas_count_get') }}";

    var $main_calendar = $('#horario_calendar');

    var dis_ID;

    function twoDigits(num) {
        return num < 10 ? ('0' + num) : num;
    }

    function dateToString(date) {
        return date.getUTCFullYear() + '-' + twoDigits(date.getUTCMonth()+1) + '-' + twoDigits(date.getUTCDate()) + ' ' + twoDigits(date.getUTCHours()) + ':' + twoDigits(date.getUTCMinutes()) + ':00';
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

    function fn_new_event(start, end, allDay) {
        var $frm = $('#frm_data');
        $frm.find('#horario_id').val('0');
        $frm.find('#inicio').val(dateToString(start._d));
        $frm.find('#fin').val(dateToString(end._d));
        submitForm($frm, function() {
            var $cal = $main_calendar;
            $cal.fullCalendar('refetchEvents');
            $cal.fullCalendar('unselect');
        });
    }

    function fn_drop_event(event) {
        var $frm = $('#frm_data');
        $frm.find('#horario_id').val(event.id);
        $frm.find('#inicio').val(dateToString(event.start._d));
        $frm.find('#fin').val(dateToString(event.end._d));
        submitForm($frm, function() {
            var $cal = $main_calendar;
            $cal.fullCalendar('refetchEvents');
            $cal.fullCalendar('unselect');
        });
    }

    function fn_render_event(event) {
        //console.log('rendered: ' + event.id);
    }

    function fn_render_all_events(view) {
        bindEventClick();
        //highlighting today
        var $today = $('.fc-today');
        if ($today.length) {
            $('.fc-day-header:nth-child(' + ($today.index() + 1) + ')').addClass('today-header');
        }
    }

    function bindEventClick() {
        @if (!$read_only)
        $('a.fc-event').click(function() {
            dis_ID = parseInt($(this).find('input.id').val()) || 0;
            if (dis_ID > 0) {
                getState(dis_ID);
                var $modal = $('#actions_modal');
                $modal.css('visibility', 'hidden');
                $modal.modal('show');
                setTimeout(function() {
                    setActionsModalWidth();
                    $modal.css('visibility', 'visible');
                }, 300);
            }
        });
        @endif
    }

    function showState(state) {
        var $btns = $('#states').find('button');
        $btns.removeClass('active btn-primary btn-danger btn-success').addClass('btn-default');
        var $btn = $('#state' + state);
        if ($btn.length) {
            $btn.removeClass('btn-default').addClass('active btn-' + $btn.attr('attr-type'));
        }
    }

    function applyState(data) {
        if (data['ok']) {
            showState( data['state'] );
            var $events = $('a.fc-event');
            $.each($events, function(i, e) {
                var $e = $(e);
                if ($e.find('input.id').val() == data['horario_id']) {
                    if ( data['state'] == '-1' ) { //deleted
                        $e.remove();
                    }
                    else {
                        $e.removeClass('state0 state1').addClass('state' + data['state']);
                    }
                    return false;
                }
            });
        }
    }

    function setState(disp_id, state) {
        showState(state);
        var $frm = $('#frm_action');
        $frm.find('input[name=horario_id]').val( disp_id );
        $frm.find('input[name=action]').val( 'set_state' );
        $frm.find('input[name=val]').val( state );
        submitForm( $frm, function($frm, data) {
            applyState(data);
        });
    }

    function getState(disp_id) {
        var $frm = $('#frm_action');
        $frm.find('input[name=horario_id]').val( disp_id );
        $frm.find('input[name=action]').val( 'get_state' );
        submitForm( $frm, function($frm, data) {
            if (data['ok']) {
                showState( data['state'] );
            }
        });
    }

    function duplicateWeek() {
        var $frm = $('#frm_duplicate_week');
        var view = $main_calendar.fullCalendar('getView');
        var start = view.start._d;
        var end = view.end._d;

        start = start.getUTCFullYear() + '-' + twoDigits(start.getUTCMonth()+1) + '-' + twoDigits(start.getUTCDate());
        end = end.getUTCFullYear() + '-' + twoDigits(end.getUTCMonth()+1) + '-' + twoDigits(end.getUTCDate());
        $frm.find('input[name=start]').val( start );
        $frm.find('input[name=end]').val( end );
        submitForm( $frm, function($frm, data) {
            if (data['ok'] == 1) {
                alert(data['msg']);
            }
            submitFormDoneDefault($frm, data);
        });
    }

    function deletehorario(disp_id, $btn) {
        var $frm = $('#frm_action');
        $frm.find('input[name=horario_id]').val( disp_id );
        $frm.find('input[name=action]').val( 'delete' );
        submitForm( $frm, function($frm, data) {
            applyState(data);
            $btn.closest('.modal').modal('hide');
            $btn.removeClass('disabled');
        });
    }

    function deletehorarioes(all) {
        all = typeof all == 'undefined' ? false : all;
        var $frm = $('#frm_delete');
        var view = $main_calendar.fullCalendar('getView');
        var start = view.start._d;
        var end = view.end._d;

        start = start.getUTCFullYear() + '-' + twoDigits(start.getUTCMonth()+1) + '-' + twoDigits(start.getUTCDate());
        end = end.getUTCFullYear() + '-' + twoDigits(end.getUTCMonth()+1) + '-' + twoDigits(end.getUTCDate());

        $frm.find('input[name=start]').val( start );
        $frm.find('input[name=end]').val( end );
        $frm.find('input[name=all]').val( all ? 1 : 0 );
        submitForm( $frm, function($frm, data) {
            if (data['ok'] == 1) {
                $main_calendar.fullCalendar('refetchEvents');
            }
        });
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

    $(document).ready(function() {
        App.init('{{ Config::get('app.locale') }}'); //Initialise plugins and elements

        {{ $frm->script() }}

        /* initialize the calendar
        -----------------------------------------------------------------*/
        $main_calendar.fullCalendar({
            'lang': '{{ Config::get('app.locale') }}',
            header: {
                left: '',
                center: '',
                right: ''
            },
            selectable: {{ !$read_only ? 'true' : 'false' }},
            selectHelper: {{ !$read_only ? 'true' : 'false' }},
            selectConstraint: {
                start: '00:00',
                end: '23:59',
                dow: [ {{ $options['days_to_show_str'] }} ]
            },
            eventStartEditable: {{ !$read_only ? 'true' : 'false' }},
            eventDurationEditable: {{ !$read_only ? 'true' : 'false' }},
            selectOverlap: false,
            firstDay: 1,
            weekends: true,
            allDaySlot: false,
            columnFormat: 'dddd',
            defaultView: 'agendaWeek',
            timeFormat: 'h(:mm)t',
            axisFormat: 'h(:mm)t',
            slotDuration: '00:30:00',
            hiddenDays: [{{ $options['days_to_hide_str'] }}],
            businessHours: {
                start: '{{ $options['start_time'] }}',
                end: '{{ $options['end_time'] }}',
                dow: [ {{ $options['days_to_show_str'] }} ]
                // days of week. an array of zero-based day of week integers (0=Sunday)
            },
            minTime: '{{ $options['min_time'] }}',
            maxTime: '{{ $options['max_time'] }}',
            events: '{{ URL::route('horario_calendar_source_editable', array('servicio_id'=>$servicio->id)) }}',
            @if (!$read_only)
            select: function(start, end, allDay) {
                if (typeof fn_new_event == 'function') {
                    fn_new_event(start, end, allDay);
                }
            },
            eventDrop: function( event, delta, revertFunc, jsEvent, ui, view ) {
              if (typeof fn_drop_event == 'function') {
                  fn_drop_event(event);
              }
            },
            eventResize: function( event, delta, revertFunc, jsEvent, ui, view ) {
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
            },
            editable: {{ $read_only ? 'false' : 'true' }},
            droppable: {{ $read_only ? 'false' : 'true' }}
        });
        //----- End calendar Initialization -----

        $('#states').find('button').click(function() {
            var $btn = $(this);
            if (!$btn.hasClass('active')) {
                setState(dis_ID, $btn.attr('attr-state_id'));
            }
            else {
                setState(dis_ID, 1);
            }
        });

        $('#delete_horario').click(function() {
            var $btn = $(this);
            $btn.addClass('disabled');
            if (confirm('{{ Lang::get('horario.confirm_delete') }}')) {
                deletehorario(dis_ID, $btn);
            }
            else {
                $btn.removeClass('disabled');
            }
        });

        $('#duplicate_horario').click(function() {
            var $modal = $('#duplicate_modal');
            $modal.modal('show');
        });

        $('#duplicate_modal').find('button.modal-btn-ok').click(function() {
            var $frm = $('#frm_duplicate');
            $frm.find('input[name=horario_id]').val( dis_ID );
            submitForm( $frm, function($frm, data) {
                if (data['ok'] == 1) {
                    alert(data['msg']);
                }
                submitFormDoneDefault($frm, data);
            });
        });

        $('#delete_week_btn').click(function() {
            if (confirm('{{ Lang::get('horario.confirm_delete_week') }}')) {
                deletehorarioes(false);
            }
        });

        $('#delete_all_btn').click(function() {
            if (confirm('{{ Lang::get('horario.confirm_delete_all') }}')) {
                deletehorarioes(true);
            }
        });

        $('.calendar-btn').mouseenter(function() {
            $(this).addClass('fc-state-hover');
        }).mouseleave(function() {
            $(this).removeClass('fc-state-hover');
        });

        $('#goto_date').pickadate('picker').on('set', function() {
            var $dp = $('#goto_date').pickadate('picker');
            gotoDate( $dp.get('select', 'yyyy-mm-dd') );
        });

    });
</script>
@stop