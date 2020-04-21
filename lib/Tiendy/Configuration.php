<?php
/**
 *
 * Configuration registry
 *
 */

class Tiendy_Configuration extends Tiendy
{

    /**
     * @var array array of config properties
     * @access protected
     * @static
     */
    private static $_cache = array(
                    'client_id'   => '',
                    'client_secret'    => '',
                    'client_shared'     => '',
                    'shop'    => '', // prueba (prueba.mitiendy.com)
                    'token' => ''
                   );

    /**
     * resets configuration to default
     * @access public
     * @static
     */
    public static function reset()
    {
        self::$_cache = array (
            'client_id' => '',
            'client_secret'  => '',
            'client_shared' => '',
            'shop' => '',
            'token' => ''
        );
    }

    /**
     * performs sanity checks when config settings are being set
     *
     * @ignore
     * @access protected
     * @param string $key name of config setting
     * @param string $value value to set
     * @throws InvalidArgumentException
     * @throws Tiendy_Exception_Configuration
     * @static
     * @return boolean
     */
    private static function validate($key=null, $value=null)
    {
        if (empty($key) && empty($value)) {
             throw new InvalidArgumentException('nothing to validate');
        }

        if (!isset(self::$_cache[$key])) {
             throw new Tiendy_Exception_Configuration($key .
                                    ' is not a valid configuration setting.');
        }

        if (empty($value)) {
             throw new InvalidArgumentException($key . ' cannot be empty.');
        }

        return true;
    }
    

    private static function set($key, $value)
    {
        // this method will raise an exception on invalid data
        self::validate($key, $value);
        // set the value in the cache
        self::$_cache[$key] = $value;

    }

    private static function get($key)
    {
        // throw an exception if the value hasn't been set
        if (isset(self::$_cache[$key]) &&
           (empty(self::$_cache[$key]))) {
            throw new Tiendy_Exception_Configuration(
                      $key.' needs to be set.'
                      );
        }

        if (array_key_exists($key, self::$_cache)) {
            return self::$_cache[$key];
        }

        // return null by default to prevent __set from overloading
        return null;
    }


    private static function setOrGet($name, $value = null)
    {
        if (!empty($value) && is_array($value)) {
            $value = $value[0];
        }
        if (!empty($value)) {
            self::set($name, $value);
        } else {
            return self::get($name);
        }
        return true;
    }
    /**#@+
     * sets or returns the property after validation
     * @access public
     * @static
     * @param string $value pass a string to set, empty to get
     * @return mixed returns true on set
     */
    public static function client_id($value = null)
    {
        return self::setOrGet(__FUNCTION__, $value);
    }

    public static function client_shared($value = null)
    {
        return self::setOrGet(__FUNCTION__, $value);
    }

    public static function client_secret($value = null)
    {
        return self::setOrGet(__FUNCTION__, $value);
    }

    public static function shop($value = null)
    {
        return self::setOrGet(__FUNCTION__, $value);
    }
    
    public static function token($value = null)
    {
        if (!$value && !self::$_cache['token']){
            return null;
        }
        return self::setOrGet(__FUNCTION__, $value);
    }
    /**#@-*/


    /**
     * returns the base API URL based on config values
     *
     * @access public
     * @static
     * @param none
     * @return string shop base API URL
     */
    public static function baseUrl()
    {
        if (isset($_SERVER['REMOTE_ADDR']) && ('127.0.0.1' == $_SERVER['REMOTE_ADDR'] || '192.168.' == substr($_SERVER['REMOTE_ADDR'], 0, 8))){
            return 'http://' . self::shop() . '.local/admin';
        } else {
            return 'https://' . self::shop() . '.mitiendy.com/admin';
        }
    }

    /**
     * sets the merchant path based on merchant ID
     *
     * @access protected
     * @static
     * @param none
     * @return string merchant path uri
     */
    public static function authorizePath()
    {
        return '/auth/authorize?client_id=' . self::client_id();
    }


    

    /**
     * log message to default logger
     *
     * @param string $message
     *
     */
    public static function logMessage($message)
    {
        error_log('[Tiendy] ' . $message);
    }

}
