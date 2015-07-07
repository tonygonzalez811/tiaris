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
            /*border: 1px solid #bbb;*/
            width: 100%;
            /*border-collapse: separate;
            border-spacing: 12px 0;*/
        }

        tr {
            width: 100%;
        }

        th {
            text-align: center;
        }

        .cita {
            border: 1px solid #bbb;
            background-color: #fff;
        }

        .cita.joined {
            /*border-top: 2px solid #fff;*/
            border-top: 1px dotted #ddd;
            border-bottom: 1px dotted #ddd;
        }

        .cita.first-joined {
            border-bottom: 1px dotted #ddd;
        }

        .cita.joined:last-child {
            border-bottom: 1px solid #bbb;
        }

        /*.cita:not(.continuous):not(:first-child) {
            margin-top: 20px;
        }*/
        .spacer:not(:first-child) {
            border-top: 1px solid #bbb;
            height: 20px;
        }

        td {
            background-color: #eee;
            vertical-align: top;
            width: 12.5%;
        }

        .cita p {
            line-height: 1.5;
            font-size: 11px;
            text-align: center;
            margin: 0 10px;
        }

        p .time-end {
            font-size: smaller;
        }

        .doctor {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            margin-bottom: -20px;
        }

        p.patient {
            font-weight: bold;
            text-transform: uppercase;
        }

        p.service {
            text-decoration: underline;
        }

        p.note {
            font-style: italic;
            font-size: smaller;
        }

        p.updated-at {
            text-align: right;
            font-style: italic;
            font-size: 12px;
            color: #bbb;
        }

        #toolbar {
            position: fixed;
            top: 10px;
            right: 10px;
        }

        .pagebreak {
            margin-top: 50px;
        }
        @media print {
            .pagebreak {
                margin-top: 0;
            }

            .cita.joined {
                border-top: none;
                border-bottom: none;
            }

            .cita.first-joined {
                border-bottom: none;
            }

            .cita.joined .time {
                border-top: 1px solid #bbb;
            }

            h3 {
                margin-top: 0;
                padding-top: 0;
            }

            .doctor {
                font-size: 12px;
            }

            #toolbar {
                display: none;
            }
        }
    </style>
@stop


@section('contenido')
<div class="container">
    <div class="row">
        <div class="col-md-12 text-center">
            <h3>SPA MÉDICO CHILEMEX</h3>
            <p><b>{{ Functions::longDateFormat(Input::get('day')) }}</b></p>
            <!--h2>Reporte</h2-->
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table>
                <!--thead>
                    <tr>
                        @foreach ($doctors as $doctor)
                        <th class="doctor">{{ $doctor }}</th>
                        @endforeach
                    </tr>
                </thead-->
                <tbody>
                    <tr>
                        <?php $rows = 0; ?>
                        {{-- LOOPS DOCTORS --}}
                        @foreach ($doctors as $dr_id => $doctor)
                            <?php $rows++; ?>
                            <td>
                                <div class="doctor">{{ $doctor }}</div>
                                @foreach ($citas[$dr_id] as $cita)
                                @if (!$cita['continuous'])
                                <div class="spacer"></div>
                                @endif
                                <div class="cita{{ $cita['joined'] ? ' joined' : ($cita['will_have_joined'] ? ' first-joined' : '') }}{{ $cita['continuous'] ? ' continuous' : '' }}">
                                    @if (!$cita['joined'])
                                    <p class="patient">{{ $cita['patient'] }}</p>
                                    @endif
                                    <p class="time">{{ Functions::justTime($cita['start'], true, true, true) }} — <span class="time-end">{{ Functions::justTime($cita['end'], true, true, true) }}</span></p>
                                    @if ($cita['equipment_id'] > 0)
                                    <p class="service">{{ $cita['service'] }} <span class="equipment">({{ $cita['equipment'] }})</span></p>
                                    @else
                                    <p class="service">{{ $cita['service'] }}</p>
                                    @endif
                                    <p class="office">{{ $cita['office'] }}</p>
                                    @if (!empty($cita['note']))
                                    <p class="note">*{{ $cita['note'] }}</p>
                                    @endif
                                </div>
                                @endforeach
                            </td>
                            @if ($rows == $n_rows && $rows < $total_rows)
                                <?php $rows = 0; ?>
                                </tr></tbody></table>
                                <table class="pagebreak">
                                    <tbody>
                                        <tr>
                            @endif
                        @endforeach
                        @if ($rows < 8)
                            @for ($i = $rows; $i < $n_rows; $i++)
                            <td>&nbsp;</td>
                            @endfor
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p class="updated-at">Actualizado al {{ Functions::longDateFormat(time(), true, false) }}</p>
        </div>
    </div>
    <div id="toolbar" class="btn-group btn-group-lg" role="group">
        <button type="button" class="btn btn-default print" title="Imprimir">
            <i class="fa fa-print"></i>
        </button>
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

    function doPrint() {
        window.print();
    }

    jQuery(document).ready(function() {
        //matchHeight();
        /*setTimeout(function() {
            doPrint();
        }, 500);*/

        $('.print').click(function() {
            doPrint();
        });
    });
</script>
@stop