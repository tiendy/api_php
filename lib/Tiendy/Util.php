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
        'Article' => 'article',
        'Asset'   => 'asset',
        'Metafield' => 'metafield',
        'Webhook' => 'webhook',
        'Redirect' => 'redirect'
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

        return 'Tiendy_' . $responseKeysToClassNames[$name];
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
         case 400:
         case 409:
            throw new Tiendy_Exception_ValidationsFailed($message? $message:'Bad format');
            break;
         case 401:
            throw new Tiendy_Exception_Authentication($message);
            break;
         case 403:
             throw new Tiendy_Exception_Authorization($message);
            break;
         case 404:
             throw new Tiendy_Exception_NotFound($message);
            break;
            break;
         case 500:
             throw new Tiendy_Exception_ServerError($message);
            break;
         default:
            throw new Tiendy_Exception_Unexpected('Unexpected HTTP_RESPONSE #'.$statusCode . $message);
            break;
        }
    }
    
    
    /**
     * extracts an attribute and returns an array of objects
     *
     * extracts the requested element from an array, and converts the contents
     * of its child arrays to objects of type Tiendy_$attributeName, or returns
     * an array with a single element containing the value of that array element
     *
     * @param array $attribArray attributes from a search response
     * @param string $attributeName indicates which element of the passed array to extract
     *
     * @return array array of Tiendy_$attributeName objects, or a single element array
     */
    public static function extractAttributeAsArray(& $attribArray, $attributeName)
    {
        // get what should be an array from the passed array
        $data = $attribArray;
        // set up the class that will be used to convert each array element
        $classFactory = self::buildClassName($attributeName) . '::factory';
        if(is_array($data)):
            // create an object from the data in each element
            $objectArray = array_map($classFactory, $data);
        else:
            return array($data);
        endif;

        unset($attribArray[$attributeName]);
        return $objectArray;
    }
    
    
    /**
     *
     * @param array $array associative array to implode
     * @param string $separator (optional, defaults to =)
     * @param string $glue (optional, defaults to ', ')
     */
    public static function implodeAssociativeArray($array, $separator = '=', $glue = ', ')
    {
        // build a new array with joined keys and values
        $tmpArray = null;
        foreach ($array AS $key => $value) {
                $tmpArray[] = $key . $separator . $value;

        }
        // implode and return the new array
        return (is_array($tmpArray)) ? implode($glue, $tmpArray) : false;
    }

    public static function attributesToString($attributes) {
        $printableAttribs = array();
        foreach ($attributes AS $key => $value) {
            if (is_array($value)) {
                $pAttrib = Tiendy_Util::attributesToString($value);
            } else if ($value instanceof DateTime) {
                $pAttrib = $value->format(DateTime::RFC850);
            } else {
                $pAttrib = $value;
            }
            $printableAttribs[$key] = sprintf('%s', $pAttrib);
        }
        return Tiendy_Util::implodeAssociativeArray($printableAttribs);
    }

    
}