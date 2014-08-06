<?php
/**
 * Tiendy Product module
 * Creates and manages Products
 *
 */
class Tiendy_Product extends Tiendy
{
    public static function all($parameters=null)
    {
        $response = Tiendy_Http::get('/products.json', $parameters);
        $collection = array();
        foreach ($response['products'] as $i=>$item) {
             $collection[$i] = self::factory($item);
        }
        return $collection;
    }

    /**
     * find a product by id
     *
     * @access public
     * @param string id product Id
     * @return object Tiendy_Product
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($id)
    {
        self::_validateId($id);
        try {
            $response = Tiendy_Http::get('/products/'.$id.'.json');
            return self::factory($response['product']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'product with id ' . $id . ' not found'
            );
        }

    }


    /**
     * delete a product by id
     *
     * @param string $productId
     */
    public static function delete($productId)
    {
        self::_validateId($productId);
        Tiendy_Http::delete('/products/' . $productId.'.json');
        return new Tiendy_Result_Successful();
    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $productAttribs array of product data
     * @return none
     */
    protected function _initialize($productAttribs)
    {
        // set the attributes
        $this->_attributes = $productAttribs;

        // map each address into its own object
        /*
        $addressArray = array();
        if (isset($productAttribs['addresses'])) {

            foreach ($productAttribs['addresses'] AS $address) {
                $addressArray[] = Tiendy_Address::factory($address);
            }
        }
        $this->_set('addresses', $addressArray);
        */
    }

    /**
     * returns a string representation of the product
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Tiendy_Product,
     * or is a Tiendy_Product with a different id
     *
     * @param object $otherProd product to compare against
     * @return boolean
     */
    public function isEqual($otherProd)
    {
        return !($otherProd instanceof Tiendy_Product) ? false : $this->id === $otherCust->id;
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of product data
     */
    protected $_attributes = array(
        'id'   => '',
        'title'     => '',
        );



    /**
     * verifies that a valid product id is being used
     * @ignore
     * @param string product id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected product id to be set'
                   );
        }
        
        if (!is_integer($id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid product id.'
                    );
        }
    }


    /* private class methods */

    

    /**
     *  factory method: returns an instance of Tiendy_Product
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Product
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}