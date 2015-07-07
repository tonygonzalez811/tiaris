<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 26/04/15
 * Time: 06:00 PM
 */

class UserGrupoController extends BaseController {

    const PAGE_LIMIT = 5;

    const MODEL = 'UserGrupo';

    const LANG_FILE = 'usuarios';

    const TITLE_FIELD = 'nombre';

    /** Navegacion **/

    /**
     * Muestra la página de administración de Parentescos
     * @return mixed
     */
    public function paginaAdmin() {
        if (Auth::user()->admin) {
            $total = $this->getTotalItems();
            return View::make('admin.user_grupo')->with(
                array(
                    'active_menu' => 'usuarios',
                    'total' => $total
                )
            );
        }
        return View::make('admin.inicio');
    }

    /**
     * Proceso adicional al editar / crear un nuevo registro
     * @param $item
     * @return bool
     */
    public function editarRelational($item) {

        return true; //needs to return true to output json
    }

    /**
     * Datos adicionales que se envian al solicitar la información del registro para editar
     * @param $item
     */
    public function additionalData($item) {

    }

    /**
     * Código HTML que se envía al solicitar la información del registro para visualizar
     * @param $item
     * @return string
     */
    public function outputInf( $item ) {
        $frm = new AForm;
        $total = $item->usuarios->count();
        $output = "";
        $output .= $frm->id( $item->id );
        $output .= $frm->hidden('action');
        $output .= $frm->view('nombre', Lang::get(self::LANG_FILE . '.group'), $item->nombre);
        $output .= $frm->view('total', Lang::get(self::LANG_FILE . '.total_users'), $total);
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
        return AForm::searchResults($records, reset($search_fields));
    }


    public function setGroup() {
        $validator = Validator::make(Input::all(),
            array(
                'user_id'  => 'required|integer|min:1',
                //'group_id' => 'required'
            )
        );
        if ($validator->passes()) {
            $user_id = (int)Input::get('user_id');
            $group_id = (int)Input::get('group_id', 0);

            $user = User::find($user_id);
            if ($user) {
                $user->grupo_id = $group_id ? $group_id : null;
                $user->save();
                return $this->returnJson();
            }
        }
        return $this->setError(Lang::get('global.wrong_action'));
    }

}