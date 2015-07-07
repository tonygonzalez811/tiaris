<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>Clínica | @yield('titulo')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="Alfredo">
    <!-- STYLESHEETS -->
    <!--[if lt IE 9]>
    {{ HTML::script('js/flot/excanvas.min.js') }}
    {{ HTML::script('js/ie-hacks/html5.js') }}
    {{ HTML::script('js/ie-hacks/css3-mediaqueries.js') }}
    <![endif]-->
    {{ HTML::style('css/admin.css') }}

    {{ HTML::style('font-awesome/css/font-awesome.min.css') }}
    <!-- UNIFORM -->
    {{ HTML::style('js/uniform/css/uniform.default.min.css') }}
    <!-- ANIMATE -->
    {{ HTML::style('css/animatecss/animate.min.css') }}
    <!-- FONTS -->
    {{ HTML::style('http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700') }}
    @yield('cabecera')
</head>
<body @yield('atributos_body')>
    @yield('contenido')

    <!-- JAVASCRIPTS -->
    <!-- Se coloca al final del documento para agilizar la carga de la página -->
    <!-- JQUERY -->
    {{ HTML::script('js/jquery/jquery-2.0.3.min.js') }}
    <!-- JQUERY UI-->
    {{ HTML::script('js/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js') }}
    <!-- BOOTSTRAP -->
    {{ HTML::script('bootstrap-dist/js/bootstrap.min.js') }}
    <!-- JQUERY COOKIE -->
    {{ HTML::script('js/jQuery-Cookie/jquery.cookie.min.js') }}


    <!-- UNIFORM -->
    {{ HTML::script('js/uniform/jquery.uniform.min.js') }}
    <!-- CUSTOM SCRIPT -->
    {{ HTML::script('js/script.js') }}
    <script type="text/javascript">
        function swapScreen(id) {
            jQuery('.visible').removeClass('visible animated fadeInUp');
            jQuery('#'+id).addClass('visible animated fadeInUp');
        }
    </script>
    @yield('scripts')
    <!-- /JAVASCRIPTS -->
</body>