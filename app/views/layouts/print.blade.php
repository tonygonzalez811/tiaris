<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>Citas | @yield('titulo')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
    <meta name="description" content="Sistema de Cita del Spa Médico Chilemex">
    <meta name="author" content="Alfredo">
    <link href="{{ URL::asset('img/favicon/favicon.ico') }}" rel="shortcut icon"  type="image/x-icon">
    <link href="{{ URL::asset('img/favicon/favicon-48.png') }}" rel="apple-touch-icon" />
    <link href="{{ URL::asset('img/favicon/favicon-120.png') }}" rel="apple-touch-icon" sizes="120x120" />
    <link href="{{ URL::asset('img/favicon/favicon-152.png') }}" rel="apple-touch-icon" sizes="152x152" />
    {{ HTML::style('font-awesome/css/font-awesome.min.css') }}
    {{ HTML::style('css/admin.css') }}
    <style type="text/css">
        body {
            background-color: #fff;
        }
    </style>
    @yield('cabecera')
</head>
<body>
    @yield('contenido')
    
    <!-- JQUERY -->
    {{ HTML::script('js/jquery/jquery-2.0.3.min.js') }}
    @yield('scripts')
</body>
</html>