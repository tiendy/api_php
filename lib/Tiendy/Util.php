<?php
/**
 * Tiendy Utility methods
 */

class Tiendy_Util
{

    static public $classNamesToResponseKeys = array(
        'Product' => 'product',
        'Category' => 'category',
        'Page' => 'page',
        'Blog'    => 'blog',
        'Metafield' => 'metafield',
        'Webhook' => 'webhook'
    );   

    /**
     * removes the Tiendy_ header from a classname
     *
     * @param string $name Tiendy_ClassName
     * @return camelCased classname minus Tiendy_ header
     */
    public static function cleanClassName($name)
    {
        $name = str_replace('Tiendy_', '', $name);
        if (array_key_exists($name, self::$classNamesToResponseKeys)) {
            return self::$classNamesToResponseKeys[$name];
        }
        return false;
    }

    /**
     *
     * @param string $name className
     * @return string Tiendy_ClassName
     */
    public static function buildClassName($name)
    {
        $responseKeysToClassNames = array_flip(self::$classNamesToResponseKeys);

        return 'Tiendy_' . self::$responseKeysToClassNames[$name];
    }
    
    
    /**
     * throws an exception based on the type of error
     * @param string $statusCode HTTP status code to throw exception from
     * @throws Tiendy_Exception multiple types depending on the error
     *
     */
    public static function throwStatusCodeException($statusCode, $message=null)
    {
        switch($statusCode) {
         case 401:
            throw new Tiendy_Exception_Authentication();
            break;
         case 403:
             throw new Tiendy_Exception_Authorization($message);
            break;
         case 404:
             throw new Tiendy_Exception_NotFound();
            break;
            break;
         case 500:
             throw new Tiendy_Exception_ServerError();
            break;
         default:
            throw new Tiendy_Exception_Unexpected('Unexpected HTTP_RESPONSE #'.$statusCode);
            break;
        }
    }

    
}