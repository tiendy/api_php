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

    
}