<?php
/**
 * Tiendy Library Version
 * stores version information about the Tiendy library
 *
 */
final class Tiendy_Version
{
    /**
     * class constants
     */
    const MAJOR = 0;
    const MINOR = 0;
    const TINY = 1;

    /**
     * @ignore
     * @access protected
     */
    protected function  __construct()
    {
    }

    /**
     *
     * @return string the current library version
     */
    public static function get()
    {
        return self::MAJOR.'.'.self::MINOR.'.'.self::TINY;
    }
}