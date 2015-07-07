<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 30/06/2015
 * Time: 06:36 PM
 */

class HorarioController extends BaseController {

    const MODEL = 'Horario';

    /** Navegacion **/

    /**
     * Muestra la página de inicio del equipo
     * @param $servicio_id
     * @return mixed
     */
    public function paginaAdminServicio($servicio_id) {
            $servicio_id = (int)$servicio_id;

            if ($servicio_id > 0) {
                $servicio = Servicio::find($servicio_id);
                if ($servicio) {
                    $options = Opcion::load();
                    return View::make('admin.horario_servicio')->with(
                        array(
                            'active_menu' => 'inicio',
                            'servicio' => $servicio,
                            'read_only' => false,
                            'options' => $options
                        )
                    );
                }
            }

        return ApplicationController::mostrarDefault();
    }


    public function editarPost() {
        $saved = false;
        $id = (int)Input::get('id');
        $inicio = Input::get('inicio');
        $fin = Input::get('fin');
        $servicio_id = (int)Input::get('servicio_id');
        if ($servicio_id > 0) {
            $inicio = strtotime($inicio);
            $fin = strtotime($fin);

            if ($inicio !== false && $fin !== false) {
                $hora_inicio = Functions::justTime($inicio, false, false);
                $hora_fin = Functions::justTime($fin, false, false);
                $dia = date('N', $inicio); //1 (for Monday) through 7 (for Sunday)

                $collapses = Horario::collapseUp($inicio, $fin, $servicio_id)->first();
                if ($collapses && $collapses->id > 0) {
                    $hora_inicio = $collapses->inicio;
                    $collapses->delete();
                }
                else {
                    $collapses = Horario::collapseDown($inicio, $fin, $servicio_id)->first();
                    if ($collapses && $collapses->id > 0) {
                        $hora_fin = $collapses->fin;
                        $collapses->delete();
                    }
                }

                if ($id > 0) { //existing one
                    $horario = Horario::find($id);
                    if ($horario) {
                        $horario->dia = $dia;
                        $horario->inicio = $hora_inicio;
                        $horario->fin = $hora_fin;
                        $horario->save();
                        $saved = true;
                    }
                }
                else { //new one
                    $data = array(
                        'dia' => $dia,
                        'inicio' => $hora_inicio,
                        'fin' => $hora_fin,
                        'servicio_id' => $servicio_id
                    );
                    Horario::create($data);
                    $saved = true;
                }

                //delete complete overlapped ones
                //Horario::inDateTime($hora_inicio, $hora_fin, $servicio_id)->delete();

            }

        }
        if ($saved) {
            return $this->setSuccess( Lang::get('global.saved_msg') );
        }
        return $this->setError( Lang::get('global.unable_perform_action') );
    }


    public function getHorario($servicio_id = 0, $editable = false) {
        if ($servicio_id == 0) {
            $servicio_id = (int)Input::get('servicio_id');
            if ($servicio_id == 0) return '[]';
        }

        $inicio = strtotime( Input::get('start') );
        //$fin = strtotime( Input::get('end') );

        $items_json = array();
        $servicio = Servicio::find($servicio_id);
        if ($servicio) {
            //gets all items for the corresponding service
            $items = $servicio->horarios;
            foreach ($items as $item) {
                if ($item->dia > 1) {
                    $item_inicio = date('Y-m-d', strtotime('+' . ($item->dia - 1) . ' days', $inicio));
                }
                else {
                    $item_inicio = date('Y-m-d', $inicio);
                }
                $item_fin = $item_inicio . ' ' . $item->fin;
                $item_inicio = $item_inicio . ' ' . $item->inicio;

                if ($editable) {
                    $items_json[] = <<<EOT
                        {
                            "id": "{$item->id}",
                            "start": "{$item_inicio}",
                            "end": "{$item_fin}"
                        }
EOT;
                }
            }
        }

        return '[' . implode(',', $items_json) . ']';
    }


    public function getHorarioEditable($servicio_id = 0) {
        return $this->getHorario($servicio_id, true);
    }


    public function calendarActionPost() {
        $horario_id = (int)Input::get('horario_id');
        $action = Input::get('action');
        //$val = Input::get('val');

        if ($horario_id > 0) {
            $item = Horario::find($horario_id);
            switch ($action) {
                case 'set_state':
                    /*$val = (int)$val;
                    if ($val < 0 || $val > 1) $val = 0;
                    $item->disponible = $val;
                    $item->save();
                    $this->setReturn('disponibilidad_id', $horario_id);
                    $this->setReturn('state', $item->disponible);
                    $to_log = serialize($item->toArray());
                    ActionLog::log(self::MODEL . ' changed_state', $to_log, $item->id);*/
                    break;
                case 'get_state':
                    $this->setReturn('horario_id', $horario_id);
                    $this->setReturn('state', '1');
                    break;
                case 'delete':
                    $to_log = serialize($item->toArray());
                    $item->delete();
                    $this->setReturn('horario_id', $horario_id);
                    $this->setReturn('state', '-1');
                    ActionLog::log(self::MODEL . ' deleted', $to_log, $horario_id);
            }
        }
        return $this->returnJson();
    }

}