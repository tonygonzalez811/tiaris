<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 2/27/2015
 * Time: 09:54 AM
 */

class ServicioController extends BaseController {

    const PAGE_LIMIT = 5;

    const MODEL = 'Servicio';

    const LANG_FILE = 'servicio';

    const TITLE_FIELD = 'nombre';

    /** Navegacion **/

    /**
     * Muestra la página de administración
     * @return mixed
     */
    public function paginaAdmin() {
        if (Auth::user()->admin) {
            /*$duraciones = array(
                '0' => '',
                '10' => '10m',
                '20' => '20m',
                '30' => '30m',
                '40' => '40m',
                '50' => '50m',
                '60' => '1h',
                '70' => '',
                '80' => '',
                '90' => '1h 30m',
                '100' => '',
                '110' => '',
                '120' => '2h',
                '130' => '',
                '140' => '',
                '150' => '2h 30m',
                '160' => '',
                '170' => '',
                '180' => '3h'
            );*/
            $duraciones = array();
            $with_labels = array(30,60,90,120,150,180);
            for ($i=0; $i<=180; $i+=30) {
                $duraciones[$i] = (in_array($i,$with_labels) ? Functions::minToHours($i) : '');
            }

            $total = $this->getTotalItems();
            $modalidades = Modalidad::getList(true);
            reset($modalidades);
            $equipos = Equipo::getList(key($modalidades));
            $consultorios = Consultorio::lists('nombre', 'id');
            $doctores = $this->getDoctorsArray();
            $tecnicos = $this->getTechniciansArray();
            return View::make('admin.servicios')->with(
                array(
                    'active_menu' => 'servicio',
                    'total' => $total,
                    'duraciones' => $duraciones,
                    'modalidades' => $modalidades,
                    'equipos' => $equipos,
                    'consultorios' => $consultorios,
                    'doctores' => $doctores,
                    'tecnicos' => $tecnicos
                )
            );
        }
        return View::make('admin.inicio');
    }

    public function paginaAdminHorarios() {
        $equipos = Equipo::getList();
        return View::make('admin.servicios')->with(
            array(
                'active_menu' => 'servicio',
                'equipos' => $equipos,
            )
        );
    }

    /**
     * This function will be called after the model validation has passed successfully
     * @param $inputs
     * @return boolean
     */
    public function afterValidation($inputs) {
        $equipos = Input::get('equipos');
        if (isset($inputs['validar_equipo']) && $inputs['validar_equipo'] && !is_array($equipos)) {
            $this->setError(Lang::get('servicio.equipments_required'));
            return false;
        }
        return true;
    }

    /**
     * Proceso adicional al editar / crear un nuevo registro
     * @param $item
     * @return bool
     */
    public function editarRelational($item) {
        if ($item->id) {
            $equipos = Input::get('equipos');
            $doctores = Input::get('doctores');
            $tecnicos = Input::get('tecnicos');
            $consultorios = Input::get('consultorios');

            /*if (!is_array($doctores)) $doctores = explode(',', $doctores);
            if (!is_array($tecnicos)) $tecnicos = explode(',', $tecnicos);*/

            if (!is_array($equipos)) $equipos = array(0=>false);
            if (!is_array($doctores)) $doctores = array(0=>false);
            if (!is_array($tecnicos)) $tecnicos = array(0=>false);
            if (!is_array($consultorios)) $consultorios = array(0=>false);

            //$existing = Disposicion::where('servicio_id', '=', $item->id)->get();

            //deletes existing ones as long as they don't have events attached
            Disposicion::where('servicio_id', '=', $item->id)->has('citas', '=', 0)->delete();


            //creates combinations for dispositions. A LOT OF PROCESSING IS REQUIRED!!!!!!
            $items = array();
            $n = 0;
            foreach ($equipos as $equipo) {
                foreach ($doctores as $doctor) {
                    foreach ($tecnicos as $tecnico) {
                        foreach ($consultorios as $consultorio) {
                            if ($equipo || $doctor || $tecnico || $consultorio) {
                                $data = array();
                                if ($equipo) $data['equipo_id'] = (int)$equipo;
                                if ($doctor) $data['doctor_id'] = (int)$doctor;
                                if ($tecnico) $data['tecnico_id'] = (int)$tecnico;
                                if ($consultorio) $data['consultorio_id'] = (int)$consultorio;
                                if (!Disposicion::findFor($item->id, $data)) {
                                    $data['servicio_id'] = $item->id;
                                    $items[] = $data;
                                    $n++;
                                    if ($n >= 100) { //inserts every 100 items
                                        $n = 0;
                                        Disposicion::insert($items);
                                        $items = array();
                                        sleep(1); //<-- don't want to kill the DB server
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (count($items)) {
                Disposicion::insert($items);
            }


            /*if (is_array($equipos)) {
                $existing = Disposicion::where('servicio_id', '=', $item->id)->get();
                if ($existing) {
                    foreach($existing as $exist) {
                        if (($key = array_search($exist->equipo_id, $equipos)) !== false) {
                            unset($equipos[$key]);
                        }
                        else {
                            $exist->delete();
                        }
                    }
                }

                if (count($equipos)) {
                    $items = array();
                    foreach ($equipos as $equipo) {
                        $items[] = array(
                            'servicio_id' => $item->id,
                            'equipo_id' => $equipo
                        );
                    }
                    Disposicion::insert($items);
                }
            }*/
        }
    }

    /**
     * Datos adicionales que se envian al solicitar la información del registro para editar
     * @param $item
     */
    public function additionalData($item) {
        //equipos
        $this->setReturn('equipos_seleccionados', implode(',', array_keys($this->getEquipmentArray($item, $modalidad_id))));
        $this->setReturn('modalidad_id', $modalidad_id);

        //doctores
        $this->setReturn('doctores', $this->getDoctorsArray($item, false));

        //tecnicos
        $this->setReturn('tecnicos', $this->getTechniciansArray($item));

        //consultorios
        $this->setReturn('consultorios', $this->getOfficesArray($item));
    }

    /**
     * Código HTML que se envía al solicitar la información del registro para visualizar
     * @param $item
     * @return string
     */
    public function outputInf( $item ) {
        $frm = new AForm;
        $output = "";
        $output .= $frm->id( $item->id );
        $output .= $frm->hidden('action');

        //left panel
        $output .= $frm->halfPanelOpen(true);
            $output .= $frm->view('nombre', Lang::get(self::LANG_FILE . '.name'), $item->nombre);
            if (!empty($item->descripcion)) {
                $output .= $frm->view('descripcion', Lang::get(self::LANG_FILE . '.description'), $item->descripcion);
            }
            $output .= $frm->view('duracion', Lang::get(self::LANG_FILE . '.duration'), Functions::minToHours($item->duracion));
            $output .= $frm->view('validar_horario', Lang::get(self::LANG_FILE . '.validate_time'), ucfirst(Lang::get('global.' . ($item->validar_horario ? 'yes' : 'no'))));
            $output .= $frm->view('validar_equipo', Lang::get(self::LANG_FILE . '.validate_equipment'), ucfirst(Lang::get('global.' . ($item->validar_equipo ? 'yes' : 'no'))));
            $output .= $frm->view('validar_doctor', Lang::get(self::LANG_FILE . '.validate_doctor'), ucfirst(Lang::get('global.' . ($item->validar_doctor ? 'yes' : 'no'))));
            $output .= $frm->view('validar_tecnico', Lang::get(self::LANG_FILE . '.validate_technician'), ucfirst(Lang::get('global.' . ($item->validar_tecnico ? 'yes' : 'no'))));
            $output .= $frm->view('validar_consultorio', Lang::get(self::LANG_FILE . '.validate_technician'), ucfirst(Lang::get('global.' . ($item->validar_consultorio ? 'yes' : 'no'))));
        $output .= $frm->halfPanelClose();

        //right panel
        $output .= $frm->halfPanelOpen(false, 6, 'text-center');
            //horario
            if ($item->validar_horario) {
                $items = $this->getTimeArray($item);
                if ($items) $output .= $frm->view('horario', Lang::get('servicio.timetable'), implode('<br>', $items));
                $output .= $frm->view('horario_edit', '', '<a class="btn btn-default" href="' . URL::route('horario_servicio', array('servicio_id'=>$item->id)) . '">' . Lang::get('servicio.edit_timetable') . '</a>');
            }

            //equipos
            if ($item->validar_equipo) {
                $modalidad_id = '';
                $items = $this->getEquipmentArray($item, $modalidad_id);
                if ($items) $output .= $frm->view('equipos', Lang::get('equipo.title_' . (count($items) != 1 ? 'plural' : 'single')), implode(',<br>', $items));
            }

            //doctores
            if ($item->validar_doctor) {
                $items = $this->getDoctorsArray($item, true);
                if ($items) $output .= $frm->view('doctores', Lang::get('usuarios.' . (count($items) != 1 ? 'doctors' : 'doctor')), implode(',<br>', $items));
            }

            //tecnicos
            if ($item->validar_tecnico) {
                $items = $this->getTechniciansArray($item);
                if ($items) $output .= $frm->view('tecnicos', Lang::get('usuarios.' . (count($items) != 1 ? 'technicians' : 'tecnico')), implode(',<br>', $items));
            }

            //consultorios
            if ($item->validar_consultorio) {
                $items = $this->getOfficesArray($item);
                if ($items) $output .= $frm->view('consultorios', Lang::get('consultorio.title_' . (count($items) != 1 ? 'plural' : 'single')), implode(',<br>', $items));
            }
        $output .= $frm->halfPanelClose(true);

        $output .= $frm->controlButtons();
        return $output;
    }

    /**
     * Código HTML que se envía al realizar una búsqueda
     * @param $records
     * @param $search_fields
     * @return string
     */
    public function buscarReturnHtml($records, $search_fields) {
        return AForm::searchResults($records, 'nombre', 'descripcion', null, 'duracion', 'Functions::minToHours');
        /*$output = "";
        if (count($records)) {
            foreach ($records as $record) {
                $row = $record->nombre;
                $equipment = $record->equipos()->lists('nombre', 'id');
                if (is_array($equipment) && count($equipment)) {
                    $row .= ('  -  <i>' . implode(', ', $equipment) . '</i>');
                }
                $id = $record->id;
                $output.= <<<EOT
                    <a class="list-group-item search-result" data-id="{$id}">{$row}
EOT;
                    $output .= '&nbsp;<span class="badge">' . Functions::minToHours($record->duracion) . '</span>';
                    $output .= '<br><span class="text-muted">' . $record->descripcion . '</span></b>';
                $output.= '</a>';
            }
        }
        return $output;*/
    }


    public function listSeekAlt() {
        return $this->listSeek();
       /* $query = Input::get('q');
        $query = explode(' ', $query);

        $search_fields = array('servicio', 'equipo', 'modelo', 'serial', 'cod_dicom', 'host', 'modalidad');

        $records = DB::table('vw_servicio_equipo');

        foreach($query as $q) {
            $q = trim($q);
            $records = $records->where(function ($sql_query) use ($search_fields, $q) {
                $first_query = true;
                foreach($search_fields as $attr) {
                    if ($first_query) {
                        $sql_query->where($attr, 'ILIKE', '%' . $q . '%');
                        $first_query = false;
                    }
                    else {
                        $sql_query->orWhere($attr, 'ILIKE', '%' . $q . '%');
                    }
                }
            });
        }

        $records = $records->get();

        $list = array();
        foreach($records as $record) {
            $list[] = json_encode(array(
                'name' => $record->servicio . ' - ' . $record->equipo . Functions::encloseStr($record->modelo),
                '_id' => $record->servicio_id
            ));
        }

        return '[' . implode(',', $list) . ']';*/
    }

    public function getServices() {
        $id = (int)Input::get('category_id');
        $html = '';
        $items = DB::table('vw_servicio_equipo')->orderBy('servicio_id');
        if ($id > 0) {
            $items = $items->where('modalidad_id', '=', $id);
        }
        $items = $items->get();
        if ($items) {
            $last_service = 0;
            foreach ($items as $item) {
                if ($item->servicio_id == $last_service) continue;
                $nombre = ucwords($item->servicio);
                $duracion = '<span class="badge pull-right">' . Functions::minToHours($item->duracion) . '</span>';
                $time = '';
                if ($item->validar_horario) {
                    $date = Input::get('date');
                    if (!empty($date)) {
                        $date = (int)date('N', strtotime($date));
                        $items = Horario::where('servicio_id', '=', $item->servicio_id)->where('dia', '=', $date)->orderBy('dia')->get();
                        $horarios = array();
                        foreach ($items as $horario) {
                            $horarios[] = $this->htmlTimeLabel(null, $horario->inicio, $horario->fin);
                        }
                        $time = '<br>' . implode('<br>', $horarios);
                    }
                }
                $html.= <<<EOT
                <a class="list-group-item search-result" data-id="{$item->servicio_id}">{$nombre}{$duracion}{$time}</a>
EOT;
                $last_service = $item->servicio_id;
            }
        }
        $this->setReturn('html', $html);
        return $this->returnJson();
    }


    private function getTimeArray($item) {
        $items = $item->horarios()->orderBy('dia')->get();
        $horarios = array();
        if ($items) {
            $last_day = false;
            foreach ($items as $horario) {
                $horarios[$horario->id] = $this->htmlTimeLabel($horario->dia, $horario->inicio, $horario->fin, ($horario->dia == $last_day && $last_day !== false));
                $last_day = $horario->dia;
            }
        }
        return $horarios;
    }

    private function htmlTimeLabel($day, $start, $end, $hidden = false) {
        if ($day !== null) {
            if ($hidden) {
                $output = '<span class="horario-dia-lbl" style="visibility:hidden">' . Horario::dia($day) . ':</span>';
            } else {
                $output = '<span class="horario-dia-lbl margin-top">' . Horario::dia($day) . ':</span>';
            }
            return $output . (' &nbsp; ' . Functions::justTime($start,true,true,true) . ' — ' . Functions::justTime($end,true,true,true));
        }
        return Functions::justTime($start,true,true,true) . ' — ' . Functions::justTime($end,true,true,true);
    }

    private function getEquipmentArray($item, &$modalidad_id) {
        $items = DB::table('vw_servicio_equipo')->where('servicio_id', '=', $item->id)->orderBy('equipo')->get( array('equipo_id', 'equipo', 'modelo', 'modalidad_id') );
        $equipments = array();
        $modalidad_id = '';
        if ($items) {
            foreach ($items as $equipment) {
                $equipments[$equipment->equipo_id] = $equipment->equipo . Functions::encloseStr($equipment->modelo, ' - ', '');
                if ($modalidad_id == '') $modalidad_id = $equipment->modalidad_id;
            }
        }
        return $equipments;
    }

    private function getDoctorsArray($item = null, $include_specialty = false) {
        if ($item !== null) {
            $items = DB::table('vw_servicio_doctor')->orderBy('nombre');
            $items = $items->where('servicio_id', '=', $item->id);
        }
        else {
            $items = DB::table('vw_doctor');
        }
        $items = $items->get(array('id', 'nombre', 'apellido', 'dni', 'especialidad', 'numero'));

        $doctores = array();
        if ($items) {
            foreach ($items as $doctor) {
                $doctores[$doctor->id] = (!empty($doctor->nombre) ? (Functions::firstNameLastName($doctor->nombre, $doctor->apellido) . Functions::encloseStr($doctor->dni)) : $doctor->numero) . ($include_specialty ? (' - ' . $doctor->especialidad) : '');
            }
        }
        return $doctores;
    }

    private function getTechniciansArray($item = null) {
        if ($item !== null) {
            $items = DB::table('vw_servicio_tecnico')->orderBy('nombre');
            $items = $items->where('servicio_id', '=', $item->id);
        }
        else {
            $items = DB::table('vw_tecnico');
        }
        $items = $items->get(array('id', 'nombre', 'apellido', 'dni', 'cod_dicom'));

        $tecnicos = array();
        if ($items) {
            foreach ($items as $tecnico) {
                $tecnicos[$tecnico->id] = (!empty($tecnico->nombre) ? (Functions::firstNameLastName($tecnico->nombre, $tecnico->apellido) . Functions::encloseStr($tecnico->dni)) : $tecnico->cod_dicom);
            }
        }
        return $tecnicos;
    }

    private function getOfficesArray($item) {
        $items = DB::table('vw_servicio_consultorio')->where('servicio_id', '=', $item->id)->orderBy('nombre')->lists('nombre', 'id');
        return $items;
    }

}