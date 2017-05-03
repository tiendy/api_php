<?php
/**
 * Tiendy Webhook module
 * Creates and manages content categories
 *
 */
class Tiendy_Webhook extends Tiendy
{
    public static function all($parameters=array())
    {
        $response = Tiendy_Http::get('/webhooks.json', $parameters);
        if (isset($parameters['limit']) || isset($parameters['page'])) {
            return Tiendy_Util::extractAttributeAsArray(
                $response['categories'],
                'webhook'
            );
        } else {
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
        $response = Tiendy_Http::get('/webhooks/count.json');
        return $response['count'];
    }
    
    
    public static function fetch($parameters, $page=1, $limit=0)
    {
        $parameters['page'] = intval(isset($parameters['page'])? $parameters['page']:$page);
        if ($limit) {
            $parameters['limit'] = isset($parameters['limit'])? $parameters['limit']:$limit;
        }
        $response = Tiendy_Http::get('/webhooks.json', $parameters);
        return Tiendy_Util::extractAttributeAsArray(
            $response['categories'],
            'webhook'
        );
    }

    /**
     * find a webhook by id
     *
     * @access public
     * @param string id webhook Id
     * @return object Tiendy_Webhook
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($id, $parameters=null)
    {
        self::_validateId($id);
        try {
            $response = Tiendy_Http::get('/webhooks/'.$id.'.json', $parameters);
            return self::factory($response['webhook']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'webhook with id ' . $id . ' not found'
            );
        }

    }
    
    
    /**
     * Creates a webhook using the given +attributes+.
     *
     * <code>
     *   $result = Tiendy_Webhook::create(array(
     *     'title' => 'Featured products',
     *     'description' => 'The best products on the market',
     *   ));
     *   if($result->success) {
     *     echo 'Created webhook ' . $result->webhook->id;
     *   } else {
     *     echo 'Could not create webhook, see result->errors';
     *   }
     * </code>
     *
     * @access public
     * @param array $attribs
     * @return object Result, either Successful or Error
     */
    public static function create($attribs = array())
    {
        return self::_doCreate('/webhooks.json', array('webhook' => $attribs));
    }
    


    /**
     * delete a webhook by id
     *
     * @param string $webhookId
     */
    public static function delete($webhookId)
    {
        self::_validateId($webhookId);
        Tiendy_Http::delete('/webhooks/' . $webhookId.'.json');
        return new Tiendy_Result_Successful();
    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $webhookAttribs array of webhook data
     * @return none
     */
    protected function _initialize($webhookAttribs)
    {
        // set the attributes
        $this->_attributes = $webhookAttribs;        
    }

    /**
     * returns a string representation of the webhook
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Tiendy_Webhook,
     * or is a Tiendy_Webhook with a different id
     *
     * @param object $otherProd webhook to compare against
     * @return boolean
     */
    public function isEqual($otherProd)
    {
        return !($otherProd instanceof Tiendy_Webhook) ? false : $this->id === $otherCust->id;
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of webhook data
     */
    protected $_attributes = array(
        'id'   => '',
        'title'     => '',
        );



    /**
     * verifies that a valid webhook id is being used
     * @ignore
     * @param string webhook id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected webhook id to be set'
                   );
        }
        
        if (!is_numeric($id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid webhook id.'
                    );
        }
    }


    /* private class methods */

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
     *  factory method: returns an instance of Tiendy_Webhook
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Webhook
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
    
    
    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new Tiendy_Webhook object and encapsulates
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
        if (isset($response['webhook'])) {
            // return a populated instance of Tiendy_Metafield
            return new Tiendy_Result_Successful(
                    self::factory($response['webhook'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Tiendy_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Tiendy_Exception_Unexpected(
            "Expected metafield or apiErrorResponse"
            );
        }
    }
    

}