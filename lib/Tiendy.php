<?php
/**
 * Tiendy API PHP Library
 *
 * Tiendy base class and initialization
 * Provides methods to child classes. This class cannot be instantiated.
 *
 */
 
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)));
 
abstract class Tiendy
{
    /**
     * @ignore
     * don't permit an explicit call of the constructor!
     * (like $t = new Tiendy_Category())
     */
    protected function __construct()
    {
    }
    /**
     * @ignore
     *  don't permit cloning the instances (like $x = clone $v)
     */
    protected function __clone()
    {
    }

    /**
     * returns private/nonexistent instance properties
     * @ignore
     * @access public
     * @param string $name property name
     * @return mixed contents of instance properties
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        }
        else {
            trigger_error('Undefined property on ' . get_class($this) . ': ' . $name, E_USER_NOTICE);
            return null;
        }
    }

    /**
     * used by isset() and empty()
     * @access public
     * @param string $name property name
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    public function _set($key, $value)
    {
        $this->_attributes[$key] = $value;
    }

    /**
     *
     * @param string $className
     * @param object $resultObj
     * @return object returns the passed object if successful
     * @throws Tiendy_Exception_ValidationsFailed
     */
    public static function returnObjectOrThrowException($className, $resultObj)
    {
        $resultObjName = Tiendy_Util::cleanClassName($className);
        if ($resultObj->success) {
            return $resultObj->$resultObjName;
        } else {
            throw new Tiendy_Exception_ValidationsFailed();
        }
    }
    
}

require_once('Tiendy/Exception/Configuration.php');
require_once('Tiendy/Exception/ValidationsFailed.php');
require_once('Tiendy/Util.php');

if (version_compare(PHP_VERSION, '5.2.1', '<')) {
    throw new Tiendy_Exception('PHP version >= 5.2.1 required');
}


function requireDependencies() {
    $requiredExtensions = array('xmlwriter', 'SimpleXML', 'openssl', 'dom', 'hash', 'curl');
    foreach ($requiredExtensions AS $ext) {
        if (!extension_loaded($ext)) {
            throw new Tiendy_Exception('The Tiendy API library requires the ' . $ext . ' extension.');
        }
    }
}

requireDependencies();