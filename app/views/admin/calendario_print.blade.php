@extends('layouts.print')

@section('titulo')
Vista de Impresión
@stop

@section('cabecera')
    <style type="text/css">
        h2 {
            font-weight: 400;
        }

        table {
            border: 1px solid #bbb;
            width: 100%;
            /*border-collapse: separate;
            border-spacing: 12px 0;*/
        }

        th {
            text-align: center;
        }

        th.hour {
            max-width: 90px;
        }

        td.cita {
            border: 1px solid #bbb;
            background-color: #fff;
        }

        td.cita.joined {
            /*border-top: 2px solid #fff;*/
            border-top: 1px dotted #ddd;
            border-bottom: 1px dotted #ddd;
        }

        td.cita.first-joined {
            border-bottom: 1px dotted #ddd;
        }

        td.cita.joined .time {
            /*border-top: 1px dotted #bbb;*/
        }

        td.empty {
            background-color: #eee;
        }

        td.cita p {
            line-height: 1.5;
            font-size: 12px;
            text-align: center;
            margin: 0 10px;
        }

        p.patient {
            font-weight: bold;
            text-transform: uppercase;
        }

        p.updated-at {
            text-align: center;
            font-style: italic;
        }
    </style>
@stop


@section('contenido')
<div class="container">
    <div class="row">
        <div class="col-md-12 text-center">
            <h3>SPA MÉDICO CHILEMEX</h3>
            <p>{{ Functions::longDateFormat(Input::get('day')) }}</p>
            <!--h2>Reporte</h2-->
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table>
                <thead>
                    <tr>
                        <!--th>&nbsp;</th-->
                        @foreach ($doctors as $doctor)
                        <th>{{ $doctor }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- LOOPS TIME INTERVALS --}}
                    @foreach ($times as $time)
                    <tr>
                        <!--th class="hour">{{-- Functions::justTime($time, true, false, true) --}}</th-->
                        {{-- LOOPS DOCTORS --}}
                        @foreach ($doctors as $dr_id => $doctor)
                            <?php $set = false; ?>
                            {{-- LOOPS DOCTORS CITAS --}}
                            @foreach ($citas[$dr_id] as $cita)
                                @if (!$set && Functions::compareHoursInInverval($time, strtotime($cita['start']), 30))
                                <?php
                                    $span[$dr_id] = round($cita['duration'] / 30);
                                    $set = true;
                                    $joined = $cita['joined'];
                                    $will_have_joined = isset($cita['will_have_joined']) && $cita['will_have_joined'];
                                ?>
                                <td class="cita<?php if ($joined) echo ' joined'; else if ($will_have_joined) echo ' first-joined'; ?>" rowspan="{{ $span[$dr_id] }}">
                                    @if (!$joined)
                                    <p class="patient">{{ $cita['patient'] }}</p>
                                    @endif
                                    <p class="time">{{ Functions::justTime($cita['start'], true, true, true) }} — {{ Functions::justTime($cita['end'], true, true, true) }}</p>
                                    @if ($cita['equipment_id'] > 0)
                                    <p class="service">{{ $cita['service'] }} <span class="equipment">({{ $cita['equipment'] }})</span></p>
                                    @else
                                    <p class="service">{{ $cita['service'] }}</p>
                                    @endif
                                    <p class="office">{{ $cita['office'] }}</p>
                                </td>
                                @endif
                            @endforeach
                            @if (!$set)
                                @if (isset($span[$dr_id]) && (int)$span[$dr_id] > 0)
                                    <?php $span[$dr_id]-- ?>
                                    @if ($span[$dr_id] == 0)
                                    <td class="empty">&nbsp;</td>
                                    @endif
                                @else
                                    <td class="empty">&nbsp;</td>
                                @endif
                            @endif
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p class="updated-at">{{ Functions::longDateFormat(time(), true, false) }}</p>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script type="text/javascript">
    function matchHeight() {
        var $hours = $('.hour');
        var $h;
        var max_height = 0;
        //finds the heighest
        $.each($hours, function(i,h) {
            $h = $(h);
            if ($h.height() > max_height) {
                max_height = $h.height();
            }
        });
        //sets to the heighest
        $.each($hours, function(i,h) {
            $h = $(h);
            $h.height( max_height );
        });
    }

    function fixJoinedSeparation() {

    }

    jQuery(document).ready(function() {
        //matchHeight();
        fixJoinedSeparation();
        /*setTimeout(function() {
            window.print();
        }, 500);*/
    });
</script>
@stop