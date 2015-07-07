@extends('layouts.print')

@section('titulo')
Reporte
@stop

@section('cabecera')
    <style type="text/css">
        h2 {
            font-weight: 400;
        }

        table {
            border: 1px solid #bbb;
            width: 100%;
        }

        th {
            text-align: center;
        }

        tr {
            border: 1px solid #bbb;
        }

        td {
            line-height: 2;
        }

        #toolbar {
            position: fixed;
            top: 10px;
            right: 10px;
        }

        @media print {
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
            <h3>{{ Lang::get('global.site_title') }}</h3>
            <p>{{ Functions::longDateFormat(time(), true, false) }}</p>
            <h2>{{ Lang::get('reportes.title_single') }}</h2>
            @if ($show_date_range)
            <p>{{ $date_range }}</p>
            @endif
            @if ($doctor)
            <p>{{ Lang::get('usuarios.doctor') }}: {{ $doctor }}</p>
            @endif
            @if ($patient)
            <p>{{ Lang::get('usuarios.patient') }}: {{ $patient }}</p>
            @endif
            @if ($service)
            <p>{{ $service }}</p>
            @endif
            @if ($equipment)
            <p>{{ $equipment }}</p>
            @endif
            @if ($state)
            <p>{{ $state }}</p>
            @endif
        </div>
    </div>
    <div class="row">
        <div id="citas" class="col-md-12">
            <p><b>Total: {{ count($records) }}</b></p>
            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        @if (!$state)
                        <th>{{ Lang::get('citas.state') }}</th>
                        @endif
                        @if (!$patient)
                        <th>{{ Lang::get('pacientes.name') }}</th>
                        <th>{{ Lang::get('pacientes.lastname') }}</th>
                        <th>{{ Lang::get('pacientes.dni') }}</th>
                        @endif
                        <th>{{ Lang::get('citas.date') }}</th>
                        <th>{{ Lang::get('citas.time_start') }}</th>
                        @if (!$service)
                        <th>{{ Lang::get('citas.service') }}</th>
                        @endif
                        @if (!$equipment)
                        <th>{{ Lang::get('equipo.title_single') }}</th>
                        @endif
                        @if (!$doctor)
                        <th>{{ Lang::get('usuarios.doctor') }}</th>
                        @endif
                        @if ($show_note)
                        <th>{{ Lang::get('citas.notes') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 0; ?>
                    @foreach ($records as $record)
                    <tr>
                        {{ Cita::getRowValues($count, $record, !$patient, !$service, !$equipment, !$doctor, !$state, $show_note) }}
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
    function doPrint() {
        window.print();
    }

    jQuery(document).ready(function() {
        /*setTimeout(function() {
            doPrint();
        }, 500);*/

        $('.print').click(function() {
            doPrint();
        });
    });
</script>
@stop