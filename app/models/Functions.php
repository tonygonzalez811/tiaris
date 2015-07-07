<?php
/**
 * Created by PhpStorm.
 * User: Alfredo
 * Date: 06/05/14
 * Time: 05:05 PM
 */

class Functions {

    public static function monthName($index, $long = true) {
      if ($index <= 0) return '';
      if ($long) {
        $months = array(
            Lang::get('global.jan_l'),
            Lang::get('global.feb_l'),
            Lang::get('global.mar_l'),
            Lang::get('global.apr_l'),
            Lang::get('global.may_l'),
            Lang::get('global.jun_l'),
            Lang::get('global.jul_l'),
            Lang::get('global.aug_l'),
            Lang::get('global.sep_l'),
            Lang::get('global.oct_l'),
            Lang::get('global.nov_l'),
            Lang::get('global.dec_l')
        );
      }
      else {
        $months = array(
            Lang::get('global.jan_s'),
            Lang::get('global.feb_s'),
            Lang::get('global.mar_s'),
            Lang::get('global.apr_s'),
            Lang::get('global.may_s'),
            Lang::get('global.jun_s'),
            Lang::get('global.jul_s'),
            Lang::get('global.aug_s'),
            Lang::get('global.sep_s'),
            Lang::get('global.oct_s'),
            Lang::get('global.nov_s'),
            Lang::get('global.dec_s')
        );
      }
      return $months[$index - 1];
    }

    public static function longDateFormat($date, $show_time=false, $convert_to_time=true) {
        if ($convert_to_time) {
          $date = strtotime($date);
        }
        if ($date === false) return "";
        $week = array(
            Lang::get('global.sun_l'),
            Lang::get('global.mon_l'),
            Lang::get('global.tue_l'),
            Lang::get('global.wed_l'),
            Lang::get('global.thu_l'),
            Lang::get('global.fri_l'),
            Lang::get('global.sat_l')
        );
        
        return Lang::get('global.long_date', array(
            'semana' => $week[date('w', $date)],
            'dia' => date('d', $date),
            'mes' => Functions::monthName(date('n', $date)),
            'ano' => date('Y', $date)
        )) . ($show_time ? (', ' . date(' h:i a', $date)) : '');
    }

    public static function shortDateFormat($date, $show_week=false, $show_time=false) {
        $date = strtotime($date);
        if ($date === false) return "";
        $week = array(
            Lang::get('global.sun_s'),
            Lang::get('global.mon_s'),
            Lang::get('global.tue_s'),
            Lang::get('global.wed_s'),
            Lang::get('global.thu_s'),
            Lang::get('global.fri_s'),
            Lang::get('global.sat_s')
        );
        
        return Lang::get($show_week ? 'global.short_date_week' : 'global.short_date', array(
            'semana' => $week[date('w', $date)],
            'dia' => date('d', $date),
            'mes' => Functions::monthName(date('n', $date)),
            'ano' => date('Y', $date)
        )) . ($show_time ? date(' h:i a', $date) : '');
    }

    public static function justTime($time, $ampm = true, $convert_to_time = true, $minimum = false) {
        $time = date($ampm ? 'h:i A' : 'H:i', $convert_to_time ? strtotime($time) : $time);
        if ($minimum) {
            $time = str_replace(' PM', 'p', str_replace(' AM', 'a', str_replace(':00', '', $time)));
            if (substr($time, 0, 1) == '0') {
                $time = substr($time, 1);
            }
        }
        return $time;
    }

    public static function ampmto24($time) {
        if (empty($time)) return null;
        $time = explode(' ', $time);
        $ampm = strtoupper($time[1]);
        $time = explode(':', $time[0], 3);
        if (count($time) > 1) {
            if ($time[0] == 12 && $ampm == 'AM') {
              $time[0] = 0;
            }
            else {
              $time[0] = (int)$time[0] + (($ampm == 'PM' && $time[0] != 12) ? 12 : 0);
            }
            return ($time[0] < 10 ? '0' : '') . $time[0] . ':' . $time[1];
        }
        return '';
    }

    public static function explodeDateTime($datetime, $ignore_time = false) {
        $ret = array();
        //fecha
        $date = explode('-', $datetime, 3);
        $date = array_map('intval', $date);
        $ret['year'] = $date[0];
        $ret['month'] = $date[1];
        $ret['day'] = $date[2];

        //hora
        if (!$ignore_time) {
            $start = explode(':', Functions::justTime($datetime, false, 3));
            if (count($start) >= 2) {
                $start = array_map('intval', $start);
                $ret['hour'] = $start[0];
                $ret['minutes'] = $start[1];
            }
        }

        return $ret;
    }

    public static function minToHours($min) {
        $hours = 0;
        if ($min > 59) {
            $hours = intval($min / 60);
            $min -= $hours * 60;
        }
        return ($hours ? (Functions::singlePlural(Lang::get('global.hour'), Lang::get('global.hours'), $hours, true) . ($min ? ' ' : '')) : '') .
                 ($min ?  Functions::singlePlural(Lang::get('global.minute'), Lang::get('global.minutes'), $min, true) : '');
    }

    public static function addMinutes($start, $minutes, $return_format = 'Y-m-d H:i:s') {
        if (strlen(preg_replace('/[0-9]/', '', $start)) > 0) {
            $start = strtotime($start);
        }
        $time = strtotime('+' . $minutes . ' minutes', $start);
        if ($return_format !== false) {
            return date($return_format, $time);
        }
        return $time;
    }

    /**
     * Checks if a time (time2) is between the interval specified from another time (time1)
     * @param $time1
     * @param $time2
     * @param int $interval
     * @return bool
     */
    public static function compareHoursInInverval($time1, $time2, $interval = 30) {
        //if ($time1 < $time2) return false;
        $h1 = (int)date('G', $time1); //G = 0..23 (no leading zero as opposed to H)
        $h2 = (int)date('G', $time2);
        $m1 = (int)date('i', $time1);
        $m2 = (int)date('i', $time2);
        /*if (($m1 < $m2 && $h1 <= $h2) || ($m1 = $m2 && $h1 < $h2)) {
            return false;
        }*/
        if ($h1 == $h2) { //same hour
            if ($m1 == $m2) return true; //same minutes
            $diff = abs($m2 - $m1); // ex:  8:00 -- 8:29 --> diff = 29 (ok)  |  8:00 -- 8:30 --> diff = 30 (bad)
            if ($diff < $interval) return true;
        }
        return false;
    }


    public static function ageFromDate($date) {
      $date = new DateTime($date);
      $now = new DateTime();
      $interval = $now->diff($date);
      return $interval->y;
    }

    public static function inactiveIf($content, $inactive) {
      return ($inactive ? '<span class="text-muted" style="text-decoration:line-through">' : '') . $content . ($inactive ? '</span>' : '');
    }

    public static function wrapWithSpanIf($to_be_wrapped, $wrap, $class = '', $find_str = '', $find_wrap_open = '<b><i>', $find_wrap_close = '</i></b>') {
        $to_be_wrapped = Functions::wrapSubstringIn($find_str, $to_be_wrapped, $find_wrap_open, $find_wrap_close);
        if ($wrap) {
            if ($class != "") $class = ' class="' . $class . '"';
            return '<span' . $class . '>' . $to_be_wrapped . '</span>';
        }
        else return $to_be_wrapped;
    }

    public static function wrapSubstringIn($substring, $in, $wrap_start = '<b><i>', $wrap_end = '</i></b>') {
        if (strlen($substring)) {
            $pos = stripos($in, $substring);
            if ($pos !== false) {
                $in = substr_replace($in, $wrap_end, $pos + strlen($substring), 0);
                $in = substr_replace($in, $wrap_start, $pos, 0);
            }
        }
        return $in;
    }

    /**
     * Replaces new line for a html br tag. PHP's function only adds a br but doesn't remove the new line character
     * @param $str
     * @param string $nl_tag
     * @return string
     */
    public static function nl2br($str, $nl_tag = '<br />') {
      return str_replace(array("\r\n", "\r", "\n", "\n\r"), $nl_tag, $str);
    }

    /**
     * Removes any character that is not a letter, number or any of these: @ . _ -
     * @param $str
     * @return mixed
     */
    public static function noWeirdChars($str) {
        return preg_replace('/[^ \w@._\-áéíóúÁÉÍÓÚ]+/', '', $str);
    }

    /**
     * simple method to encrypt or decrypt a plain text string
     * initialization vector(IV) has to be the same when encrypting and decrypting
     * PHP 5.4.9
     *
     * this is a beginners template for simple encryption decryption
     * before using this in production environments, please read about encryption
     *
     * @param string $action: can be 'encrypt' or 'decrypt'
     * @param string $string: string to encrypt or decrypt
     *
     * @return string
     */
    public static function encrypt_decrypt($action, $string) {
        $output = false;

        $encrypt_method = "AES-256-CBC";
        $secret_key = '4#7$./jk3@fdk-fd4ñ0?maf&lfrd';
        $secret_iv = 'U1kUEYSRsbXYAidzIBAy';

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        }
        else if( $action == 'decrypt' ){
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }

    /**
     * Help function to be used in form fields (value property)
     * @param $key
     * @param string $default
     * @return string
     */
    public static function retrieve($key, $default = "") {
        $val = Input::old($key);
        if (isset($val)) {
            return $val;
        }
        return $default;
    }

    /**
     * Capitalizes the first letter of every word except the word 'de' if cap_de is false
     * @param $val
     * @param $cap_de
     * @return string
     */
    public static function capitalize($val, $cap_de = true) {
        /*$val = str_replace(' De ',' de ',ucwords(strtolower( $val )));*/
        $val = ucwords(mb_strtolower( $val, 'UTF-8' ));
        if (!$cap_de) {
            $val = str_replace(' De ', ' de ', $val);
            if (substr($val,0,3) == 'De ') $val = preg_replace('/De /','de ',$val,1);
        }
        return trim( $val );
    }

    public static function singlePlural($single, $plural, $quantity, $append_quantity = false) {
        return ($append_quantity ? ($quantity . ' ') : '') . ($quantity == 1 ? $single : $plural);
    }

    /**
     * Removes all the html tags (to prevent scripts and odd behavior)
     * @param $str
     * @return string
     */
    public static function noTags($str) {
        return trim( preg_replace('/<(.*?)>/', '', $str) );
    }

    /**
     * Breaks the number and separates it with parenthesis and dots. Ex: //04249440972 --> (0424) 944.09.72
     * @param $str
     * @return string
     */
    public static function formatPhone($str) {
        $str = preg_replace("/[^0-9]/", "", $str);
        $len = strlen($str);
        if ($len == 11) {
            return '(' . substr($str, 0, 4) . ') ' . substr($str, 4, 3) . '.' . substr($str, 7, 2) . '.' . substr($str, 9);
        }
        return $str;
    }

    /**
     * Adds the 'at' symbol before the string if not present
     * @param $str
     * @return string
     */
    public static function formatSocial($str) {
        $str = str_replace(' ', '', $str);
        if (strlen($str) == 0) return '';
        if (substr($str, 0, 1) != '@') {
          $str = '@' . $str;
        }
        return strtolower($str);
    }

    /**
     * Removes all but the allowed phone characters.
     * @param $str
     * @return mixed
     */
    public static function onlyPhoneChars($str) {
        return preg_replace('/[^0-9()+-\.\/]/', '', $str);
    }

    /**
     * Removes all but the allowed email characters.
     * @param $str
     * @return mixed
     */
    public static function onlyEmailChars($str) {
        return strtolower( preg_replace('/[^a-zA-Z0-9_\-\.@]/', '', $str) );
    }

    /**
     * Removes all but letters.
     * @param $str
     * @return mixed
     */
    public static function onlyLetters($str) {
        $str = preg_replace('/[^\pL\s]/', '', $str);
        return trim( preg_replace('!\s+!', ' ', $str) );
    }

    /**
     * Returns an array with the translated values of the passed array
     * @param $lang_file
     * @param $array
     * @param null $field_name
     * @param null $field_id
     * @return array
     */
    public static function langArray($lang_file, $array, $field_name = null, $field_id = null) {
        $return_array = array();
        if (is_array($array)) {
            foreach ($array as $key => $a) {
                if ($field_name == null) {
                    if ($field_id == null) {
                        $return_array[$key] = Lang::get($lang_file . '.' . $a);
                    }
                    else {
                        $return_array[$a[$field_id]] = Lang::get($lang_file . '.' . $a);
                    }
                }
                else {
                    if ($field_id == null) {
                        $return_array[$key] = Lang::get($lang_file . '.' . $a[$field_name]);
                    }
                    else {
                        $return_array[$a[$field_id]] = Lang::get($lang_file . '.' . $a[$field_name]);
                    }
                }
            }
        }
        return $return_array;
    }

    public static function arrayIt($objects, $key, $value, $rel = null) {
        $arr = array();
        $has_rel = is_array($rel) && count($rel) > 1;
        foreach($objects as $item) {
            if ($has_rel) {
              $rel_model = $rel[0];
              $rel_field = $rel[1];
              $rel_value = $item->$rel_model->$rel_field;
              //$arr[$item->$key] = $item->$value . ' &nbsp; <span class="text-muted">' . $rel_value . '</span>';
              $arr[$item->$key] = $item->$value . (!empty($rel_value) ? (' - ' . $rel_value) : '');
            }
            elseif ($rel !== null) {
              $arr[$item->$key] = $item->$value . (!empty($item->$rel) ? (' - ' . $item->$rel) : '');
            }
            else {
              $arr[$item->$key] = $item->$value;
            }
        }
        return $arr;
    }

    public static function firstNameLastName($fname, $lname, $initial_lname = false) {
        $fname = explode(' ', $fname);
        $lname = $initial_lname ? substr($lname, 0, 1) : explode(' ', $lname);
        if (!$initial_lname && strtolower($lname[0]) == 'de') {
            return ucfirst(mb_strtolower(reset($fname))) . ' ' . ($initial_lname ? strtoupper($lname) : ('de ' . ucfirst(mb_strtolower(next($lname)))));
        }
        return ucfirst(mb_strtolower(reset($fname))) . ' ' . ($initial_lname ? strtoupper($lname) : ucfirst(mb_strtolower(reset($lname))));
    }

    public static function remainingTime($date_str, $type = null) {
        //date_default_timezone_set('UTC');
        //date_default_timezone_set('America/Caracas');
        $now = new DateTime('now', new DateTimeZone( Config::get('app.timezone') ));
        $future_date = new DateTime($date_str, new DateTimeZone( Config::get('app.timezone') ));

        $interval = $future_date->diff($now, true);

        switch ($type) {
            case 'days':
                return $interval->d;
                break;

            case 'hours':
                return $interval->h;
                break;

            case 'minutes':
                return $interval->i;
                break;

            case 'seconds':
                return $interval->s;
                break;

            case 'all':
                if ($now > $future_date) return '';
                if ($interval->d > 6) {
                    $week = intval($interval->d / 7);
                    $interval->d -= $week * 7;
                }
                else $week = false;
                return 
                    ($interval->y ? (Functions::singlePlural(Lang::get('global.year'), Lang::get('global.years'), $interval->y, true) . ' ') : '') .
                    ($interval->m ? (Functions::singlePlural(Lang::get('global.month'), Lang::get('global.months'), $interval->m, true) . ' ') : '') .
                    ($week ? (Functions::singlePlural(Lang::get('global.week'), Lang::get('global.weeks'), $week, true) . ' ') : '') .
                    ($interval->d ? (Functions::singlePlural(Lang::get('global.day'), Lang::get('global.days'), $interval->d, true) . ' ') : '') .
                    ($interval->h ? (Functions::singlePlural(Lang::get('global.hour'), Lang::get('global.hours'), $interval->h, true) . ' ') : '') .
                    Functions::singlePlural(Lang::get('global.minute'), Lang::get('global.minutes'), $interval->i, true); //. " " .
                    //Functions::singlePlural(Lang::get('global.second'), Lang::get('global.seconds'), $interval->s, true);
                break;

            default:
                return $interval;
        }
    }

    /**
     * Returns a the passed string enclosed in two strings
     * @param $str
     * @param string $before
     * @param string $after
     * @return string
     */
    public static function encloseStr($str, $before = ' (', $after = ')') {
        if (!empty($str)) {
            return $before . $str . $after;
        }
        return '';
    }

    /**
     * easy image resize function
     * @param  $file - file name to resize
     * @param  $string - The image data, as a string
     * @param  $width - new image width
     * @param  $height - new image height
     * @param  $proportional - keep image proportional, default is no
     * @param  $output - name of the new file (include path if needed)
     * @param  $delete_original - if true the original image will be deleted
     * @param  $use_linux_commands - if set to true will use "rm" to delete the image, if false will use PHP unlink
     * @param  $quality - enter 1-100 (100 is best quality) default is 100
     * @return boolean|resource
     */
    public static function smart_resize_image($file, $string = null, $width = 0, $height = 0, $proportional = false, $output = 'file', $delete_original = true, $use_linux_commands = false, $quality = 100) {
        if ( $height <= 0 && $width <= 0 ) return false;
        if ( $file === null && $string === null ) return false;

        # Setting defaults and meta
        $info                         = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
        $image                        = '';
        $final_width                  = 0;
        $final_height                 = 0;
        list($width_old, $height_old) = $info;
        $cropHeight = $cropWidth = 0;

        # Calculating proportionality
        if ($proportional) {
          if      ($width  == 0)  $factor = $height/$height_old;
          elseif  ($height == 0)  $factor = $width/$width_old;
          else                    $factor = min( $width / $width_old, $height / $height_old );

          $final_width  = round( $width_old * $factor );
          $final_height = round( $height_old * $factor );
        }
        else {
          $final_width = ( $width <= 0 ) ? $width_old : $width;
          $final_height = ( $height <= 0 ) ? $height_old : $height;
          $widthX = $width_old / $width;
          $heightX = $height_old / $height;
          
          $x = min($widthX, $heightX);
          $cropWidth = ($width_old - $width * $x) / 2;
          $cropHeight = ($height_old - $height * $x) / 2;
        }

        # Loading image to memory according to type
        switch ( $info[2] ) {
          case IMAGETYPE_JPEG:  $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);  break;
          case IMAGETYPE_GIF:   $file !== null ? $image = imagecreatefromgif($file)  : $image = imagecreatefromstring($string);  break;
          case IMAGETYPE_PNG:   $file !== null ? $image = imagecreatefrompng($file)  : $image = imagecreatefromstring($string);  break;
          default: return false;
        }
        
        
        # This is the resizing/resampling/transparency-preserving magic
        $image_resized = imagecreatetruecolor( $final_width, $final_height );
        if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
          $transparency = imagecolortransparent($image);
          $palletsize = imagecolorstotal($image);

          if ($transparency >= 0 && $transparency < $palletsize) {
            $transparent_color  = imagecolorsforindex($image, $transparency);
            $transparency       = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
            imagefill($image_resized, 0, 0, $transparency);
            imagecolortransparent($image_resized, $transparency);
          }
          elseif ($info[2] == IMAGETYPE_PNG) {
            imagealphablending($image_resized, false);
            $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
            imagefill($image_resized, 0, 0, $color);
            imagesavealpha($image_resized, true);
          }
        }
        imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);
        
        # Taking care of original, if needed
        if ( $delete_original ) {
          if ( $use_linux_commands ) exec('rm '.$file);
          else @unlink($file);
        }

        # Preparing a method of providing result
        switch ( strtolower($output) ) {
          case 'browser':
            $mime = image_type_to_mime_type($info[2]);
            header("Content-type: $mime");
            $output = NULL;
          break;
          case 'file':
            $output = $file;
          break;
          case 'return':
            return $image_resized;
          break;
          default:
          break;
        }
        
        # Writing image according to type to the output destination and image quality
        switch ( $info[2] ) {
          case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;
          case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, $quality);   break;
          case IMAGETYPE_PNG:
            $quality = 9 - (int)((0.9*$quality)/10.0);
            imagepng($image_resized, $output, $quality);
            break;
          default: return false;
        }

        return true;
    }
}