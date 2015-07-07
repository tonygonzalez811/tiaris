<?php

class AForm {
    protected $edit;
    protected $script;
    protected $values;
    protected $show_labels;
    protected $disabled;

    protected $accordion_id;
    protected $accordion_count;

    protected $include_empty_option;

    public function __construct() {
        $this->edit = false;
        $this->script = "";
        $this->values = array();
        $this->show_labels = true;
        $this->disabled = false;
        $this->include_empty_option = false;
    }

    public function setEdit($edit) {
        $this->edit = (bool)$edit;
    }

    public function setValues($values) {
        $this->values = $values;
    }

    public function clearValues() {
        $this->values = array();
    }

    public function displayLabels($show = true) {
        $this->show_labels = $show;
    }

    public function setDisabled($disabled = true) {
        $this->disabled = $disabled;
    }

    public function setIncludeEmptyOption($include) {
        $this->include_empty_option = (bool)$include;
    }

    public function text($name, $id = null, $label = null, $classes = "", $required = false, $validation_pattern = null) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $required = $required ? ' required' : '';
        $disabled = $this->disabled ? ' readonly' : '';
        $value = isset($this->values[$name]) ? ' value="' . $this->values[$name] . '"' : '';
        if (is_array($validation_pattern)) {
            $vp = ' pattern="' . $validation_pattern[0] . '" title="' . $validation_pattern[1] . '"';
        } else $vp = '';
        if ($this->show_labels) {
            return <<<EOT
            <div class="form-group {$classes}">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <div class="col-md-10">
                    <input type="text" id="{$id}" name="{$name}" class="form-control" placeholder="{$label}"{$vp}{$value}{$required}{$disabled}>
                </div>
            </div>
EOT;
        }
        else {
            return <<<EOT
            <div class="form-group {$classes}">
                <div class="col-md-12">
                    <input type="text" id="{$id}" name="{$name}" class="form-control" placeholder="{$label}"{$vp}{$value}{$required}>
                </div>
            </div>
EOT;
        }
    }

    public function username($name, $id = null, $label = null, $classes = "", $required = true) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $required = $required ? ' required' : '';
        $value = isset($this->values[$name]) ? ' value="' . $this->values[$name] . '"' : '';

        $ds = '$';
        $this->script.= <<<EOT
            $('#{$id}').blur(function() {
                var {$ds}in = $(this);
                {$ds}in.val( {$ds}in.val().toLowerCase() );
            });
EOT;

        if ($this->show_labels) {
            return <<<EOT
            <div class="form-group {$classes}">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <div class="col-md-10">
                    <input type="text" id="{$id}" name="{$name}" class="form-control" placeholder="{$label}"{$value}{$required}>
                </div>
            </div>
EOT;
        }
        else {
            return <<<EOT
            <div class="form-group {$classes}">
                <div class="col-md-12">
                    <input type="text" id="{$id}" name="{$name}" class="form-control" placeholder="{$label}"{$value}{$required}>
                </div>
            </div>
EOT;
        }
    }

    public function dni($name, $id = null, $label = null, $classes = "", $required = false, $validation_pattern = null) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $required = $required ? ' required' : '';
        $readonly = $this->disabled ? ' readonly' : '';
        $disabled = $this->disabled ? ' disabled' : '';
        $tvalue = 'V';
        if (isset($this->values[$name])) {
            $dni = $this->values[$name];
            $dni = explode('-', $dni);
            if (count($dni) > 1) {
                $tvalue = strtoupper($dni[0]);
                if (!in_array($tvalue, array('V', 'E', 'J'))) $tvalue = 'V';
                $value = ' value="' . $dni[1] . '"';
            }
            else {
                $value = ' value="' . $dni[0] . '"';
            }
        }
        else {
            $value = '';
        }
        if ($validation_pattern === null) {
            $validation_pattern = array('[0-9]{7,9}', '123456789');
        }
        if (is_array($validation_pattern)) {
            $vp = ' pattern="' . $validation_pattern[0] . '" title="' . $validation_pattern[1] . '"';
        } else $vp = '';

        $ds = '$';

        $this->script.= <<<EOT
            $('#{$id}_options').find('a').click(function(e) {
                var {$ds}a = $(this);
                $('#{$id}_options').parent().removeClass('open').find('button').find('span').html( {$ds}a.html() );
                $('#t{$id}').val( {$ds}a.html().split('-')[0] );
                e.preventDefault();
                return false;
            });
EOT;

        if ($this->show_labels) {
            return <<<EOT
            <div class="form-group {$classes}">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <div class="col-md-10">
                    
                    <div class="input-group">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle{$disabled}" data-toggle="dropdown" tabindex="-1">
                                <span>{$tvalue}-</span>
                            </button>
                            <ul id="{$id}_options" class="dropdown-menu" role="menu">
                                <li><a href="#">V-</a></li>
                                <li><a href="#">E-</a></li>
                                <li><a href="#">J-</a></li>
                            </ul>
                        </div>
                        <input type="hidden" id="t{$id}" name="t{$name}" value="{$tvalue}">
                        <input type="text" id="{$id}" name="{$name}" class="form-control input-dni" placeholder="{$label}"{$vp}{$value}{$required}{$readonly}>
                    </div>

                </div>
            </div>
EOT;
        }
        else {
            return <<<EOT
            <div class="form-group {$classes}">
                <div class="col-md-12">

                    <div class="input-group">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle{$disabled}" data-toggle="dropdown" tabindex="-1">
                                <span class="">V-</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="#">V-</a></li>
                                <li><a href="#">E-</a></li>
                                <li><a href="#">J-</a></li>
                            </ul>
                        </div>
                        <input type="hidden" id="t{$id}" name="t{$name}" value="V">
                        <input type="text" id="{$id}" name="{$name}" class="form-control" placeholder="{$label}"{$vp}{$value}{$required}{$readonly}>
                    </div>

                </div>
            </div>
EOT;
        }
    }

    public function textarea($name, $id = null, $label = null, $classes = "", $required = false) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $required = $required ? ' required' : '';
        $value = isset($this->values[$name]) ? $this->values[$name] : '';
        if ($this->show_labels) {
            return <<<EOT
            <div class="form-group {$classes}">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <div class="col-md-10">
                    <textarea id="{$id}" name="{$name}" class="form-control" placeholder="{$label}"{$required}>{$value}</textarea>
                </div>
            </div>
EOT;
        }
        else {
            return <<<EOT
            <div class="form-group {$classes}">
                <div class="col-md-12">
                    <textarea id="{$id}" name="{$name}" class="form-control" placeholder="{$label}"{$required}>{$value}</textarea>
                </div>
            </div>
EOT;
        }
    }

    public function email($name = 'correo', $id = null, $label = null, $classes = "", $required = true) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $required = $required ? ' required' : '';
        $value = isset($this->values[$name]) ? ' value="' . $this->values[$name] . '"' : '';
        if ($this->show_labels) {
            return <<<EOT
            <div class="form-group {$classes}">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <div class="col-md-10">
                    <input type="email" id="{$id}" name="{$name}" class="form-control" placeholder="{$label}"{$value}{$required}>
                </div>
            </div>
EOT;
        }
        else {
            return <<<EOT
            <div class="form-group {$classes}">
                <div class="col-md-12">
                    <input type="email" id="{$id}" name="{$name}" class="form-control" placeholder="{$label}"{$value}{$required}>
                </div>
            </div>
EOT;
        }
    }

    public function password($name = 'password', $id = null, $label = null, $classes = "", $required = true) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $required = $required ? ' required' : '';
        if ($this->show_labels) {
            return <<<EOT
            <div class="form-group {$classes}">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <div class="col-md-10">
                    <input type="password" id="{$id}" name="{$name}" class="form-control" placeholder="{$label}"{$required}>
                </div>
            </div>
EOT;
        }
        else {
            return <<<EOT
            <div class="form-group {$classes}">
                <div class="col-md-12">
                    <input type="password" id="{$id}" name="{$name}" class="form-control" placeholder="{$label}"{$required}>
                </div>
            </div>
EOT;
        }
    }

    public function number($name, $id = null, $label = null, $classes = "") {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        if ($this->show_labels) {
            return <<<EOT
            <div class="form-group {$classes}">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <div class="col-md-10">
                    <div class="input-group">
                        <div class="input-group-btn">
                            <button class="btn btn-default number-more" type="button"><i class="fa fa-plus"></i></button>
                            <button class="btn btn-default number-less" type="button"><i class="fa fa-minus"></i></button>
                        </div>
                        <input type="text" id="{$id}" name="{$name}" class="form-control number-input">
                    </div>
                </div>
            </div>
EOT;
        }
        else {
            return <<<EOT
            <div class="form-group {$classes}">
                <div class="col-md-12">
                    <div class="input-group">
                        <div class="input-group-btn">
                            <button class="btn btn-default number-more" type="button"><i class="fa fa-plus"></i></button>
                            <button class="btn btn-default number-less" type="button"><i class="fa fa-minus"></i></button>
                        </div>
                        <input type="text" id="{$id}" name="{$name}" class="form-control number-input">
                    </div>
                </div>
            </div>
EOT;
        }
    }

    public function date($name, $id = null, $label = null, $type = 'year', $classes = "") {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $value = isset($this->values[$name]) ? ' value="' . $this->values[$name] . '"' : '';

        $url = URL::route('full_date');

        $this->script .= <<<EOT
            $('#{$id}').change(function() {
                var o = $(this);
                if (o.val().length > 0) {
                    $.ajax({
                        type: 'GET',
                        url: '{$url}',
                        dataType: 'json',
                        data: { date : o.val() }
                    }).done(function(data) {
                        if (data['ok']) {
                            $('#{$id}_str').html( data['date'] );
                        }
                    });
                }
                else {
                    o.parent().find('#{$id}_str').html('&nbsp');
                }
            });
EOT;

        if ($this->show_labels) {
            return <<<EOT
          <div class="form-group {$classes}">
             <label for="{$id}" class="col-md-2 control-label">{$label}</label>
             <div class="col-md-10">
                <div class="input-group">
                    <input type="text" id="{$id}" name="{$name}" class="form-control input-calendar-{$type}" data-mask="9999-99-99" placeholder="{$label}"{$value}>
                    <span class="input-group-btn">
                        <button id="{$id}_str" class="btn btn-default disabled" type="button">
                            &nbsp;
                        </button>
                    </span>
                </div>
            </div>
          </div>
EOT;
        }
        else {
            return <<<EOT
          <div class="form-group {$classes}">
             <div class="col-md-12">
                <div class="input-group">
                    <input type="text" id="{$id}" name="{$name}" class="form-control input-calendar-{$type}" data-mask="9999-99-99" placeholder="{$label}"{$value}>
                    <span class="input-group-btn">
                        <button id="{$id}_str" class="btn btn-default disabled" type="button">
                            &nbsp;
                        </button>
                    </span>
                </div>
            </div>
          </div>
EOT;
        }
    }

    public function time($name, $id = null, $label = null, $classes = '') {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $value = isset($this->values[$name]) ? ' value="' . $this->values[$name] . '"' : '';

        if (strlen($value) > 0) {
            $this->script .= <<<EOT
                setTimePicker($('#{$id}'), '{$this->values[$name]}');
EOT;
        }

        if ($this->show_labels) {
            return <<<EOT
          <div class="form-group {$classes}">
             <label for="{$id}" class="col-md-2 control-label">{$label}</label>
             <div class="col-md-10">
                <div class="input-group">
                    <input type="text" id="{$id}" name="{$name}" class="form-control input-time" data-mask="99:99 aa" placeholder="{$label}"{$value}>
                </div>
            </div>
          </div>
EOT;
        }
        else {
            return <<<EOT
          <div class="form-group {$classes}">
             <div class="col-md-12">
                <div class="input-group">
                    <input type="text" id="{$id}" name="{$name}" class="form-control input-time" data-mask="99:99 aa" placeholder="{$label}"{$value}>
                </div>
            </div>
          </div>
EOT;
        }
    }

    public function checkbox($name, $id = null, $label = null, $classes = "") {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        if ($this->show_labels) {
            return <<<EOT
            <div class="form-group">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <div class="col-md-10">
                    <input type="checkbox" class="switch {$classes}" id="{$id}" name="{$name}" value="1">
                </div>
            </div>
EOT;
        }
        else {
            return <<<EOT
            <div class="form-group">
                <div class="col-md-12">
                    <input type="checkbox" class="switch {$classes}" id="{$id}" name="{$name}" value="1">
                </div>
            </div>
EOT;
        }
    }

    public function search($name = 'search', $id = null, $label = null, $classes = "") {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        return <<<EOT
            <div class="input-group {$classes}">
                <input type="search" id="{$id}" name="{$name}" class="form-control" placeholder="{$label}" required>
                <span class="input-group-btn">
                    <button type="submit" id="{$id}_btn" class="btn btn-default">
                        <i class="fa fa-search"></i>
                    </button>
                </span>
            </div>
EOT;
    }

    public function slider($name = 'correo', $id = null, $label = null, $ticks = array(), &$script, $step = null) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $value = isset($this->values[$name]) ? ' value="' . $this->values[$name] . '"' : '';

        $ticks_vals = array_keys($ticks);
        $ticks_snap = ($step === null ? ($ticks_vals[1] - $ticks_vals[0]) : $step);
        $ticks_vals = implode(', ', array_keys($ticks));
        $ticks_lbls = '"' . implode('", "', $ticks) . '"';

        $script = <<<EOT
            $('#{$id}').slider({
                value: 0,
                ticks: [{$ticks_vals}],
                ticks_labels: [$ticks_lbls],
                step: {$ticks_snap}
            });
EOT;
        /*ticks_snap_bounds: {$ticks_snap}*/

        if ($this->show_labels) {
            return <<<EOT
            <div class="form-group">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <div class="col-md-10">
                    <input type="text" id="{$id}" name="{$name}" style="width:95%" class="input-slider" placeholder="{$label}"{$value}>
                </div>
            </div>
EOT;
        }
        else {
            return <<<EOT
            <div class="form-group">
                <div class="col-md-12">
                    <input type="text" id="{$id}" name="{$name}" style="width:95%" class="input-slider" placeholder="{$label}"{$value}>
                </div>
            </div>
EOT;
        }
    }

    public function multiselect($name, $id = null, $label = null, $options = array(), $options_key = null, $options_val = null, $options_selected = array()) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $value = isset($this->values[$name]) ? $this->values[$name] : null;
        if ($this->show_labels) {
            $output = <<<EOT
            <div class="form-group">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <select multiple="" id="{$id}" name="{$name}" class="multi-select col-sm-9 col-xs-10">
EOT;
        }
        else {
            $output = <<<EOT
            <div class="form-group">
                <select multiple="" id="{$id}" name="{$name}" class="multi-select col-sm-11 col-xs-12">
EOT;
        }
        foreach($options as $id => $val) {
            if ($options_key != null) {
                $id = $val[$options_key];
            }
            if ($options_val != null) {
                $val = $val[$options_val];
            }
            if (in_array($val, $options_selected) || in_array($id, $options_selected) || $id == $value) {
                $output.= <<<EOT
                    <option value="{$id}" selected>{$val}</option>
EOT;
            }
            else {
                $output.= <<<EOT
                    <option value="{$id}">{$val}</option>
EOT;
            }
        }
        $output.= <<<EOT
                </select>
            </div>
EOT;
        return $output;
    }

    public function select($name, $id = null, $label = null, $options = array(), $options_key = null, $options_val = null, $option_selected = null) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $value = isset($this->values[$name]) ? $this->values[$name] : null;
        if ($this->show_labels) {
            $output = <<<EOT
            <div class="form-group">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <select id="{$id}" name="{$name}" class="single-select col-sm-9 col-xs-10">
EOT;
        }
        else {
            $output = <<<EOT
            <div class="form-group">
                <select id="{$id}" name="{$name}" class="single-select col-sm-11 col-xs-12">
EOT;
        }
        if ($this->include_empty_option) {
            $output.= <<<EOT
                    <option value="">-</option>
EOT;
        }
        foreach($options as $id => $val) {
            if ($options_key != null) {
                $id = $val[$options_key];
            }
            if ($options_val != null) {
                $val = $val[$options_val];
            }
            if ($val == $option_selected || $id == $value) {
                $output.= <<<EOT
                    <option value="{$id}" selected>{$val}</option>
EOT;
            }
            else {
                $output.= <<<EOT
                    <option value="{$id}">{$val}</option>
EOT;
            }
        }
        $output.= <<<EOT
                </select>
            </div>
EOT;
        return $output;
    }

    public function remoteSelect($name, $id = null, $label = null, $route = "", $multiple = false) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';

        $multiple = $multiple ? 'multiple: true,' : '';
        $this->script.= <<<EOT
            $("#{$id}").select2({
                allowClear: true,
                placeholder: "{$label}",
                minimumInputLength: 1,{$multiple}
                ajax: {
                    url: "{$route}",
                    dataType: 'json',
                    type: 'GET',
                    data: function (term, page) {
                        return {
                            q: term,
                        };
                    },
                    results: function (data, page) {
                        var items = {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name,
                                    slug: item.name,
                                    id: item._id
                                }
                            })
                        };
                        console.log(items);
                        return items;
                    }
                },
                dropdownCssClass: "bigdrop"
            });
EOT;
        if ($this->show_labels) {
            return <<<EOT
            <div class="form-group">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <input type="hidden" id="{$id}" name="{$name}" class="select2ajax bigdrop col-md-9">
            </div>
EOT;
        }
        else {
            return <<<EOT
            <div class="form-group">
                <input type="hidden" id="{$id}" name="{$name}" class="select2ajax bigdrop col-md-11">
            </div>
EOT;
        }
    }

    public function tagSelect($name, $id = null, $label = null) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $value = isset($this->values[$name]) ? ' value="' . $this->values[$name] . '"' : '';
        /*$this->script.= <<<EOT
            $("#{$id}").select2({
				tags:[""]
			});
EOT;*/
        if ($this->show_labels) {
            return <<<EOT
            <div class="form-group">
                <label for="{$id}" class="col-md-2 control-label">{$label}</label>
                <input type="hidden" id="{$id}" name="{$name}" class="select2tags col-md-9"{$value}>
            </div>
EOT;
        }
        else {
            return <<<EOT
            <div class="form-group">
                <input type="hidden" id="{$id}" name="{$name}" class="select2tags col-md-11"{$value}>
            </div>
EOT;
        }
    }

    public function social($name, $id = null, $label = null) {
        if ($id == null) $id = $name;
        if ($label === null) $label = ucfirst($name);
        if ($this->edit) $id = $id . '_edit';
        $value = '';//isset($this->values[$name]) ? ' value="' . $this->values[$name] . '"' : '';
        if ($this->show_labels) {
            return <<<EOT
          <div class="form-group">
             <label for="{$id}" class="col-md-2 control-label">{$label}</label>
             <div class="col-md-10">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-facebook-official"></i></span>
                            <input type="text" id="{$id}_f" name="{$name}_f" class="form-control" placeholder="Facebook"{$value}>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-twitter"></i></span>
                            <input type="text" id="{$id}_t" name="{$name}_t" class="form-control" placeholder="Twitter"{$value}>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-instagram"></i></span>
                            <input type="text" id="{$id}_i" name="{$name}_i" class="form-control" placeholder="Instagram"{$value}>
                        </div>
                    </div>
                </div>
            </div>
          </div>
EOT;
        } else {
            return <<<EOT
          <div class="form-group">
             <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-facebook-official"></i></span>
                    <input type="text" id="{$id}_f" name="{$name}_f" class="form-control" placeholder="Facebook"{$value}>
                </div>
            </div>
             <div class="col-md-4">
                <div class="input-group">
                   <span class="input-group-addon"><i class="fa fa-twitter"></i></span>
                    <input type="text" id="{$id}_t" name="{$name}_t" class="form-control" placeholder="Twitter"{$value}>
                </div>
            </div>
             <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-instagram"></i></span>
                    <input type="text" id="{$id}_i" name="{$name}_i" class="form-control" placeholder="Instagram"{$value}>
                </div>
            </div>
          </div>
EOT;
        }
    }

    public function hidden($name, $id = null, $classes = "", $value = null) {
        if ($id == null) $id = $name;
        if ($this->edit) $id = $id . '_edit';
        
        if (isset($this->values[$name])) {
            $value = ' value="' . $this->values[$name] . '"';
        }
        elseif ($value != null) {
            $value = ' value="' . $value . '"';
        }
        else {
            $value = '';
        }

        if ($classes != '') $classes = ' class="' . $classes . '"';

        return <<<EOT
        <input type="hidden" name="{$name}" id="{$id}"{$classes}{$value}>
EOT;
    }

    public function view($id, $label, $val = "", $fa_icon = null) {
        $fa_icon = $fa_icon != null ? '<i class="fa ' . $fa_icon . '"></i>' : '';
        return <<<EOT
            <div class="form-group">
                <label class="col-sm-3 control-label">
                    {$fa_icon}
                    {$label}
                </label>
                <div class="col-sm-9">
                    <div class="field-content" id="view_{$id}">{$val}</div>
                </div>
            </div>
EOT;
    }

    public function id($val = "", $editing = true) {
        $this->edit = $editing;
        return <<<EOT
            <input type="hidden" name="id" value="{$val}" class="record-id">
EOT;
    }

    public function submit($label = null, $class = 'btn-primary') {
        if ($label === null) $label = Lang::get('global.save');
        return <<<EOT
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn {$class}">{$label}</button>
                </div>
            </div>
EOT;
    }

    public function controlButtons($edit_lbl = null, $delete_lbl = null, $html = '') {
        if ($edit_lbl === null) $edit_lbl = Lang::get('global.edit');
        if ($delete_lbl === null) $delete_lbl = Lang::get('global.delete');
        $output = '<div class="col-sm-offset-2 col-xs-offset-0 col-sm-3 col-xs-12 btn-group" style="margin-bottom:10px">';
        if ($edit_lbl !== false) {
            $output .= <<<EOT
                <button type="submit" name="action_edit" class="btn btn-default">
                    <i class="fa fa-pencil"></i>
                    {$edit_lbl}
                </button>
EOT;
        }
        if ($delete_lbl !== false) {
            $output .= <<<EOT
                <button type="submit" name="action_delete" class="btn btn-danger">
                    <i class="fa fa-trash-o"></i>
                    {$delete_lbl}
                </button>
EOT;
        }
        $output .= <<<EOT
            </div>
            <div class="col-sm-7 col-xs-12">
                {$html}
            </div>
EOT;
        return $output;
    }

    public function dropDownButton($label, $options, $js_func_name, $id = null) {
        if ($id == null) $id = 'dropdownMenu1';
        $output = <<<EOT
            <div class="dropdown">
              <button class="btn btn-default dropdown-toggle" type="button" id="{$id}" data-toggle="dropdown" aria-expanded="true">
                <span class="caption">{$label}</span>
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu" role="menu" aria-labelledby="{$id}" id="{$id}_options">
EOT;
        foreach ($options as $key => $option) {
            if (is_array($option)) {
                $output.= '<li role="presentation"><a role="menuitem" tabindex="-1" href="javascript:;" menu-action="' . reset($option) . '">' . next($option) . '</a></li>';
            }
            else {
                $output.= '<li role="presentation"><a role="menuitem" tabindex="-1" href="javascript:;" menu-action="' . $key . '">' . $option . '</a></li>';
            }
        }
        $output.= <<<EOT
              </ul>
            </div>
EOT;

        $this->script.= <<<EOT
            $('#{$id}_options li a').click(function(e) {
                if (typeof {$js_func_name} == 'function') {
                    {$js_func_name}( $(this) );
                }
            });
EOT;

        return $output;
    }

    public function button($id, $label, $fa_icon = null) {
        $fa_icon = $fa_icon != null ? '<i class="fa ' . $fa_icon . '"></i>' : '';
        $output = <<<EOT
            <button id="{$id}" type="button" class="btn btn-default">
                {$fa_icon}
                {$label}
            </button>
EOT;
        return $output;
    }


    public static function searchResults($results, $field, $field2 = null, $badge = null, $badge_field = null, $badge_match = null) {
        $output = "";
        if (count($results)) {
            foreach ($results as $result) {
                $row = $result->$field;
                $id = $result->id;
                $output.= <<<EOT
                    <a class="list-group-item search-result" data-id="{$id}">{$row}
EOT;
                if ($badge != null && $badge_field != null) {
                    $badge_lbl = ($result->$badge_field == $badge_match || ($badge_match == null && $result->$badge_field == 1)) ? $badge : false;
                    if ($badge_lbl !== false) {
                        $output.= <<<EOT
                            &nbsp;<span class="badge">{$badge}</span>
EOT;
                    }
                }
                elseif ($badge_field != null) {
                    if ($badge_match != null) {
                        $output .= '&nbsp;<span class="badge">' . call_user_func($badge_match, $result->$badge_field) . '</span>';
                    }
                    else {
                        $output .= '&nbsp;<span class="badge">' . $result->$badge_field . '</span>';
                    }
                }
                if ($field2 != null) {
                    if (is_array($field2)) {
                        foreach ($field2 as $f) {
                            if (is_array($f)) {
                                if (!empty($result->$f[1])) {
                                    $output.= '<br><b>' . $f[0] . '</b>: ' . $result->$f[1];
                                }
                            }
                            else {
                                if (!empty($result->$f)) {
                                    $output.= '<br>' . $result->$f;
                                }
                            }
                        }
                    }
                    else {
                        $row2 = $result->$field2;
                        if ($field2 == 'descripcion') {
                            $output .= '<br><span class="text-muted">' . $row2 . '</span></b>';
                        }
                        else {
                            $output .= '<br><b>' . $row2 . '</b>';
                        }
                    }
                }
                $output.= '</a>';
            }
        }

        return $output;
    }


    public static function searchResultsRelational($results, $field, $rel_model, $rel_field) {
        $output = "";

        foreach ($results as $result) {
            $row = $result->$field;
            $id = $result->id;
            $output.= <<<EOT
                <a class="list-group-item search-result" data-id="{$id}">{$row}
EOT;
            $badge_lbl = $result->$rel_model->$rel_field;
            $output.= <<<EOT
                &nbsp;<span class="badge">{$badge_lbl}</span>
EOT;
            $output.= '</a>';
        }

        return $output;
    }

    public static function badge($label, $show = true) {
        if ($show) {
            return <<<EOT
            &nbsp;<span class="badge">{$label}</span>
EOT;
        }
        return '';
    }

    public function remainingTime($value, $refresh_interval = 1000, $id = null) {
        if ($id == null) {
            $id = 'a' . uniqid();
        }
        $this->script.= <<<EOT
            $('#{$id}').knob({
                min: 0,
                max: {$value} + 1,
                readOnly: true
            });

            var {$id} = setInterval(function() {
                var v = $('#{$id}').val();
                if (v > 0) {
                    $('#{$id}').val( v - 1 ).trigger('change');
                }
                if (v == 1) {
                    setTimeout(function() {
                        $('#{$id}').parent().fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }, 1000);
                    clearInterval({$id});
                }
            }, {$refresh_interval});
            
EOT;
        return <<<EOT
            <input id="{$id}" type="text" value="{$value}" class="dial">
EOT;
    }

    public function image($url, $alt = '') {
        return <<<EOT
<img src="{$url}" alt="{$alt}">
EOT;
    }

    public static function userStatus($nombre, $apellido, $atendidos, $pendientes, $avatar, $link, $id = 0) {
        $title = Functions::firstNameLastName($nombre, $apellido);
        $t = $atendidos + $pendientes;
        if ($t > 0) {
            $p_atendido = (int)(($atendidos / $t) * 100);
            $p_pendiente = 100 - $p_atendido; //(int)($pendientes / $t);
        }
        else {
            $p_atendido = 0;
            $p_pendiente = 0;
        }
        $atendidos_lbl = Functions::singlePlural(Lang::get('pacientes.done_singular'), Lang::get('pacientes.done_plural'), $atendidos);
        $pendientes_lbl = Functions::singlePlural(Lang::get('pacientes.pending_singular'), Lang::get('pacientes.pending_plural'), $pendientes);
        return <<<EOT
        <li id="user_status_{$id}"><!-- class="current"-->
            <a href="{$link}">
                <span class="image">
                    <img src="{$avatar}" alt="" />
                </span>
                <span class="title">
                    {$title}
                </span>
                <div class="progress">
                    <div class="progress-bar progress-bar-success" style="width: {$p_atendido}%">
                        <span class="sr-only">{$p_atendido}% Completado</span>
                    </div>
                    <!--div class="progress-bar progress-bar-warning" style="width: 20%">
                        <span class="sr-only">20% Complete (warning)</span>
                    </div-->
                    <div class="progress-bar progress-bar-danger" style="width: {$p_pendiente}%">
                        <span class="sr-only">{$p_pendiente}% Completado</span>
                    </div>
                </div>
                <span class="status">
                    <div class="field">
                        <span class="badge badge-green">{$atendidos}</span> {$atendidos_lbl}
                        <span class="pull-right fa fa-check"></span>
                    </div>
                    <!--div class="field">
                        <span class="badge badge-orange">3</span> in-progress
                        <span class="pull-right fa fa-adjust"></span>
                    </div-->
                    <div class="field">
                        <span class="badge badge-red">{$pendientes}</span> {$pendientes_lbl}
                        <span class="pull-right fa fa-list-ul"></span>
                    </div>
                </span>
            </a>
        </li>
EOT;
    }

    public static function equipmentStatus($nombre, $modelo, $avatar, $link, $id = 0) {
        $title = $nombre . Functions::encloseStr($modelo, ' - ', '');
        return <<<EOT
        <li id="equipment_{$id}">
            <a href="{$link}">
                <span class="image">
                    <img src="{$avatar}" alt="" />
                </span>
                <span class="title">
                    {$title}
                </span>
            </a>
        </li>
EOT;
    }

    public static function itemStatus($title, $atendidos, $pendientes, $link, $id = 0) {
        $t = $atendidos + $pendientes;
        if ($t > 0) {
            $p_atendido = (int)(($atendidos / $t) * 100);
            $p_pendiente = 100 - $p_atendido; //(int)($pendientes / $t);
        }
        else {
            $p_atendido = 0;
            $p_pendiente = 0;
        }
        $atendidos_lbl = Lang::get('global.done_plural');
        $pendientes_lbl = Lang::get('global.pending_plural');
        return <<<EOT
        <li id="item_status_{$id}"><!-- class="current"-->
            <a href="{$link}">
                <span class="title">
                    {$title}
                </span>
                <div class="progress">
                    <div class="progress-bar progress-bar-success" style="width: {$p_atendido}%">
                        <span class="sr-only">{$p_atendido}% Completado</span>
                    </div>
                    <div class="progress-bar progress-bar-danger" style="width: {$p_pendiente}%">
                        <span class="sr-only">{$p_pendiente}% Completado</span>
                    </div>
                </div>
                <span class="status">
                    <div class="field">
                        <span class="badge badge-green">{$atendidos}</span> {$atendidos_lbl}
                        <span class="pull-right fa fa-check"></span>
                    </div>
                    <div class="field">
                        <span class="badge badge-red">{$pendientes}</span> {$pendientes_lbl}
                        <span class="pull-right fa fa-list-ul"></span>
                    </div>
                </span>
            </a>
        </li>
EOT;
    }


    public function header($title, $total, $icon, $show_add_new = true) {
        $registros_lbl = Functions::singlePlural('registro', 'registros', $total);
        $output = <<<EOT
        <div class="row">
            <div class="col-sm-4">
                <div class="clearfix">
                    <h3 class="content-title pull-left">{$title}</h3>
                </div>
                <div class="description">{$title} registrados</div>
            </div>
            <div class="col-sm-8">
                <div class="dashbox panel panel-default">
                    <div class="panel-body">
                        <div class="panel-left red">
                            <i class="fa {$icon} fa-3x"></i>
                        </div>
                        <div class="panel-right">
                            <div class="pull-left">
                                <div id="total_records" class="number">{$total}</div>
                                <div class="title">{$registros_lbl}</div>
                            </div>
                            <div class="pull-left hidden-xs">
EOT;
        if ($show_add_new) $output .= <<<EOT
                                <button type="button" class="btn-add-new btn btn-primary btn-lg btn-custom">
                                    <span class="visible-sm" title="Agregar nuevo">
                                        <i class="fa fa-plus"></i>
                                    </span>
                                    <span class="hidden-sm">
                                        <i class="fa fa-plus"></i>
                                        Agregar nuevo
                                    </span>
                                </button>
EOT;
        $output .= <<<EOT
                            </div>
                            <!--span class="label label-success">
                                26% <i class="fa fa-arrow-up"></i>
                            </span-->
                        </div>
                    </div>
                </div>
                <div class="text-center">
EOT;
        if ($show_add_new) $output .= <<<EOT
                    <button type="button" class="btn-add-new btn btn-primary btn-lg btn-custom visible-xs">
                        <i class="fa fa-plus"></i>
                        Agregar nuevo
                    </button>
                </div>
            </div>
        </div>
EOT;
        return $output;
    }

    public function infoCountBox($fa_icon, $total, $label, $link) {
        return <<<EOT
        <a href="{$link}">
            <div class="dashbox panel panel-default">
                <div class="panel-body">
                    <div class="panel-left red">
                        <i class="fa {$fa_icon} fa-3x"></i>
                    </div>
                    <div class="panel-right">
                        <div class="number">{$total}</div>
                        <div class="title">{$label}</div>
                        <!--span class="label label-success">
                            26% <i class="fa fa-arrow-up"></i>
                        </span-->
                    </div>
                </div>
            </div>
        </a>
EOT;
    }

    public function pieChart($title, $value, $total = 100, $color = null, $id = null) {
        if ($total != 100 && $total > 0) {
            $percent = round($value * 100 / $total);
        }
        else {
            $percent = $value;
        }

        if ($id === null) {
            $id = 'chart_' . uniqid() . rand(0,100);
        }
        if ($color === null) {
            $color = printf('#%06x', rand(0,16777215));
        }
        $this->script .= <<<EOT
        $('#{$id}').easyPieChart({
            animate: 2000,
            easing: 'easeOutBounce',
            onStep: function(from, to, percent) {
                $(this.el).find('.percent').text(Math.round(percent)+"%");
            },
            lineWidth: 6,
            barColor: "{$color}"
        });
EOT;
        return <<<EOT
        <div id="{$id}" class="piechart" data-percent="{$percent}">
            <span class="percent"></span>
        </div>
        <a class="title">{$title}</a>
EOT;
    }

    public function lineChart($title, $fa_icon, $data, $key, $val, $tooltip_text = null, $id = null) {
        if ($id === null) {
            $id = 'chart_' . uniqid() . rand(0,100);
        }
        if ($tooltip_text === null) {
            $tooltip_text = $title;
        }
        $cur_month = date('n');
        for /*($i = 0; $i <= 11; $i++) {*/ ($i = 11; $i >= 0; $i--) {
            $months[$i] = $cur_month - $i;
            if ($months[$i] <= 0) {
                $months[$i] += 12;
            }
        }
        $plot = array();
        $month_labels = array();
        $data = Functions::arrayIt($data, $key, $val);
        $i = 1;
        foreach($months as $point) {
            if (isset($data[$point])) {
                $plot[] = "[{$i}, " . $data[$point] . "]";
            }
            else {
                $plot[] = "[{$i}, 0]";
            }
            $month_labels[$i - 1] = '"' . Functions::monthName($point) . '"';
            $i++;
        }
        $plot = implode(', ', $plot);
        $month_labels = implode(', ', $month_labels);

        $this->script .= <<<EOT
        var {$id}_likes = [{$plot}];
        var {$id}_point = [{$month_labels}];
        
        var {$id}_plot = $.plot($("#{$id}"),
               [ { data: {$id}_likes} ], {
                   series: {
                       label: "{$tooltip_text}",
                       lines: { 
                            show: true,
                            lineWidth: 3, 
                            fill: false
                       },
                       points: { 
                            show: true, 
                            lineWidth: 3,
                            fill: true,
                            fillColor: "#9EB37A" 
                       },   
                       shadowSize: 0
                   },
                   grid: { hoverable: true, 
                           clickable: true, 
                           tickColor: "rgba(255,255,255,.15)",
                           borderColor: "rgba(255,255,255,0)"
                         },
                   colors: ["#fff"],
                   xaxis: {
                        font: {
                            color: "#fff"
                        },
                        ticks:6, 
                        tickDecimals: 0, 
                        //tickColor: "#9EB37A",
                        tickFormatter: function(val, axis) { return (val > 0 && val <= 12) ? {$id}_point[val - 1] : ''; }
                   },
                   yaxis: {
                        font: {
                            color: "#fff"
                        },
                        ticks:4, 
                        tickDecimals: 0,
                        autoscaleMargin: 0.000001
                   },
                   legend: {
                        show: false
                   }
                 });

        function showTooltip(x, y, contents) {
            $('<div id="tooltip">' + contents + '</div>').css( {
                position: 'absolute',
                display: 'none',
                top: y + 5,
                left: x + 5,
                border: '1px solid #fdd',
                padding: '2px',
                'background-color': '#dfeffc',
                opacity: 0.80
            }).appendTo("body").fadeIn(200);
        }

        var previousPoint = null;
        $("#{$id}").bind("plothover", function (event, pos, item) {
            $("#x").text(pos.x.toFixed(2));
            $("#y").text(pos.y.toFixed(2));

                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;

                        $("#tooltip").remove();
                        var x = Math.round(item.datapoint[0].toFixed(2)),
                            y = Math.round(item.datapoint[1].toFixed(2));

                        showTooltip(item.pageX, item.pageY,
                                    y + ' ' + item.series.label);
                    }
                }
                else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
        });
EOT;

        return <<<EOT
        <div class="box solid grey">
            <div class="box-title">
                <h4><i class="fa {$fa_icon}"></i>{$title}</h4>
                <!--div class="tools">
                    <span class="label label-danger">
                        20% <i class="fa fa-arrow-up"></i>
                    </span>
                    <a href="#box-config" data-toggle="modal" class="config">
                        <i class="fa fa-cog"></i>
                    </a>
                    <a href="javascript:;" class="reload">
                        <i class="fa fa-refresh"></i>
                    </a>
                    <a href="javascript:;" class="collapse">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                    <a href="javascript:;" class="remove">
                        <i class="fa fa-times"></i>
                    </a>
                </div-->
            </div>
            <div class="box-body">
                <div id="{$id}" style="height:240px;"></div>
            </div>
        </div>
EOT;
    }

    public static function notificationItem($item) {
        $action = explode(' ', $item->accion);

        switch (reset($action)) {
            case 'Area':
                $icon = 'fa-cube';
                break;
            case 'Cita':
                $icon = 'fa-calendar-o';
                break;
            case 'Consultorio':
                $icon = 'fa-cube';
                break;
            case 'Disponibilidad':
                $icon = 'fa-calendar-o';
                break;
            case 'Doctor':
                $icon = 'fa-user-md';
                break;
            case 'Equipo':
                $icon = 'fa-plug';
                break;
            case 'Nota':
                $icon = 'fa-comment';
                break;
            case 'Paciente':
                $icon = 'fa-user';
                break;
            case 'Servicio':
                $icon = 'fa-check-square-o';
                break;
            case 'ServicioCategoria':
                $icon = 'fa-check-square-o';
                break;
            case 'User':
                $icon = 'fa-key';
                break;
            default:
                $icon = 'fa-info-circle';
        }

        switch (next($action)) {
            case 'created':
                $type = 'primary';
                break;
            case 'edited':
                $type = 'warning';
                break;
            case 'deleted':
                $type = 'danger';
                break;
            default:
                $type = 'default';
        }

        $user = User::find($item->usuario_id);
        if ($user) {
            $user_name = $user->nombre;
            $user = $user->paciente;
            if ($user) {
                $user_name = Functions::firstNameLastName($user->nombre, $user->apellido);
            }
        }
        else {
            $user_name = Lang::get('log.the_user');
        }

        $text = $user_name . ' ' . Lang::get('log.' . $item->accion);
        $date = $item->updated_at;
        $url = URL::route('admin_log_item', array('id' => $item->id));
        return <<<EOT
        <li>
            <a class="notification" data-id="{$item->id}" href="{$url}">
                <span class="label label-{$type}"><i class="fa {$icon}"></i></span>
                <span class="body">
                    <span class="message">{$text}</span>
                    <span class="time">
                        <i class="fa fa-clock-o"></i>
                        <span>{$date}</span>
                    </span>
                </span>
            </a>
        </li>
EOT;
    }


    public function script($jquery = false) {
        if (!$jquery) {
            return $this->script;
        }
        else {
            return <<<EOT
                $(document).ready(function() {
                    {$this->script}
                });
EOT;
        }
    }

    public function halfPanelOpen($first = false, $col = 6) {
        return ($first ? '<div class="row">' : '') . '<div class="col-md-' . $col . '">';
    }

    public function halfPanelClose($last = false) {
        return '</div>' . ($last ? '</div>' : '');
    }


    public function panelOpen($name, $label, $fa_icon, $classes = "primary", $tools = null) {
        $head = <<<EOT
            <div id="{$name}_panel" class="box border {$classes}">
            <div class="box-title">
                <h4>
                    <i id="{$name}_icon" class="fa {$fa_icon} panel_icon"></i>
                    <span id="{$name}_lbl" class="panel_lbl">{$label}</span>
                </h4>
                <div class="tools">
EOT;
        if (is_array($tools)) {
            foreach ($tools as $tool) {
                switch ($tool) {
                    case 'config':
                        $head .= <<<EOT
                    <a href="#box-config-{$name}" data-toggle="modal" class="config">
                        <i class="fa fa-cog"></i>
                    </a>
EOT;
                        break;

                    case 'refresh':
                        $head .= <<<EOT
                    <a href="javascript:;" class="reload">
                        <i class="fa fa-refresh"></i>
                    </a>
EOT;
                        break;

                    case 'collapse':
                        $head .= <<<EOT
                    <a href="javascript:;" class="collapse">
                        <i class="fa fa-chevron-up"></i>
                    </a>
EOT;
                        break;

                    case 'remove':
                        $head .= <<<EOT
                    <a href="javascript:;" class="remove">
                        <i class="fa fa-times"></i>
                    </a>
EOT;
                        break;



                }
            }
        }
        return <<<EOT
                {$head}
                </div>
            </div>
            <div class="box-body">
EOT;

    }

    public function panelClose() {
        return <<<EOT
                <div class="clearfix"></div>
            </div>
        </div>
EOT;

    }

    
    public function modalOpen($id, $title) {
        return <<<EOT
            <div class="modal fade" id="{$id}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">{$title}</h4>
                        </div>
                        <div class="modal-body">
EOT;

    }

    public function modalClose($ok = null, $close = null, $footer = true, $before_footer_html = null) {
        if ($ok === null) $ok = Lang::get('global.ok');
        if ($close === null) $close = Lang::get('global.close');
        $output = '';
        if ($footer) {
            if ($before_footer_html == null) {
                $output = <<<EOT
                            <div class="alert alert-danger alert-dismissible modal-alert hidden" role="alert">
                              <button type="button" class="close" data-hide="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              <i class="fa fa-exclamation-circle"></i>&nbsp; 
                              <span class="sr-only">Error:</span>
                              <span class="msg"></span>
                            </div>
EOT;
            }
            else {
                $output = $before_footer_html;
            }
            $output .= <<<EOT
                        </div>
                        <div class="modal-footer">
EOT;
            if ($close !== false) $output .= <<<EOT
                            <button type="button" class="btn btn-default" data-dismiss="modal">{$close}</button>
EOT;
            if ($ok !== false) $output .= <<<EOT
                            <button type="button" class="btn btn-primary modal-btn-ok">{$ok}</button>
EOT;
            $output .= <<<EOT
                        </div>
EOT;
        }
        else $output.= '</div>';
        return $output . <<<EOT
                    </div>
                </div>
            </div>
EOT;
    }


    public function accordionOpen($id, $group = 0) {
        $this->accordion_id = $id;
        return <<<EOT
            <div class="panel-group filter-accordion" id="{$id}" attr-id="{$group}" role="tablist" aria-multiselectable="true">
EOT;
    }

    public function accordionItemOpen($title, $show_eye = false, $eye_id = 0, $open = true) {
        $this->accordion_count = (int)$this->accordion_count + 1;
        if ($show_eye !== false) {
            $state = $show_eye == 1 ? 'eye' : 'eye-slash';
            $state_class = $show_eye ? '' : ' not-visible';
            $eye = <<<EOT
                <a href="#" class="toggle-visibility pull-right{$state_class}" data-id="{$eye_id}">
                    <i class="fa fa-{$state}"></i>
                </a>
EOT;
        }
        else {
            $eye = '';
        }
        $open_attr = $open ? '' : ' class="collapsed"';
        $open_class = $open ? ' in' : '';
        return <<<EOT
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingOne">
                        <h4 class="panel-title">
                            <a{$open_attr} data-toggle="collapse" data-parent="#{$this->accordion_id}" href="#{$this->accordion_id}_{$this->accordion_count}" aria-expanded="true" aria-controls="collapseOne">
                                {$title}
                            </a>
                            {$eye}
                        </h4>
                    </div>
                    <div id="{$this->accordion_id}_{$this->accordion_count}" class="panel-collapse collapse{$open_class}" role="tabpanel" aria-labelledby="headingOne">
EOT;
    }

    public function accordionItemClose() {
        return <<<EOT
                    </div>
                </div>
EOT;
    }

    public function accordionClose() {
        return <<<EOT
            </div>
EOT;
    }

}