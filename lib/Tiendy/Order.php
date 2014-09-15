<?php
/**
 * Tiendy Order module
 * Creates and manages orders
 *
 */
class Tiendy_Order extends Tiendy
{
    public static function all($parameters=array())
    {
        $response = Tiendy_Http::get('/orders.json', $parameters);
        if (count($parameters) > 0) {
            return Tiendy_Util::extractAttributeAsArray(
                $response['orders'],
                'order'
            );
        } elseif (!$parameters) {
            $pager = array(
                        'className' => __CLASS__,
                        'classMethod' => 'fetch',
                        'methodArgs' => array($parameters)
                        );
            if (isset($parameters['limit'])) {
                if ($parameters['limit'] > MAX_ITEMS_PER_PAGE){ 
                    $pageSize = MAX_ITEMS_PER_PAGE;
                } else {
                    $pageSize = $parameters['limit'];
                }
            } else {
                $pageSize = DEFAULT_ITEMS_PER_PAGE;
            }
            $response["searchResults"]["pageSize"] = $pageSize;
            $response["searchResults"]["count"] = self::count();
            
            return new Tiendy_ResourceCollection($response, $pager);
        }
    }
    
    public static function count()
    {
        $response = Tiendy_Http::get('/orders/count.json');
        return $response['count'];
    }
    
    
    public static function fetch($parameters, $page=1, $limit=0)
    {
        $parameters['page'] = intval(isset($parameters['page'])? $parameters['page']:$page);
        if ($limit) {
            $parameters['limit'] = isset($parameters['limit'])? $parameters['limit']:$limit;
        }
        $response = Tiendy_Http::get('/orders.json', $parameters);
        return Tiendy_Util::extractAttributeAsArray(
            $response['orders'],
            'order'
        );
    }

    /**
     * find a order by id
     *
     * @access public
     * @param string id order Id
     * @return object Tiendy_Order
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($id)
    {
        self::_validateId($id);
        try {
            $response = Tiendy_Http::get('/orders/'.$id.'.json');
            return self::factory($response['order']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'order with id ' . $id . ' not found'
            );
        }

    }
    
    
    /**
     * Creates a order using the given +attributes+. If <tt>:id</tt> is not passed,
     * the gateway will generate it.
     *
     * <code>
     *   $result = Tiendy_Order::create(array(
     *     'namespace' => 'seotool',
     *     'key' => 'meta_title',
     *     'value' => 'Mi zapato',
     *     'owner_resource' => 'product',
     *     'owner_id' => '12345',
     *   ));
     *   if($result->success) {
     *     echo 'Created order ' . $result->order->id;
     *   } else {
     *     echo 'Could not create order, see result->errors';
     *   }
     * </code>
     *
     * @access public
     * @param array $attribs
     * @return object Result, either Successful or Error
     */
    public static function create($attribs = array())
    {
        return self::_doCreate('/orders.json', array('order' => $attribs));
    }
    
    
    /**
     * updates the order record
     *
     * if calling this method in static context, orderId
     * is the 2nd attribute. orderId is not sent in object context.
     *
     * @access public
     * @param string $orderId
     * @param array $attributes
     * @return object Tiendy_Result_Successful or Tiendy_Result_Error
     */
    public static function update($orderId, $attributes)
    {
        self::_validateId($orderId);
        return self::_doUpdate(
            'put',
            '/orders/' . $orderId . '.json',
            array('order' => $attributes)
        );
    }


    /**
     * delete a order by id
     *
     * @param string $orderId
     */
    public static function delete($orderId)
    {
        self::_validateId($orderId);
        Tiendy_Http::delete('/orders/' . $orderId.'.json');
        return new Tiendy_Result_Successful();
    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $orderAttribs array of order data
     * @return none
     */
    protected function _initialize($orderAttribs)
    {
        // set the attributes
        $this->_attributes = $orderAttribs;        
    }

    /**
     * returns a string representation of the order
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Tiendy_Order,
     * or is a Tiendy_Order with a different id
     *
     * @param object $otherProd order to compare against
     * @return boolean
     */
    public function isEqual($otherProd)
    {
        return !($otherProd instanceof Tiendy_Order) ? false : $this->id === $otherCust->id;
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of order data
     */
    protected $_attributes = array(
        'id'   => '',
        'key'     => '',
        );


    /**
     * sends the create request to the gateway
     *
     * @ignore
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public static function _doCreate($url, $params)
    {
        $response = Tiendy_Http::post($url, $params);
        return self::_verifyGatewayResponse($response);
    }


    /**
     * verifies that a valid order id is being used
     * @ignore
     * @param string order id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected order id to be set'
                   );
        }
        
        if (!is_numeric($id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid order id.'
                    );
        }
    }


    /* private class methods */
    
    /**
     * sends the update request to the gateway
     *
     * @ignore
     * @param string $url
     * @param array $params
     * @return mixed
     */
    private static function _doUpdate($httpVerb, $url, $params)
    {
        $response = Tiendy_Http::$httpVerb($url, $params);
        return self::_verifyGatewayResponse($response);
    }

    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new Tiendy_Order object and encapsulates
     * it inside a Tiendy_Result_Successful object, or
     * encapsulates a Tiendy_Errors object inside a Result_Error
     * alternatively, throws an Unexpected exception if the response is invalid.
     *
     * @ignore
     * @param array $response gateway response values
     * @return object Result_Successful or Result_Error
     * @throws Tiendy_Exception_Unexpected
     */
    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['order'])) {
            // return a populated instance of Tiendy_Order
            return new Tiendy_Result_Successful(
                    self::factory($response['order'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Tiendy_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Tiendy_Exception_Unexpected(
            "Expected order or apiErrorResponse"
            );
        }
    }

    

    /**
     *  factory method: returns an instance of Tiendy_Order
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Order
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}