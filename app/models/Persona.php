<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 29/12/14
 * Time: 01:06 PM
 */

class Persona extends Eloquent {

    public $timestamps = true;

    protected $fillable = array(
        'nombre',
        'apellido',
        'tdni',
        'dni',
        'fecha_nacimiento',
        'sexo',
        'estado_civil',
        'direccion'
    );

    protected $table = 'persona';

    protected $searchable = array(
        'nombre',
        'apellido',
        'dni'
    );

    protected $booleans = array(

    );

    protected $deletable_models = array(

    );

    public static function getMaritalStatuses($item = null) {
        $elems = array(
            0 => 'single',
            1 => 'married',
            2 => 'divorced',
            3 => 'widower'
        );
        if ($item === null) {
            return $elems;
        }
        return $elems[$item];
    }

    public static function getGenders($item = null) {
        $elems = array(
            0 => 'female',
            1 => 'male'
        );
        if ($item === null) {
            return $elems;
        }
        return $elems[$item];
    }

    /**
     * Devuélve las reglas de validación para un campo específico o el arreglo de reglas por defecto.
     *
     * @param string $field     Nombre del campo del que se quiere las reglas de validación.
     * @param int $ignore_id    ID del elemento que se está editando, si es el caso.
     * @return array
     */
    public static function getValidationRules($field = null, $ignore_id = 0) {
        $rules = array(
            'id'                => 'integer|min:0',
            'nombre'            => 'required|alpha_spaces|max:63',
            'apellido'          => 'required|alpha_spaces|max:63',
            'tdni'              => 'in:V,E,J',
            'dni'               => 'regex:/^[0-9]{7,9}$/|unique:persona,dni,' . (int)$ignore_id,
            'fecha_nacimiento'  => 'date_format:Y-m-d',
            'sexo'              => 'in:0,1',
            'estado_civil'      => 'in:0,1,2,3', //soltero, casado, divorciado, viudo
            'direccion'         => 'max:255',
            'telefonos'         => 'regex:/[0-9,]+/',
            'avatar'            => 'image'
        );
        if ($field === null) {
            return $rules;
        }
        return $rules[$field];
    }

    public function setDniAttribute($value)
    {
        $this->attributes['dni'] = strtoupper($value);
    }

    public function setTdniAttribute($value)
    {
        $this->attributes['tdni'] = empty($value) ? 'V' : $value;
    }

    public function setUsuarioIdAttribute($value)
    {
        $value = (int)$value;
        $this->attributes['usuario_id'] = $value == 0 ? null : $value;
    }

    public function setSexoAttribute($value)
    {
        $this->attributes['sexo'] = (int)$value;
    }

    public function setFechaNacimientoAttribute($value)
    {
        $this->attributes['fecha_nacimiento'] = (empty($value) || $value == '0000-00-00') ? null : $value;
    }

    public function getDniAttribute()
    {
        if (empty($this->attributes['dni'])) return '';
        $tdni = $this->attributes['tdni'];
        return (!empty($tdni) ? strtoupper($tdni) : 'V') . '-' . preg_replace('/[^0-9]/', '', $this->attributes['dni']);
    }

    public function getNombreAttribute()
    {
        return ucwords($this->attributes['nombre']);
    }

    public function getApellidoAttribute()
    {
        return ucwords($this->attributes['apellido']);
    }

    //RELACIONES:
    public function contactos() {
        return $this->hasMany('Contacto', 'persona_id');
    }

    public function usuario() {
        return $this->hasOne('User', 'persona_id', 'id');
    }

    public function citas() {
        return $this->hasMany('Cita', 'persona_id', 'id');
    }

    public function doctor() {
        return $this->hasOne('Doctor', 'persona_id', 'id');
    }

    public function tecnico() {
        return $this->hasOne('Tecnico', 'persona_id', 'id');
    }


    public function getSearchable() {
        return $this->searchable;
    }

    public function getBooleans() {
        return $this->booleans;
    }

    public function getDeletableModels() {
        return $this->deletable_models;
    }


    public static function combine($main, $new) {
        $main = self::find($main);
        if ($main) {
            $new = self::find($new);
            if ($new) {
                $changed = false;
                $fields = $main->getFillable();
                foreach ($fields as $field) {
                    if (empty($main->$field) && (!empty($new->$field))) {
                        $changed = true;
                        $main->$field = $new->$field;
                    }
                }
                if ($changed) {
                    $main->save();
                }
                //adds the contact info from the 'dni'less one to the right one
                $new->contactos()->associate($main); //DB::table('contacto')->where('persona_id', '=', $new->id)->where('contacto', '<>', '')->update(array('persona_id' => $main->id));

                //changes all the appointments assigned to the 'dni'less one for the right one
                $new->citas()->associate($main); //DB::table('cita')->where('persona_id', '=', $new->id)->update(array('persona_id' => $main->id));

                //deletes the 'dni'less one
                $new->delete();
                return true;
            }
        }
        return false;
    }


    public static function saveContacts($item, $contacts, $type, &$existing_contacts) {
        if (is_array($contacts) && count($contacts)) {
            //check with existing ones
            if ($existing_contacts) {
                foreach ($existing_contacts as $contact) {
                    if ($contact->tipo == $type) {
                        if (($key = array_search(trim($contact->contenido), $contacts)) !== false) {
                            unset($contacts[$key]);
                        } else {
                            $contact->delete();
                        }
                    }
                }
            }
            foreach ($contacts as $key => $val) {
                if (strlen(trim($val)) > 0) {
                    $contacts[$key] = new Contacto(array('tipo' => $type, 'contenido' => $val));
                }
                else {
                    unset($contacts[$key]);
                }
            }
            if (count($contacts)) {
                $item->contactos()->saveMany( $contacts );
            }
        }
        else {
            $item->contactos()->where('tipo', '=', $type)->delete();
        }
    }


    public static function getProperties($item) {
        $phone = '';
        $email = '';
        //This is available using the view 'vw_persona'
        $tcontacts = explode(',', $item->tipo_contactos);
        $contacts = explode(',', $item->contactos);
        if (is_array($tcontacts) && count($tcontacts)) {
            foreach ($tcontacts as $key => $type) {
                switch ($type) {
                    case 1:
                        $phone .= (($phone != '' ? ', ' : '') . Functions::formatPhone($contacts[$key]));
                        break;
                    case 2:
                        $email .= (($email != '' ? ', ' : '') . strtolower($contacts[$key]));
                        break;
                }
            }
        }

        return array(
            'fname' => Functions::capitalize($item->nombre),
            'lname' => Functions::capitalize($item->apellido),
            'dni' => strlen($item->dni) > 0 ? ((strlen($item->tdni) > 0 ? ($item->tdni . '-') : '') . number_format(intval($item->dni), 0, ',', '.')) : '',
            'birthdate' => Functions::shortDateFormat($item->fecha_nacimiento),
            'gender' => Lang::get('pacientes.' . self::getGenders($item->sexo)),
            'marital_status' => Lang::get('pacientes.' . self::getMaritalStatuses($item->estado_civil)),
            'phone' => $phone,
            'email' => $email
       );
    }

} 