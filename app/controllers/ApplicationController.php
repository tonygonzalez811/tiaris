<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 6/24/2015
 * Time: 12:06 PM
 */

class ApplicationController extends Controller {

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function instalar() {
        Application::instalar();

        return Redirect::route('inicio_sesion')->with(array(
            'msg' => 'Instalación completada.'
        ));
    }

    /**
     * Muestra la página por defecto
     * @return mixed
     */
    public static function mostrarDefault() {
        if (Auth::check()) {
            return UserController::paginaAdminInicio();
        }
        return self::mostrarInicioSesion();
    }

    /**
     * Muestra el formulario de inicio de sesión
     * @return mixed
     */
    public static function mostrarInicioSesion() {
        return View::make('formulario_inicio');
    }

    /**
     * Cron Job script para actualizar la data de tiaris
     */
    public function verificarTiaris() {
        $total = $this->subirTablaTiaris();
        echo Functions::singlePlural('Se subió', 'Se subieron', $total) . ' ' . Functions::singlePlural('cita', 'citas', $total, true);
    }

    public static function subirTablaTiaris() {
        $n = 0;
        $citas = Cita::notSent()->toNow()->get();
        if ($citas) {
            foreach ($citas as $cita) {
                $disposicion = $cita->disposicion;

                //solo envia citas con equipos (modalidades)
                $equipo = $disposicion->equipo;
                if (!$equipo || $equipo->id <= 0) continue;

                //datos del paciente
                $paciente = $cita->persona;

                //datos del doctor
                $doctor = $disposicion->doctor;
                $doctor = $doctor ? $doctor->persona : false;

                //datos del servicio
                $servicio = $disposicion->servicio;

                //datos de la modalidad
                $modalidad = $equipo->modalidad;

                //datos que se registraran en la tabla de "listadetrabajo"
                $data = array(
                    'codigo' => '', //¿? Es el código único para un estudio específico de la orden. Es generado por el sistema. Puede ser alfanumérico.
                    'fechahora' => date('Y-m-d H:i:s', strtotime($cita->inicio)),
                    'idadmision' => '', //¿? Este es el código que debe ser generado por cada Orden o Admisión (único por cada vez que el paciente accesa al servicio de radiología)
                    'cedula' => $paciente->dni,
                    'apellidos' => self::format($paciente->apellido),
                    'nombres' => self::format($paciente->nombre),
                    'sexo' => $paciente->sexo ? 'M' : 'F',
                    'nombreestudio' => self::format($servicio->nombre),
                    'modalidad' => self::format($modalidad->nombre),
                    'apedoctor' => $doctor ? self::format($doctor->apellido) : '',
                    'nomdoctor' => $doctor ? self::format($doctor->nombre) : '',
                    'origen' => '', //¿? Nombre del servicio desde el cual se solicita la orden. Ej: AMBULATORIO, EMERGENCIA, HOSPITALIZACION, UCI, UTIN, CONSULTA
                    'procesado' => 0
                );
                if (!empty($paciente->fecha_nacimiento)) {
                    $data['fechanacimiento'] = date('Y-m-d', strtotime($paciente->fecha_nacimiento));
                }

                //registra en la base de datos
                $creado = Tiaris::create($data);

                //actualiza la cita con el estado y id creado en la tabla "listadetrabajo"
                if ($creado) {
                    $cita->enviado = 'true';
                    $cita->listadetrabajo_id = $creado->id;
                    $cita->save();
                }

                $n++;

                sleep(5); //<-- to not overload the server
            }
        }
        return $n;
    }


    private static function format($val) {
        return strtoupper(self::removeAccents($val)); //éjémpló --> EJEMPLO
    }

    private static function removeAccents($string) {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
        );

        $string = strtr($string, $chars);

        return $string;
    }
}