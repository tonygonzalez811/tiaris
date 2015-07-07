<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 30/12/14
 * Time: 04:26 PM
 */

class PersonaController extends BaseController {

    const PAGE_LIMIT = 5;

    const MODEL = 'Persona';

    const LANG_FILE = 'pacientes';

    const TITLE_FIELD = 'dni';

    private $counter = 0;

    /** Navegacion **/

    /**
     * Muestra la página de administración de Pacientes
     * @param string $tipo
     * @return mixed
     */
    public function paginaAdmin($tipo = 'paciente') {
        if (User::canAdminPersonas()) {
            $model = self::MODEL;
            switch ($tipo) {
                case 'doctor':
                    $total = Doctor::count();
                    $title = Lang::get('usuarios.doctors');
                    $tipo = User::ROL_DOCTOR;
                    break;

                case 'tecnico':
                    $total = Tecnico::count();
                    $title = Lang::get('usuarios.technicians');
                    $tipo = User::ROL_TECHNICIAN;
                    break;

                default:
                case 'paciente':
                    $total = $this->getTotalItems();
                    $title = Lang::get('pacientes.title_plural');
                    $tipo = USER::ROL_PATIENT;
            }
            $genders = Functions::langArray(self::LANG_FILE, $model::getGenders());
            $marital_statuses = Functions::langArray(self::LANG_FILE, $model::getMaritalStatuses());
            $roles = RolController::getRoles();
            $especialidades = Especialidad::lists('nombre', 'id');
            return View::make('admin.personas')->with(
                array(
                    'active_menu' => 'personas',
                    'total' => $total,
                    'genders' => $genders,
                    'marital_statuses' => $marital_statuses,
                    'tipo' => $tipo,
                    'title' => $title,
                    'roles' => $roles,
                    'especialidades' => $especialidades
                )
            );
        }
        return View::make('admin.inicio');
    }

    /**
     * Muestra la página de administración de Pacientes para reportes
     * @return mixed
     */
    public function paginaAdminReporte() {
        if (User::canAdminReportes()) {
            $model = self::MODEL;
            $total = $this->getTotalItems();
            $genders = Functions::langArray(self::LANG_FILE, $model::getGenders());
            $marital_statuses = Functions::langArray(self::LANG_FILE, $model::getMaritalStatuses());
            return View::make('admin.pacientes_alt')->with(
                array(
                    'active_menu' => 'reportes',
                    'total' => $total,
                    'genders' => $genders,
                    'marital_statuses' => $marital_statuses
                )
            );
        }
        return View::make('admin.inicio');
    }

    /**
     * Muestra la página de Mi Cuenta del usuario que inicio sesión
     * @return mixed
     */
    public function paginaMiCuenta() {
        $model = self::MODEL;
        $genders = Functions::langArray('pacientes', $model::getGenders());
        $marital_statuses = Functions::langArray('pacientes', $model::getMaritalStatuses());

        $user = Auth::user()->persona;
        if ($user) {
            $field_values = $user->toArray();
            
            $phones = array();
            $emails = array();
            $this->getContactInfo($user, $phones, $emails);

            $field_values = array_merge($field_values, array(
                'telefonos' => $phones,
                'correos'   => $emails
            ));

            if (User::is(User::ROL_DOCTOR)) {
                $doctor = $user->doctor;
                if ($doctor) {
                    $field_values = array_merge($field_values, array(
                        'especialidad' => $doctor->especialidad,
                        'numero' => $doctor->numero
                    ));
                }
            }

            if (User::is(User::ROL_TECHNICIAN)) {
                $tecnico = $user->tecnico;
                if ($tecnico) {
                    $field_values = array_merge($field_values, array(
                        'cod_dicom' => $tecnico->cod_dicom
                    ));
                }
            }
        }
        else {
            $field_values = null;
        }

        $especialidades = Especialidad::lists('nombre', 'id');

        return View::make('admin.mi_cuenta')->with(
            array(
                'active_menu' => '',
                'total' => null,
                'genders' => $genders,
                'marital_statuses' => $marital_statuses,
                'field_values' => $field_values,
                'especialidades' => $especialidades
            )
        );
    }

    /**
     * This function will be called after the model validation has passed successfully
     * @param $inputs
     * @return boolean
     */
    public function afterValidation($inputs) {
        if (!User::canAdminPersonas()) {
            $id = (int)Input::get('id');
            if ($id > 0) {
                //if the user to be modified is not the current one, then abort
                if (Auth::user()->admin || $id != Auth::user()->persona->id) {
                    return false;
                }
            } else {
                //if creating a new profile it needs to be for the current one only
                if (!Auth::user()->admin && Input::get('usuario_id') != Auth::user()->id) {
                    return false;
                }
            }
        }

        if (isset($inputs['tipo'])) {
            $tipo = $inputs['tipo'];
            if ($tipo == User::ROL_DOCTOR || $tipo == User::ROL_TECHNICIAN) {
                $data = Input::all();
                if (isset($inputs['user_nombre'])) $data['nombre'] = $inputs['user_nombre'];

                //checking user data
                $validator = Validator::make($data, User::getValidationRules());
                if ($validator->fails()) {
                    $this->setError($validator->messages()->first());
                    return false;
                }

                //checking doctor/technician data
                if ($tipo == User::ROL_DOCTOR) {
                    $rules = Doctor::getValidationRules();
                    unset($rules['persona_id']);
                    $validator = Validator::make($data, $rules);
                    if ($validator->fails()) {
                        $this->setError($validator->messages()->first());
                        return false;
                    }
                }
                elseif ($tipo == User::ROL_TECHNICIAN) {
                    $rules = Tecnico::getValidationRules();
                    unset($rules['persona_id']);
                    $validator = Validator::make($data, $rules);
                    if ($validator->fails()) {
                        $this->setError($validator->messages()->first());
                        return false;
                    }
                }
            }
        }

        if (!User::canEditDeletePersonas()) {
            unset($_POST['dni']);
            unset($_POST['nombre']);
            unset($_POST['apellido']);
        }
        return true;
    }

    /**
     * Proceso adicional al editar / crear un nuevo registro
     * @param $item
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    public function editarRelational($item) {
        if (Input::get('my_account')) {
            $user = Auth::user();
            if ($user->persona_id != $item->id) {
                $user->persona_id = $item->id;
                $user->save();
            }
        }

        //contactos
        if (Input::get('telefonos_check') !== Input::get('telefonos') || Input::get('correos_check') !== Input::get('correos')) {
            $contacts = $item->contactos;

            //telefonos
            $phones = isset($_POST['telefonos']) ? explode(',', Input::get('telefonos')) : false;
            Persona::saveContacts($item, $phones, Contacto::PHONE, $contacts);

            //correos
            $emails = isset($_POST['correos']) ? explode(',', Input::get('correos')) : false;
            Persona::saveContacts($item, $emails, Contacto::EMAIL, $contacts);
        }

        Input::merge(array('persona_id' => $item->id));

        //creating types
        $tipo = Input::get('tipo');
        if (isset($tipo)) { //from admin
            switch ($tipo) {
                case User::ROL_DOCTOR:
                    $data = array(
                        'numero' => Input::get('numero'),
                        'especialidad_id' => Input::get('especialidad_id'),
                        'persona_id' => $item->id
                    );
                    Doctor::create($data);
                    break;

                case User::ROL_TECHNICIAN:
                    $data = array(
                        'cod_dicom' => Input::get('cod_dicom'),
                        'persona_id' => $item->id
                    );
                    Tecnico::create($data);
                    break;

                default:
                    $tipo = false;
            }
            if ($tipo) {
                //creating user
                $data = array(
                    'nombre' => Input::get('user_nombre'),
                    'password' => Input::get('password'),
                    'admin' => Input::get('admin'),
                    'persona_id' => $item->id
                );
                $user = User::create($data);
                if ($user) {
                    $user->roles()->attach($tipo);
                }
            }
        }
        else { //from my account
            //doctor
            $especialidad_id = Input::get('especialidad_id');
            $numero = Input::get('numero');
            if (isset($especialidad_id, $numero)) {
                if (User::is(User::ROL_DOCTOR)) {
                    $doctor = Doctor::where('persona_id', '=', $item->id)->first();
                    $validator = Validator::make(Input::all(), Doctor::getValidationRules(null, $doctor ? $doctor->id : 0));
                    if ($validator->passes()) {
                        if ($doctor && $doctor->id > 0) {
                            $doctor->especialidad_id = $especialidad_id;
                            $doctor->numero = $numero;
                            $doctor->save();
                        } else {
                            $data = array(
                                'especialidad' => $especialidad_id,
                                'numero' => $numero,
                                'persona_id' => $item->id
                            );
                            Doctor::create($data);
                        }
                    }
                }
            }

            //tecnico
            $cod_dicom = Input::get('cod_dicom');
            if (isset($cod_dicom)) {
                if (User::is(User::ROL_TECHNICIAN)) {
                    $technician = Tecnico::where('persona_id', '=', $item->id)->first();
                    $validator = Validator::make(Input::all(), Tecnico::getValidationRules(null, $technician ? $technician->id : 0));
                    if ($validator->passes()) {
                        if ($technician && $technician->id > 0) {
                            $technician->cod_dicom = $cod_dicom;
                            $technician->save();
                        } else {
                            $data = array(
                                'cod_dicom' => $cod_dicom,
                                'persona_id' => $item->id
                            );
                            Tecnico::create($data);
                        }
                    }
                }
            }
        }

        //avatar
        if (Input::hasFile('avatar')) {
            $file = Input::file('avatar');
            if ($file->isValid()) {
                $extension = strtolower($file->getClientOriginalExtension());
                //if (in_array($extension, array('jpg', 'jpeg', 'png'))) {
                    $destination_path = 'img/avatars';
                    $filename = uniqid() . '.' . $extension;//$file->getClientOriginalName();
                    $esc = 0;
                    while (file_exists($destination_path . '/s/' . $filename)) {
                        $filename = uniqid() . '.' . $extension;
                        $esc++;
                        if ($esc >= 30) return true; //just a escape for too many failed attempts to get a unique name
                    }
                    $moved = $file->move($destination_path, $filename);
                    if ($moved) {
                        Functions::smart_resize_image($destination_path . '/' . $filename, null, 256, 256, false, $destination_path . '/s/' . $filename, false);
                        $item->avatar = $filename;
                        $item->save();

                        //if the current user change its profile picture, clear the avatars name in order to reload the new one
                        $user = $item->usuario;
                        if ($user && $user->id == Auth::user()->id) {
                            Session::forget('user_avatar');
                        }
                    }
                //}
            }
            //return $this->paginaMiCuenta();
            //return Redirect::route('mi_cuenta');
        }

        return true;
    }

    /**
     * Datos adicionales que se envian al solicitar la información del registro para editar
     * @param $item
     */
    public function additionalData($item) {
        $telefonos = array();
        $correos = array();
        $this->getContactInfo($item, $telefonos, $correos);

        $user = $item->usuario;
        if ($user) {
            $this->setReturn('usuario_id_lbl', $user->nombre);
        }
        $this->setReturn('telefonos', $telefonos);
        $this->setReturn('correos', $correos);
        $socials = array(3 => 'f', 4 => 't', 5 => 'i');
        foreach ($socials as $key => $val) {
            $this->setReturn('social_' . $val, isset($social[$key]) ? $social[$key] : '');
        }
    }

    /**
     * Código HTML que se envía al solicitar la información del registro para visualizar
     * @param $item
     * @return string
     */
    public function outputInf($item) {
        $telefonos = array();
        $correos = array();
        $this->getContactInfo($item, $telefonos, $correos, true);

        $frm = new AForm;
        $output = "";
        $output .= $frm->id( $item->id );
        $output .= $frm->hidden('action');

        $output .= $frm->halfPanelOpen(true, 7);
        $output .= $frm->view('name', Lang::get(self::LANG_FILE . '.name'), strtoupper($item->apellido) . ', ' . $item->nombre);
        $output .= $frm->view('dni', Lang::get(self::LANG_FILE . '.dni'), $item->dni);
        if ($item->fecha_nacimiento > 0) $output .= $frm->view('birthdate', Lang::get(self::LANG_FILE . '.birthdate'), Functions::shortDateFormat($item->fecha_nacimiento) . ' (' . Functions::ageFromDate($item->fecha_nacimiento) . ' ' . Lang::get('global.years') . ')');
        $output .= $frm->view('gender', Lang::get(self::LANG_FILE . '.gender'), Lang::get(self::LANG_FILE . '.' . Persona::getGenders($item->sexo)));
        $output .= $frm->view('marital_status', Lang::get(self::LANG_FILE . '.marital_status'), Lang::get(self::LANG_FILE . '.' . Persona::getMaritalStatuses($item->estado_civil)));
        $output .= $frm->view('address', Lang::get(self::LANG_FILE . '.address'), $item->direccion);
        $user = $item->usuario;
        if ($user) {
            $output .= $frm->view('user', Lang::get('usuarios.title_single'), $user->nombre . AForm::badge(Lang::get('usuarios.admin'),$user->admin));
        }
        $output .= $frm->halfPanelClose();

        $output .= $frm->halfPanelOpen(false, 5);
        $output .= $frm->view('contact_phones', Lang::get(self::LANG_FILE . '.phone'), $telefonos, 'fa-phone');
        $output .= $frm->view('contact_emails', Lang::get(self::LANG_FILE . '.email'), $correos, 'fa-envelope');

        $output .= $frm->halfPanelClose(true);

        if (User::canEditDeletePersonas()) {
            $output .= $frm->controlButtons();
        }
        elseif (User::canEditPersonas()) {
            $output .= $frm->controlButtons(null, false);
        }

        //$this->setReturn('script', $frm->script());

        return $output;
    }

    /**
     * Código HTML que se envía al realizar una búsqueda
     * @param $records
     * @param $search_fields
     * @return string
     */
    public function buscarReturnHtml($records, $search_fields) {
        return AForm::searchResults($records, 'nombre', 'apellido');
    }

    public function searchByFields($min_age, $max_age, $birthdate, $gender, $marital_statuses, $with_email, $with_twitter, $with_instagram) {
        //from view:
        if ($with_email || $with_twitter || $with_instagram) {
            $records = DB::table('vw_persona_contacto'); //this table only includes patients with contact information
        }
        else {
            $records = DB::table('vw_persona');
        }

        if ($min_age > $max_age) {
            $tmp_age = $max_age;
            $max_age = $min_age;
            $min_age = $tmp_age;
        }
        
        $cur_year = date('Y');

        $records = $records->take(500);
        
        if ($min_age > 0) {
            $year = $cur_year - $min_age;
            $records = $records->whereRaw('YEAR(fecha_nacimiento) <= ' . $year);
        }

        if ($max_age > 0) {
            $year = $cur_year - $max_age;
            $records = $records->whereRaw('YEAR(fecha_nacimiento) >= ' . $year);
        }

        if ($birthdate != '') {
            $birthdate = explode('-', $birthdate);
            if (count($birthdate) == 3) {
                $records = $records->whereRaw('(month(fecha_nacimiento) = ' . (int)$birthdate[1] . ' AND day(fecha_nacimiento) = ' . (int)$birthdate[2] . ')');
            }
        }

        if ($gender != '') {
            $records = $records->where('sexo', '=', (int)((bool)$gender));
        }

        if (is_array($marital_statuses) && count($marital_statuses)) {
            if (count($marital_statuses) > 1) {
                array_walk($marital_statuses, 'intval');
                $records = $records->whereIn('estado_civil', $marital_statuses);
            }
            else {
                $records = $records->where('estado_civil', '=', (int)reset($marital_statuses));
            }
        }

        if ($with_email) {
            $records = $records->where('tipo_contactos', 'LIKE', '%2%');
        }

        if ($with_twitter) {
            $records = $records->where('tipo_contactos', 'LIKE', '%4%');
        }

        if ($with_instagram) {
            $records = $records->where('tipo_contactos', 'LIKE', '%5%');
        }

        $records = $records->get();

        return $records;
    }

    public function buscarTipoGet($tipo = User::ROL_PATIENT) {
        $tipo = $tipo == User::ROL_DOCTOR ? 'doctor' : ($tipo == User::ROL_TECHNICIAN ? 'tecnico' : '');
        switch ($tipo) {
            case 'doctor':
            case 'tecnico':
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
                        $records = $this->buscarTabla( 'vw_' . $tipo, $query, $page, $search_fields, $match_total );
                        $total = count($records);
                    }
                    else {
                        //gets all records
                        $records = DB::table('vw_' . $tipo)->get();

                        $model = ucfirst($tipo);
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
                break;
        }
        return $this->buscarGet();
    }

    public function buscarGetAlt() {
         $validator = Validator::make(Input::all(),
            array(
                'search_min_age'        => 'integer|min:0',
                'search_max_age'        => 'integer|min:0',
                'search_birthdate'      => 'date_format:Y-m-d',
                'search_gender'         => '',
                'search_marital_status' => 'array'
            )
        );
        if ($validator->passes()) {
            $min_age = (int)Input::get('search_min_age');
            $max_age = (int)Input::get('search_max_age');
            $birthdate = Input::get('search_birthdate');
            $gender = Input::get('search_gender', '');
            $marital_statuses = Input::get('search_marital_status');
            
            $with_email = (bool)Input::get('search_with_email');
            $with_twitter = (bool)Input::get('search_with_twitter');
            $with_instagram = (bool)Input::get('search_with_instagram');

            $records = $this->searchByFields($min_age, $max_age, $birthdate, $gender, $marital_statuses, $with_email, $with_twitter, $with_instagram);

            $total = count($records);

            $this->setReturn('total', $total);
            $this->setReturn('total_page', $total);

            //if (!$is_export) {
                $this->counter = 0;
                $this->setReturn('results', $this->buscarReturnHtmlTable($records, $with_email, $with_twitter, $with_instagram));
            //}
            return $this->returnJson();
        }
        return $this->setError( Lang::get('global.wrong_action') );
    }


    public function buscarReturnHtmlTable($records/*, $with_email, $with_twitter, $with_instagram*/) {
        $id = 'tbl_return_' . uniqid();
        $script = <<<EOT
            $('#{$id}').bootstrapTable();

            if ($('#found_emails').val().length > 0) {
                $('.copy-emails').click(function(e) {
                   e.preventDefault();
                   var list = $('#found_emails').val();
                   copyToClipboard(list);
                   return false;
                });
            }
            else {
                $('.copy-emails').hide();
            }
EOT;
        $this->setReturn('script', $script);
        $output = <<<EOT
            <table id="{$id}">
                <thead>
                    <tr>
                        <th data-field="count" data-sortable="true">#</th>
                        <th data-field="fname" data-sortable="true">Nombre</th>
                        <th data-field="lname" data-sortable="true">Apellido</th>
                        <th data-field="dni" data-sortable="true">DNI</th>
                        <th data-field="birthdate" data-sortable="true">F. Nacimiento</th>
                        <th data-field="gender" data-sortable="true">Sexo</th>
                        <th data-field="marital_status" data-sortable="true">Estado Civil</th>
                        <th data-field="phone">Teléfono</th>
                        <th data-field="email">Correo <a href="#" title="Copiar todos..." class="copy-emails"><i class="fa fa-files-o"></i></a></th>
                    </tr>
                </thead>
                <tbody>
EOT;
        $emails = '';
        foreach ($records as $record) {
            $properties = Persona::getProperties($record);
            $this->counter++;
            $output.= <<<EOT
                    <tr>
                        <td>{$this->counter}</td>
                        <td>{$properties['fname']}</td>
                        <td>{$properties['lname']}</td>
                        <td>{$properties['dni']}</td>
                        <td>{$properties['birthdate']}</td>
                        <td>{$properties['gender']}</td>
                        <td>{$properties['marital_status']}</td>
                        <td>{$properties['phone']}</td>
                        <td>{$properties['email']}</td>
                    </tr>
EOT;
            if (strlen($properties['email']) > 0) $emails .= ($properties['email'] . ' ');
        }
        $output.= <<<EOT
                </tbody>
            </table>
            <input type="hidden" id="found_emails" value="{$emails}">
EOT;
        $this->setReturn('total', $this->counter);
        return $output;
    }

    private function getContactInfo($item, &$telefonos, &$correos, $formatted = false) {
        $contacts = $item->contactos;
        if ($contacts) {
            foreach ($contacts as $contact) {
                if ($contact->tipo == Contacto::PHONE) {
                    if ($formatted) {
                        $telefonos[] = Functions::formatPhone($contact->contenido);
                    } else {
                        $telefonos[] = $contact->contenido;
                    }
                } elseif ($contact->tipo == Contacto::EMAIL) {
                    if ($formatted) {
                        $correos[] = '<a href="mailto:' . $contact->contenido . '">' . $contact->contenido . '</a>';
                    } else {
                        $correos[] = $contact->contenido;
                    }
                }
            }
            if ($formatted) {
                $telefonos = implode('<br>', $telefonos);
                $correos = implode('<br>', $correos);
            } else {
                $telefonos = implode(',', $telefonos);
                $correos = implode(',', $correos);
            }
        }
    }


    public function setDniPost() {
        $model = self::MODEL;
        $validator = Validator::make(Input::all(),
            array(
                'dni'  => 'required|' . $model::getValidationRules('dni'),
                'tdni'  => $model::getValidationRules('tdni'),
                'persona_id' => $model::getValidationRules('id')
            )
        );
        $paciente_id = Input::get('persona_id');
        if ($validator->passes()) {
            $paciente = $model::find($paciente_id);
            if ($paciente) {
                if (empty($paciente->dni)) { //will only save if doesn't have one already
                    $paciente->tdni = Input::get('tdni');
                    $paciente->dni = Input::get('dni');
                    $paciente->save();
                    return $this->setSuccess( Lang::get('global.saved_msg') );
                }
            }
        }
        else {
            $failed = $validator->failed();
            if (isset($failed['dni']['Unique'])) {
                $dni = Input::get('dni');
                $patient = $model::where('dni', '=', $dni)->first();
                if ($patient) {
                    $this->setReturn('matched_id', $patient->id);
                    $this->setReturn('current_id', $paciente_id);
                    $this->setReturn('patient_name', $patient->nombre . ' ' . $patient->apellido);
                    $this->setReturn('patient_record_date', Functions::longDateFormat($patient->created_at));
                }
            }
            return $this->setError($validator->messages()->first());
        }
        return $this->setError( Lang::get('global.unable_perform_action') );
    }

    public function combinePost() {
        $model = self::MODEL;
        $matched = (int)Input::get('matched_id');
        $current = (int)Input::get('current_id');
        if ($matched > 0 && $current > 0 && $matched <> $current) {
            $ok = $model::combine($matched, $current);
            if ($ok) {
                return $this->setSuccess( Lang::get('global.saved_msg') );
            }
            return $this->setError( Lang::get('global.unable_perform_action') );
        }
        return $this->setError( Lang::get('global.wrong_action') );
    }


    public function listSeekAlt() {
        return $this->listSeek(array('nombre', 'apellido', 'dni'), ' ', true);
    }

    public function listSeekDoctor() {
        $query = Input::get('q');
        $query = explode(' ', $query);

        $search_fields = array('nombre', 'apellido', 'dni', 'especialidad', 'numero');

        $records = DB::table('vw_doctor');

        foreach($query as $q) {
            $q = trim($q);
            //if (strlen($q) > 1) {
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
            //}
        }

        $records = $records->get();

        $list = array();
        foreach($records as $record) {
            $list[] = json_encode(array(
                'name' => Functions::firstNameLastName($record->nombre, $record->apellido),
                '_id' => $record->id
            ));
        }

        return '[' . implode(',', $list) . ']';
    }


    public function listSeekTecnico() {
        $query = Input::get('q');
        $query = explode(' ', $query);

        $search_fields = array('nombre', 'apellido', 'dni', 'cod_dicom');

        $records = DB::table('vw_tecnico');

        foreach($query as $q) {
            $q = trim($q);
            //if (strlen($q) > 1) {
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
            //}
        }

        $records = $records->get();

        $list = array();
        foreach($records as $record) {
            $list[] = json_encode(array(
                'name' => Functions::firstNameLastName($record->nombre, $record->apellido),
                '_id' => $record->id
            ));
        }

        return '[' . implode(',', $list) . ']';
    }


    /**
     * File should be in "public" folder and be called pacientes.csv
     * Expected format (UTF-8):
     *      NOMBRE;APELLIDO;CI;TELEFONO
     *      Francisco;Peña;3494348;0414-9974374
     *      Maria;Muñoz;3654138;0414-7689364
     *      Yosaida;Cedeño;6210707;0424-9174384
     * @return string
     */
    public function cargarPacientesCsv() {
        if (($f = fopen('pacientes.csv', 'r')) !== false) {
            $model = self::MODEL;
            $total = 0;
            $total_invalid = 0;
            $first_invalid = 0;
            $counter = 0;
            $first = true;
            while (($data = fgetcsv($f, 50, ';')) !== false) {
                if ($first) {
                    $first = false;
                    continue;
                }
                if (count($data) == 4) {
                    $datos = array(
                        'nombre'    => $data[0],
                        'apellido'  => $data[1],
                        'dni'       => $data[2],
                        'sexo'      => 0, //defaults to female
                    );
                    $phone = $data[3];
                    $validator = Validator::make(
                        array_merge($datos, array('telefonos' => $phone)),
                        $model::getValidationRules()
                    );
                    if ($validator->passes()) {
                        $paciente = $model::create($datos);
                        if ($paciente) {
                            $counter++;
                            $total++;
                            if (!empty($phone)) {
                                //TODO[alfredo]:: $paciente->contacto()->attach( array(0 => array('tipo_contacto_id' => 1, 'contacto' => $phone)) );
                            }
                        }
                    }
                    else {
                        $total_invalid++;
                        if (!$first_invalid) $first_invalid = $total . '|' . $validator->messages()->first();//$total;
                    }
                }
                if ($counter >= 100) {
                    sleep(2);
                    $counter = 0;
                }
            }
            fclose($f);
        }
        if (!isset($total)) {
            return 'No se cargaron pacientes.';
        }
        return "Se cargaron {$total} pacientes. {$total_invalid} registros fallaron la validación. ({$first_invalid})";
    }


    public function totalPeronasGet($tipo = User::ROL_PATIENT) {
        switch ($tipo) {
            case (User::ROL_DOCTOR):
                $total = Doctor::count();
                break;

            case (User::ROL_TECHNICIAN):
                $total = Tecnico::count();
                break;

            default:
                $total = $this->getTotalItems();
        }
        $this->setReturn('total', $total);
        return $this->returnJson();
    }

}