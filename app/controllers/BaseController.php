<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

    //Additional by me:
    protected $output = array();

    /**
     * Destinos de las rutas
     */
    public function buscarGet() {
        $validator = Validator::make(Input::all(),
            array(
                'search_query' => 'required',
                'search_page'  => 'required|integer|min:1'
            )
        );
        if ($validator->passes()) {
            $query  = Input::get('search_query');
            $page   = Input::get('search_page');
            $search_fields = array();
            $match_total = 0;

            if ($query != '*') {
                $records = $this->buscar( $query, $page, $search_fields, $match_total );
                $total = count($records);
            }
            else {
                //gets all records
                $model = static::MODEL;
                $records = $model::get();

                $m = new $model;
                $search_fields = $m->getSearchable();
                $match_total = count($records);
                $total = $match_total;
            }

            $this->setReturn('total', $match_total);
            $this->setReturn('total_page', $total);
            $this->setReturn('results', $this->buscarReturnHtml($records, $search_fields));
            return $this->returnJson();
        }
        return $this->setError( Lang::get('global.wrong_action') );
    }

    /**
     * Outputs Json with HTML for viewing
     * @return string
     */
    public function infoGet() {
        $validator = Validator::make(Input::all(),
            array(
                'id' => 'required|integer|min:1'
            )
        );

        if ($validator->passes()) {
            $id = Input::get('id');
            $item = $this->fetchData( $id );
            $title_field = static::TITLE_FIELD;

            $this->setReturn('title', Lang::get('global.inf_for') . '"' . $item->$title_field . '"');
            $this->setReturn('results', $this->outputInf( $item ));
        }
        else {
            return $this->setError(Lang::get('global.not_found'));
        }

        return $this->returnJson();
    }

    /**
     * Outputs Json for editing
     * @return string
     */
    public function datosGet() {
        $validator = Validator::make(Input::all(),
            array(
                'id' => 'required|integer|min:1'
            )
        );

        if ($validator->passes()) {
            $id = Input::get('id');
            $item = $this->fetchData( $id );
            $title_field = static::TITLE_FIELD;

            $this->setReturn('title', Lang::get('global.edit_for') . '"' . $item->$title_field . '"');
            $this->addToOutput( $item->toArray() );
            $this->additionalData( $item );
        }
        else {
            return $this->setError(Lang::get('global.not_found'));
        }

        return $this->returnJson();
    }

    /**
     * Process POST data from action form and outputs Json
     * @return string
     */
    public function accionPost() {
        $validator = Validator::make(Input::all(),
            array(
                'id' => 'required|integer|min:1'/*,
                'action'  => 'in:action_edit,action_delete'*/
            )
        );
        if ($validator->passes()) {
            $id = Input::get('id');
            $action = Input::get('action');
            switch ($action) {
                case 'action_delete':
                    $this->delete( $id );
                    $this->setReturn('deleted', 1);
                    $this->setReturn('record', $id);
                    return $this->setSuccess(Lang::get('global.del_msg'));
                    break;

                default:
                    $this->setError(Lang::get('global.wrong_action'));
            }
        }
        else {
            return $this->setError(Lang::get('global.not_found'));
        }

        return $this->returnJson();
    }

    /**
     * Process POST data from editar form and outputs Json
     * @return string
     */
    public function editarPost() {
        $id = (int)Input::get('id');
        if ($id > 0) {
            $item = $this->editar(static::MODEL);
            if ($item != false) {
                $this->editarRelational($item);
            }
            return $this->returnJson();
        }
        return $this->registrarPost();
    }

    /**
     * Process POST data from create form and outputs Json
     * @return string
     */
    public function registrarPost() {
        $item = $this->registrar(static::MODEL);
        if ($item !== false) {
            $this->editarRelational($item);
        }
        return $this->returnJson();
    }

    /**
     * Returns Json with total records for the model
     * @return string
     */
    public function totalGet() {
        $this->setReturn('total', $this->getTotalItems());
        return $this->returnJson();
    }

    /**
     * Counts the number of records for the model
     * @return mixed
     */
    public function getTotalItems() {
        $model = static::MODEL;
        return $model::count();
    }

    /**
     * Gets the data from an specific id for the current model
     * @param $id
     * @return mixed
     */
    public function fetchData($id) {
        $model = static::MODEL;
        $item = $model::findOrFail($id);

        //$this->addToOutput( $item->toArray() );

        if ($item) {
            return $item;//$this->outputInf( $item );
        }

        return false;
    }

    /**
     * Determins if the input fields are valid for the model's fillable fields
     * @return bool
     */
    public function validateInputs() {
        $model = static::MODEL;
        $fillables = (new $model)->getFillable();
        $fields = array();
        $user_input = Input::all();
        foreach ($user_input as $input_name => $input_val) {
            if (in_array($input_name, $fillables)) {
                $fields[$input_name] = $model::getValidationRules($input_name);
            }
        }
        if (count($fields)) {
            $validator = Validator::make($user_input, $fields);
            return ($validator->passes());
        }
        return true;
    }

    /**
     * Deletes the data for an specific id of the current model
     * @param $id
     */
    public function delete($id) {
        $model = static::MODEL;
        $item = $model::findOrFail($id);

        //deleting related models
        //$item->roles()->detach();
        foreach($item->getDeletableModels() as $rel_model => $type) {
            if ($type == 'many') {
                $item->$rel_model()->detach();
            }
            else {
                $item->$rel_model()->delete(); // ???
            }
        }
        $to_log = serialize($item->toArray());
        $item->delete();
        //logging
        ActionLog::log($model . ' deleted', $to_log, $id);
    }

    /**
     * Compara el valor de query con los datos en la base de datos y retorna un arreglo con los registros que coincidan
     * @param $query
     * @param $page
     * @param &$search_fields
     * @param &$match_total
     * @param $fields
     * @return array
     */
    public function buscar($query, $page, &$search_fields, &$match_total, $fields = null) {
        $model = static::MODEL;
        $page = (int)$page;
        if ($page <= 0) $page = 1;

        $query = explode(' ', $query);

        $records = new $model;
        $search_fields = $records->getSearchable();
        $first = true;
        foreach($query as $q) {
            $q = trim($q);
            if (strlen($q) > 0) { //if (strlen($q) > 1 || is_numeric($q)) {
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
                $first = false;
            }
        }

        if (!$first) {
            $match_total = $records->count();

            $records = $records->skip(($page-1) * static::PAGE_LIMIT)->limit(static::PAGE_LIMIT);
            if ($fields == null) {
                $records = $records->get();
            }
            else {
                $records = $records->get($fields); //untested (!)
            }

            return $records;
        }

        return array();
    }

    /**
     * Compara el valor de query con los datos en la base de datos y retorna un arreglo con los registros que coincidan
     * @param $nombre_tabla
     * @param $query
     * @param $page
     * @param $search_fields
     * @param &$match_total
     * @param $fields
     * @return array|\Illuminate\Database\Query\Builder|static
     */
    public function buscarTabla($nombre_tabla, $query, $page, $search_fields, &$match_total, $fields = null, $order_by = null) {
        $records = DB::table($nombre_tabla);
        $page = (int)$page;
        if ($page <= 0) $page = 1;

        $query = explode(' ', $query);

        $first = true;
        foreach($query as $q) {
            $q = trim($q);
            if (strlen($q) > 1) {
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
                $first = false;
            }
        }

        if (!$first) {
            $match_total = $records->count();

            $records = $records->skip(($page-1) * static::PAGE_LIMIT)->limit(static::PAGE_LIMIT);

            if ($order_by !== null) {
                if (is_array($order_by)) {
                    $records = $records->orderBy($order_by[0], $order_by[1]);
                }
                else {
                    $records = $records->orderBy($order_by);
                }
            }

            if ($fields == null) {
                $records = $records->get();
            }
            else {
                $records = $records->get($fields); //untested (!)
            }

            return $records;
        }

        return array();
    }

    public function listSeek($fields_to_show = null, $separator = ' ', $capitalize = false) {
        $q = Input::get('q');
        $search_fields = '';
        $total = 0;
        $records = $this->buscar($q, 1, $search_fields, $total);

        $list = array();
        foreach($records as $record) {
            if ($fields_to_show == null) {
                $field = static::TITLE_FIELD;
                $list[] = json_encode(array(
                    'name' => $record->$field,
                    '_id' => $record->id
                ));
            }
            elseif (is_array($fields_to_show)) {
                $name = array();
                $i = 0;
                foreach($fields_to_show as $field) {
                    if ($i < 2) {
                        if ($capitalize) {
                            $name[] = ucfirst(mb_strtolower($record->$field));
                        }
                        else {
                            $name[] = $record->$field;
                        }
                    }
                    else {
                        $name[] = '(' . $record->$field . ')';
                    }
                    $i++;
                }
                $name = implode($separator, $name);
                $list[] = json_encode(array(
                    'name' => $name,
                    '_id' => $record->id
                ));
            }
        }

        return '[' . implode(',', $list) . ']';
    }

    public function registrar($model) {
        //$model = self::MODEL;
        $validator = Validator::make(Input::all(),
            $model::getValidationRules(null, Input::get('id'))
        );

        if ($validator->passes()) {
            if (method_exists(static::MODEL . 'Controller', 'afterValidation')) {
                if (!$this->afterValidation( Input::all() )) return false;
            }
            $created = $model::create(Input::all());
            if ($created) {
                $this->setReturn('created_id', $created->id);
                $this->setSuccess(Lang::get('global.saved_msg'), false);
                //logging
                $to_log = serialize($created->toArray());
                ActionLog::log($model . ' created', $to_log, $created->id);
                return $created;
            }
            $this->setError(Lang::get('global.unable_perform_action'));
            return false;
        }
        $this->setError($validator->messages()->first());
        return false;
    }

    public function editar($model) {
        $id = (int)Input::get('id');
        if ($id > 0) {
            $all_inputs = Input::all();

            $validator = Validator::make($all_inputs,
                $model::getValidationRules(null, $id)
            );

            if ($validator->passes()) {
                if (method_exists(static::MODEL . 'Controller', 'afterValidation')) {
                    if (!$this->afterValidation( Input::all() )) return false;
                    $all_inputs = Input::all();
                }
                $item = $model::find($id);
                if ($item) {
                    $fields = $item->getFillable();
                    foreach ($all_inputs as $input => $value) {
                        if (in_array($input, $fields)) {
                            $item->$input = $value;
                        }
                    }
                    //unchecked input checkboxes (AKA booleans) will be assigned to zero here if not send
                    foreach ($item->getBooleans() as $field) {
                        if (!array_key_exists($field, $all_inputs)) {
                            $item->$field = 0;
                        }
                    }
                    $to_log = serialize($item->toArray());
                    $item->save();
                    $this->setSuccess( Lang::get('global.saved_msg'), false );
                    //logging
                    ActionLog::log($model . ' edited', $to_log, $item->id);
                    return $item;
                }
                else {
                    $this->setError( Lang::get('global.not_found') );
                }
            }
            else {
                $this->setError( $validator->messages()->first() );
            }
        }
        else {
            $this->setError( Lang::get('global.not_found') );
        }
        return false;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function setReturn($key, $value) {
        $this->output[$key] = $value;
    }

    /**
     * @param $msg
     * @return string
     */
    protected function setSuccess($msg, $return = true) {
        $this->output['ok'] = 1;
        $this->output['msg'] = $msg;

        if ($return) return json_encode($this->output);
    }

    /**
     * @param $err
     * @param $return
     * @return string
     */
    protected function setError($err, $return = true) {
        $this->output['ok'] = 0;
        $this->output['err'] = $err;

        if ($return) return json_encode($this->output);
    }

    /**
     * @return string
     */
    protected function returnJson() {
        if (!isset($this->output['ok'])) {
            $this->output['ok'] = 1; //defaults to 'ok' if not present
        }
        return json_encode($this->output);
    }

    /**
     * @param $arr
     */
    protected function addToOutput($arr) {
        if (is_array($arr)) {
            $this->output = array_merge($this->output, $arr);
        }
    }

}
