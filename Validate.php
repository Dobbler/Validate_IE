<?php
/**
Experimental
*/

define('VAL_NUM',          '0-9');
define('VAL_SPACE',        '\s');
define('VAL_ALPHA_LOWER',  'a-z');
define('VAL_ALPHA_UPPER',  'A-Z');
define('VAL_ALPHA',        VAL_ALPHA_LOWER . VAL_ALPHA_UPPER);
define('VAL_EALPHA_LOWER', VAL_ALPHA_LOWER . '����������������������');
define('VAL_EALPHA_UPPER', VAL_ALPHA_UPPER . '����������������������');
define('VAL_EALPHA',       VAL_EALPHA_LOWER . VAL_EALPHA_UPPER);
define('VAL_PUNCTUATION',  VAL_SPACE . ',;:"\'\?!\(\)');
define('VAL_NAME',         VAL_EALPHA . VAL_SPACE . "'");
define('VAL_STREET',       VAL_NAME . "/\\��");

class Validate
{

    function Validate() {
    }

    /**
    * @param mixed $decimal The decimal char or false when decimal not allowed
    */
    function number($number, $decimal = null, $dec_prec = null, $min = null, $max = null)
    {
        if ( is_array( $number ) )
            extract($number);

        $dec_prec   = $dec_prec ? "{1,$dec_prec}" : '+';
        $dec_regex  = $decimal  ? "[$decimal][0-9]$dec_prec" : '';
        if (!preg_match("|^[-+]?\s*[0-9]+($dec_regex)?\$|", $number)) {
            return false;
        }
        if ($decimal != '.') {
            $number = strtr($number, $decimal, '.');
        }
        $number = (float)$number;
        if ($min !== null && $min > $number) {
            return false;
        }
        if ($max !== null && $max < $number) {
            return false;
        }
        return true;
    }

    function email($email, $check_domain = false)
    {
        if ( is_array( $email ) )
            extract($email);

        if (ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'.'@'.
                 '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
                 '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email))
        {
            if ($check_domain && function_exists('checkdnsrr')) {
                list (, $domain)  = explode('@', $email);
                if (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')) {
                    return true;
                }
                return false;
            }
            return true;
        }
        return false;
    }

    /**
    * @param string $format Ex: VAL_NUM . VAL_ALPHA
    */
    function string($string, $format = null, $min_lenght = 0, $max_lenght = 0)
    {
        if ( is_array( $string ) )
            extract($string);

        if ($format && !preg_match("|^[$format]*\$|s", $string)) {
            return false;
        }
        if ($min_lenght && strlen($string) < $min_lenght) {
            return false;
        }
        if ($max_lenght && strlen($string) > $max_lenght) {
            return false;
        }
        return true;
    }

    // move outside
    /*
    function date($date, $format, $min_range = false, $max_range = false)
    {
        if (!include_once 'Date/Date.php') {
            trigger_error(' no date class found ..', E_USER_ERROR);
            return false;
        }
    }
    */
    
    function url($url, $domain_check = false)
    {
        if ( is_array( $url ) )
            extract($url);

        $purl = parse_url($url);
        if (preg_match('|^http$|i', @$purl['scheme']) && !empty($purl['host'])) {
            if ($domain_check && function_exists('checkdnsrr')) {
                if (checkdnsrr($purl['host'], 'A')) {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    
    /*
        To Do :
        External calls based on commented Date methods

        @param  array   $data Ex: array('name'=>'toto','email'='toto@thing.info');
        @param  array   $opt   Contains the validation type and all parameters used in.
                            'type' is not optional
                            others validations properties must have the same name as the function
                            parameters.
                            Ex: array('toto'=>array('type'=>'string','format'='toto@thing.info','min_lenght'=>5));
        @param  boolean $remove if set, the invalid elements in data will be removed ( Not yet implemented)

        @return array   value name => true|false    the value name comes from the data key
    */
    function multiple( &$data, &$val_type, $remove = false  ) {
        $core_methods = array('number','string','email','url');

        if( !is_null($data) && !is_null($val_type)  ){

            $keys   = array_keys( $data );
            foreach( $keys as $var_name ) {
                if (isset( $val_type[$var_name] ))
                    $opt    = $val_type[$var_name];

                if ( isset( $opt['type'] ) && $opt['type']!="" )
                    if ( in_array( $opt['type'], $core_methods ) ){
                        $opt[$opt['type']] = $data[$var_name];
                        $valid[$var_name] = Validate::$opt['type']($opt);
                    } else {
                        // External call
                    }
            }
            return $valid;
        } else {
            return null;
        }
    }
}
?>
