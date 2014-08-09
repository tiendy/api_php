<?php
/**
 * Tiendy shop module
 * Retrieves shop information
 *
 */
class Tiendy_Shop extends Tiendy
{

    /**
     * Get shop info
     *
     * @access public
     * @return object Tiendy_Shop
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($placeholder = null)
    {
        try {
            $response = Tiendy_Http::get('/shop.json');
            return self::factory($response['shop']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'shop with id ' . $id . ' not found'
            );
        }

    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $shopAttribs array of shop data
     * @return none
     */
    protected function _initialize($shopAttribs)
    {
        // set the attributes
        $this->_attributes = $shopAttribs;        
    }

    /**
     * returns a string representation of the shop
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of shop data
     */
    protected $_attributes = array(
        'id'   => '',
        'name'     => '',
        'handle' => ''
        );

    

    /**
     *  factory method: returns an instance of Tiendy_Shop
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Shop
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}