<?php
    $active_menu = isset($active_menu) ? $active_menu : '';

    function activeClassIf($menu_name, $active_menu, $create_class = true) {
        if ($active_menu == $menu_name) {
            if ($create_class) {
                return ' class="active"';
            }
            else {
                return ' active';
            }
        }
        return '';
    }

    $user = Auth::user();
    $username = $user->persona;
    if ($username) {
    	$username = Functions::firstNameLastName($username->nombre, $username->apellido);
    }
    else {
    	$username = explode(chr(64), $user->nombre);
    	$username = $username[0] . ' &nbsp; &nbsp; <i class="fa fa-exclamation-triangle"></i> (' . Lang::get('usuarios.hint_my_account') . ')';
    }

    //$equipos = Equipo::orderBy('nombre')->get();
    $modalidades = DB::table('vw_modalidad_cita')->get();//Modalidad::has('equipos')->get();

    if (User::canSeeNotifications($user)) {
        $notifications = ActionLog::latest()->get();
    }
?>
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
    <!-- STYLESHEETS -->
    {{ HTML::style('css/admin.css') }}
    {{ HTML::style('css/themes/night.css') }}<!-- id="skin-switcher" -->
    {{ HTML::style('css/responsive.css') }}
    <!--[if lt IE 9]>
    {{ HTML::script('js/flot/excanvas.min.js') }}
    {{ HTML::script('js/ie-hacks/html5.js') }}
    {{ HTML::script('js/ie-hacks/css3-mediaqueries.js') }}
    <![endif]-->
    {{ HTML::style('font-awesome/css/font-awesome.min.css') }}
    <!-- ANIMATE -->
    {{ HTML::style('css/animatecss/animate.min.css') }}
    @yield('cabecera')
</head>
<body>
<!-- HEADER -->
<header class="navbar clearfix" id="header">
	<div class="container">
		<div class="navbar-brand">
			<!-- COMPANY LOGO -->
			<a href="{{ URL::route('admin_inicio') }}">
			<img src="{{ URL::asset('img/logo/logo.png') }}" alt="" class="img-responsive" height="30" width="120">
			</a>
			<!-- /COMPANY LOGO -->
			<!-- TEAM STATUS FOR MOBILE -->
			<div class="visible-xs">
				<a href="#" class="team-status-toggle switcher btn dropdown-toggle">
				<i class="fa fa-sitemap"></i>
				</a>
			</div>
			<!-- /TEAM STATUS FOR MOBILE -->
            @if (User::showMenu($user))
			<!-- SIDEBAR COLLAPSE -->
			<div id="sidebar-collapse" class="sidebar-collapse btn">
				<i class="fa fa-bars"
					data-icon1="fa fa-bars"
					data-icon2="fa fa-bars" ></i>
			</div>
			<!-- /SIDEBAR COLLAPSE -->
            @endif
		</div>
		<!-- NAVBAR LEFT -->
		<ul class="nav navbar-nav pull-left hidden-xs" id="navbar-left">
			<li class="dropdown">
				<a href="#" class="team-status-toggle dropdown-toggle tip-bottom" data-toggle="tooltip" title="{{ Lang::get('modalidad.view_modes_status') }}">
                    <i class="fa fa-sitemap"></i>
                    <span class="name">{{ Lang::get('modalidad.title_plural') }}</span>
                    <i class="fa fa-angle-down"></i>
				</a>
			</li>
		</ul>
		<!-- /NAVBAR LEFT -->
		<!-- BEGIN TOP NAVIGATION MENU -->
		<ul class="nav navbar-nav pull-right">
		    @if (User::canSeeNotifications($user))
		    <!-- BEGIN NOTIFICATION DROPDOWN -->
            <li class="dropdown" id="header-notification">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-bell"></i>
                    <!--span class="badge">7</span-->
                </a>
                <ul class="dropdown-menu notification">
                    <li class="dropdown-title">
                        <span><i class="fa fa-bell"></i>Notificaciones</span>
                    </li>
                    @foreach ($notifications as $notification)
                        {{ AForm::notificationItem($notification) }}
                    @endforeach
                    <li class="footer">
                        <a href="{{ URL::route('admin_log') }}">Ver más notificaciones <i class="fa fa-arrow-circle-right"></i></a>
                    </li>
                </ul>
            </li>
            <!-- END NOTIFICATION DROPDOWN -->
            @endif
            <!-- BEGIN USER LOGIN DROPDOWN -->
            <li class="dropdown user" id="header-user">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    @if (User::avatar())
                    <img src="{{ URL::asset('img/avatars/s/' . User::avatar()) }}" alt="">
                    @else
					<img src="{{ URL::asset('img/avatars/s/default.jpg') }}" alt="">
                    @endif
                    <span class="username">{{ $username }}</span>
                    <i class="fa fa-angle-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ URL::route('mi_cuenta') }}"><i class="fa fa-cog"></i> {{ Lang::get('usuarios.my_account') }}</a></li>
                    <li><a href="{{ URL::route('cerrar_sesion') }}"><i class="fa fa-power-off"></i> {{ Lang::get('usuarios.close_session') }}</a></li>
                </ul>
            </li>
            <!-- END USER LOGIN DROPDOWN -->
		</ul>
		<!-- END TOP NAVIGATION MENU -->
	</div>
	<!-- TEAM STATUS -->
    <div class="container team-status" id="team-status">
		<div id="scrollbar">
			<div class="handle">
			</div>
		</div>
		<div id="lista_modalidades">
            <ul class="team-list">
                @foreach ($modalidades as $modalidad)
				    {{--
				        AForm::equipmentStatus(
                            $equipo->nombre,
                            $equipo->modelo,
                            URL::asset('img/equipments/s/' . (!empty($equipo->avatar) ? $equipo->avatar : 'default.jpg')),
                            URL::route('inicio_equipo', array('equipo_id' => $equipo->id)),
                            $equipo->id
                        )
                     --}}
                     {{
                        AForm::itemStatus(
                            '<b>' . $modalidad->nombre . '</b><br><small>' . $modalidad->descripcion . '</small>',
                            $modalidad->realizados,
                            $modalidad->pendientes,
                            '#',
                            $modalidad->id
                        )
                    }}
                @endforeach
			</ul>
		</div>
	</div>
	<!-- /TEAM STATUS -->
</header>
<!--/HEADER -->
<!-- PAGE -->
<section id="page">
    @if (User::showMenu($user))
	<!-- SIDEBAR -->
	<div id="sidebar" class="sidebar">
		<div class="sidebar-menu nav-collapse">
			<ul>
				<!-- inicio -->
				<li{{ activeClassIf('inicio', $active_menu) }}>
					<a href="{{ URL::route('admin_inicio') }}">
					    <i class="fa fa-fw fa-home"></i> <span class="menu-text">{{ Lang::get('global.home') }}</span>
					    <span class="selected"></span>
					</a>
				</li>

				@if (User::canAdminCitas($user))
                <li{{ activeClassIf('citas', $active_menu) }}>
                    <a href="{{ URL::route('admin_calendario') }}">
                        <i class="fa fa-fw fa-calendar-o"></i>
                        <span class="menu-text">{{ Lang::get('citas.title_plural') }}</span>
                    </a>
                </li>
                @endif

				@if (User::canAdminEspecialidad($user))
                <!-- especialidades -->
				<li{{ activeClassIf('especialidad', $active_menu) }}>
                    <a href="{{ URL::route('admin_especialidad') }}">
                        <i class="fa fa-fw fa-medkit"></i>
                        <span class="menu-text">{{ Lang::get('especialidad.title_plural') }}</span>
                    </a>
                </li>
                @endif

				<!-- personas -->
				@if (User::canAdminPersonas($user))
				<!--li{{ activeClassIf('personas', $active_menu) }}>
                    <a href="{{ URL::route('admin_pacientes') }}">
                        <i class="fa fa-fw fa-users"></i>
                        <span class="menu-text">{{ Lang::get('pacientes.title_alt_plural') }}</span>
                    </a>
                </li-->
                <li class="has-sub{{ activeClassIf('personas', $active_menu, false) }}">
                    <a href="javascript:;">
                        <i class="fa fa-fw fa-users"></i>
                        <span class="menu-text">{{ Lang::get('pacientes.title_alt_plural') }}</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub">
                        <li><a href="{{ URL::route('admin_pacientes', array('tipo' => 'doctor')) }}"><span class="sub-menu-text">{{ Lang::get('usuarios.doctors') }}</span></a></li>
                        <li><a href="{{ URL::route('admin_pacientes', array('tipo' => 'tecnico')) }}"><span class="sub-menu-text">{{ Lang::get('usuarios.technicians') }}</span></a></li>
                        <li><a href="{{ URL::route('admin_pacientes', array('tipo' => 'paciente')) }}"><span class="sub-menu-text">{{ Lang::get('pacientes.title_plural') }}</span></a></li>
                    </ul>
                </li>
                @endif

				@if (User::canAdminUsuarios($user))
                <!-- usuarios -->
				<li{{ activeClassIf('usuarios', $active_menu) }}>
                    <a href="{{ URL::route('admin_usuarios') }}">
                        <i class="fa fa-fw fa-key"></i>
                        <span class="menu-text">{{ Lang::get('usuarios.title_plural') }}</span>
                    </a>
                </li>
                @endif

				@if (User::canAdminModalidad($user))
                <!-- modalidades -->
				<li{{ activeClassIf('modalidad', $active_menu) }}>
                    <a href="{{ URL::route('admin_modalidad') }}">
                        <i class="fa fa-fw fa-sitemap"></i>
                        <span class="menu-text">{{ Lang::get('modalidad.title_plural') }}</span>
                    </a>
                </li>
                @endif

                @if (User::canAdminEquipos($user))
                <!-- equipos -->
                <li{{ activeClassIf('equipo', $active_menu) }}>
                    <a href="{{ URL::route('admin_equipos') }}">
                        <i class="fa fa-fw fa-plug"></i>
                        <span class="menu-text">{{ Lang::get('equipo.title_plural') }}</span>
                    </a>
                </li>
                @endif

                @if (User::canAdminConsultorios($user))
                <!-- consultorios -->
                <li{{ activeClassIf('consultorio', $active_menu) }}>
                    <a href="{{ URL::route('admin_consultorios') }}">
                        <i class="fa fa-fw fa-cube"></i>
                        <span class="menu-text">{{ Lang::get('consultorio.title_plural') }}</span>
                    </a>
                </li>
                @endif

                @if (User::canAdminServicios($user))
                <!-- servicios -->
                <li{{ activeClassIf('servicio', $active_menu) }}>
                    <a href="{{ URL::route('admin_servicios') }}">
                        <i class="fa fa-fw fa-check-square-o"></i>
                        <span class="menu-text">{{ Lang::get('servicio.title_plural') }}</span>
                    </a>
                </li>
                <!--li class="has-sub{{-- activeClassIf('servicio', $active_menu, false) --}}">
                    <a href="javascript:;">
                        <i class="fa fa-fw fa-check-square-o"></i>
                        <span class="menu-text">{{-- Lang::get('servicio.title_plural') --}}</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub">
                        <li><a href="{{-- URL::route('admin_servicios') --}}"><span class="sub-menu-text">{{-- Lang::get('servicio.title_plural') --}}</span></a></li>
                        <li><a href="{{-- URL::route('admin_horarios') --}}"><span class="sub-menu-text">{{-- Lang::get('servicio.timetables') --}}</span></a></li>
                    </ul>
                </li-->
                @endif

                @if (User::canAdminReportes($user))
                <!-- reportes -->
                <li class="has-sub{{ activeClassIf('reportes', $active_menu, false) }}">
                    <a href="javascript:;">
                        <i class="fa fa-fw fa-file-text-o"></i>
                        <span class="menu-text">{{ Lang::get('reportes.title_plural') }}</span>
                        <span class="arrow"></span>
                    </a>
                    <ul class="sub">
                        <li><a href="{{ URL::route('admin_citas') }}"><span class="sub-menu-text">{{ Lang::get('citas.title_plural') }}</span></a></li>
                        <li><a href="{{ URL::route('admin_reportes_pacientes') }}"><span class="sub-menu-text">{{ Lang::get('pacientes.title_plural') }}</span></a></li>
                    </ul>
                </li>
                @endif

                @if (User::canAdminOpciones($user))
                <!-- opciones -->
                <li{{ activeClassIf('opciones', $active_menu) }}>
                    <a href="{{ URL::route('admin_config') }}">
                        <i class="fa fa-fw fa-cog"></i>
                        <span class="menu-text">{{ Lang::get('global.settings') }}</span>
                    </a>
                </li>
                @endif
			</ul>
			<!-- /SIDEBAR MENU -->
		</div>
	</div>
	<!-- /SIDEBAR -->
    @endif
	<div id="main-content"{{ !User::showMenu($user) ? ' style="margin-left:0 !important"' : '' }}>
		<div class="container">
			<div class="row">
				<div id="content" class="col-lg-12">
					@yield('contenido')
				</div>
				<!--/CONTENT-->
			</div>
		</div>
	</div>
</section>
<!--/PAGE -->

<!-- JAVASCRIPTS -->
<!-- Placed at the end of the document so the pages load faster -->
<!-- JQUERY -->
{{ HTML::script('js/jquery/jquery-2.0.3.min.js') }}
<!-- JQUERY UI-->
{{ HTML::script('js/jquery-ui/js/jquery-ui-1.11.3.custom.js') }}
<!-- BOOTSTRAP -->
{{ HTML::script('bootstrap-dist/js/bootstrap.min.js') }}
<!-- SLIMSCROLL -->
{{ HTML::script('js/jQuery-slimScroll-1.3.0/jquery.slimscroll.min.js') }}
{{ HTML::script('js/jQuery-slimScroll-1.3.0/slimScrollHorizontal.min.js') }}
<!-- BLOCK UI -->
{{ HTML::script('js/jQuery-BlockUI/jquery.blockUI.min.js') }}
<!-- SPARKLINES -->
{{ HTML::script('js/sparklines/jquery.sparkline.min.js') }}
<!-- COOKIE -->
{{ HTML::script('js/jQuery-Cookie/jquery.cookie.min.js') }}
<!-- CUSTOM SCRIPT -->
{{ HTML::script('js/script.js') }}
<script type="text/javascript">

    function updateModalidadStatus() {
        $.ajax({
            type: 'GET',
            url: '{{ URL::route('update_modalidades_status') }}',
            dataType: 'json'
        }).done(function(data) {
            if (data['ok'] == 1) {
                var $users = $('#lista_modalidades').find('ul.team-list').find('li');
                $.each($users, function(i, o) {
                    var $o = $(o);
                    var id = $o.attr('id');
                    if (typeof data[id] != 'undefined') {
                        $o.find('span.badge.badge-green').html( data[id].realizados );
                        $o.find('span.badge.badge-red').html( data[id].pendientes );
                        $o.find('div.progress-bar.progress-bar-success').width( data[id].p_realizado + '%' );
                        $o.find('div.progress-bar.progress-bar-danger').width( data[id].p_pendiente + '%' );
                    }
                });
            }
        }).fail(function(data) {
            console.log(data); //failed
        });
    }

    $(document).ready(function() {
        $('a.team-status-toggle').click(function() {
            if (!$('#team-status').hasClass('open')) {
                updateModalidadStatus();
            }
        });
    });

</script>
@yield('scripts')
<!-- /JAVASCRIPTS -->
</body>
</html>