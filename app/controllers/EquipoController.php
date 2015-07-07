<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 2/28/2015
 * Time: 12:06 PM
 */

class EquipoController extends BaseController {

    const PAGE_LIMIT = 5;

    const MODEL = 'Equipo';

    const LANG_FILE = 'equipo';

    const TITLE_FIELD = 'nombre';

    /** Navegacion **/

    /**
     * Muestra la página de administración
     * @return mixed
     */
    public function paginaAdmin() {
        if (Auth::user()->admin) {
            $total = $this->getTotalItems();
            $modalidades = Modalidad::getList();
            return View::make('admin.equipos')->with(
                array(
                    'active_menu' => 'equipo',
                    'total' => $total,
                    'modalidades' => $modalidades
                )
            );
        }
        return Redirect::route('inicio');
    }

    /**
     * Muestra la página de inicio del equipo
     * @param $equipo_id
     * @return mixed
     */
    public function paginaAdminInicio($equipo_id) {
        //id = (int)Input::get('equipo_id');
        $id = (int)$equipo_id;
        if ($id) {
            $equipo = Equipo::find($id);
            if ($equipo) {
                $avatar = URL::asset('img/equipments/s/' . (empty($equipo->avatar) ? 'default.jpg' : $equipo->avatar));
                $servicios_equipos_ids = Disposicion::where('equipo_id', '=', $equipo->id)->lists('id');
                if (count($servicios_equipos_ids)) {
                    $total_citas = Cita::whereIn('servicio_equipo_id', $servicios_equipos_ids)->count();
                    $total_citas_today = Cita::forToday()->whereIn('disposicion_id', $servicios_equipos_ids)->count();
                }
                else {
                    $total_citas = 0;
                    $total_citas_today = 0;
                }
                $servicios = $equipo->servicios;
                return View::make('admin.inicio_equipo')->with(
                    array(
                        'active_menu' => 'inicio',
                        'equipo' => $equipo,
                        'servicios' => $servicios,
                        'avatar' => $avatar,
                        'total_citas' => $total_citas,
                        'total_citas_today' => $total_citas_today
                    )
                );
            }
        }
        return Redirect::route('inicio');
    }

    /**
     * This function will be called after the model validation has passed successfully
     * @param $inputs
     * @return boolean
     */
    public function afterValidation($inputs) {
        return true;
    }

    /**
     * Proceso adicional al editar / crear un nuevo registro
     * @param $item
     * @return bool
     */
    public function editarRelational($item) {
        //avatar
        if (Input::hasFile('avatar')) {
            $file = Input::file('avatar');
            if ($file->isValid()) {
                $extension = strtolower($file->getClientOriginalExtension());
                $destination_path = 'img/equipments';
                $filename = uniqid() . '.' . $extension;//$file->getClientOriginalName();
                $esc = 0;
                while (file_exists($destination_path . '/s/' . $filename)) {
                    $filename = uniqid() . '.' . $extension;
                    $esc++;
                    if ($esc >= 30) return true; //just a escape for too many failed attempts to get a unique name (should not happen)
                }
                $moved = $file->move($destination_path, $filename);
                if ($moved) {
                    Functions::smart_resize_image($destination_path . '/' . $filename, null, 256, 256, false, $destination_path . '/s/' . $filename, false);
                    $item->avatar = $filename;
                    $item->save();
                }
            }
        }
        return true;
    }

    /**
     * Datos adicionales que se envian al solicitar la información del registro para editar
     * @param $item
     */
    public function additionalData($item) {
        $this->setReturn('avatar_url', URL::asset('img/equipments/s/' . (empty($item->avatar) ? 'default.jpg' : $item->avatar)));
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

        $servicios = $item->servicios->lists('nombre');

        //left panel
        $output .= $frm->halfPanelOpen(true, 8);
        $output .= $frm->view('nombre', Lang::get(self::LANG_FILE . '.name'), $item->nombre);
        $modalidad = $item->modalidad;
        if ($modalidad) {
            $output .= $frm->view('modalidad', Lang::get('modalidad.title_single'), $modalidad->nombre . (!empty($modalidad->descripcion) ? (' <span class="text-muted">(' . $modalidad->descripcion  . ')</span>') : ''));
        }
        if (!empty($item->descripcion)) {
            $output .= $frm->view('descripcion', Lang::get(self::LANG_FILE . '.description'), $item->descripcion);
        }
        if (!empty($item->modelo)) {
            $output .= $frm->view('modelo', Lang::get(self::LANG_FILE . '.model'), $item->modelo);
        }
        if (!empty($item->serial)) {
            $output .= $frm->view('serial', Lang::get(self::LANG_FILE . '.serial'), $item->serial);
        }
        if (!empty($item->cod_dicom)) {
            $output .= $frm->view('cod_dicom', Lang::get(self::LANG_FILE . '.cod_dicom'), $item->cod_dicom);
        }
        if (!empty($item->host)) {
            $output .= $frm->view('host', Lang::get(self::LANG_FILE . '.host'), $item->host);
        }
        $output .= $frm->view('servicio', Lang::get('servicio.title_' . Functions::singlePlural('single', 'plural', count($servicios))), implode(',<br> ', $servicios));
        $output .= $frm->halfPanelClose();

        //right panel
        $output .= $frm->halfPanelOpen(false, 4);
        if (!empty($item->avatar)) {
            $output .= $frm->image(URL::asset('img/equipments/s/' . $item->avatar));
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
        return AForm::searchResults($records, 'nombre');
    }


    public function equiposByModeGet() {
        $mode_id = (int)Input::get('modalidad_id');

        $equipos = Equipo::conModalidad($mode_id)->get( array('id', 'nombre', 'modelo') );

        $output = '';
        foreach ($equipos as $equipo) {
            $desc = !empty($equipo->modelo) ? (' - ' . $equipo->modelo) : '';
            $output.= "<option value='{$equipo->id}'>{$equipo->nombre}{$desc}</option>";
        }
        $this->setReturn('data', $output);
        return $this->returnJson();
    }

}