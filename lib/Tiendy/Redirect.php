<?php
/**
 * Tiendy Redirect module
 * Creates and manages redirects
 *
 */
class Tiendy_Redirect extends Tiendy
{
    public static function all($parameters=array())
    {
        $response = Tiendy_Http::get('/redirects.json', $parameters);
        if (count($parameters) > 0) {
            return Tiendy_Util::extractAttributeAsArray(
                $response['redirects'],
                'redirect'
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
        $response = Tiendy_Http::get('/redirects/count.json');
        return $response['count'];
    }
    
    
    public static function fetch($parameters, $page=1, $limit=0)
    {
        $parameters['page'] = intval(isset($parameters['page'])? $parameters['page']:$page);
        if ($limit) {
            $parameters['limit'] = isset($parameters['limit'])? $parameters['limit']:$limit;
        }
        $response = Tiendy_Http::get('/redirects.json', $parameters);
        return Tiendy_Util::extractAttributeAsArray(
            $response['redirects'],
            'redirect'
        );
    }

    /**
     * find a redirect by id
     *
     * @access public
     * @param string id redirect Id
     * @return object Tiendy_Redirect
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($id)
    {
        self::_validateId($id);
        try {
            $response = Tiendy_Http::get('/redirects/'.$id.'.json');
            return self::factory($response['redirect']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'redirect with id ' . $id . ' not found'
            );
        }

    }
    
    
    /**
     * Creates a redirect using the given +attributes+. If <tt>:id</tt> is not passed,
     * the gateway will generate it.
     *
     * <code>
     *   $result = Tiendy_Redirect::create(array(
     *     'namespace' => 'seotool',
     *     'key' => 'meta_title',
     *     'value' => 'Mi zapato',
     *     'owner_resource' => 'product',
     *     'owner_id' => '12345',
     *   ));
     *   if($result->success) {
     *     echo 'Created redirect ' . $result->redirect->id;
     *   } else {
     *     echo 'Could not create redirect, see result->errors';
     *   }
     * </code>
     *
     * @access public
     * @param array $attribs
     * @return object Result, either Successful or Error
     */
    public static function create($attribs = array())
    {
        return self::_doCreate('/redirects.json', array('redirect' => $attribs));
    }
    
    
    /**
     * updates the redirect record
     *
     * if calling this method in static context, redirectId
     * is the 2nd attribute. redirectId is not sent in object context.
     *
     * @access public
     * @param string $redirectId
     * @param array $attributes
     * @return object Tiendy_Result_Successful or Tiendy_Result_Error
     */
    public static function update($redirectId, $attributes)
    {
        self::_validateId($redirectId);
        return self::_doUpdate(
            'put',
            '/redirects/' . $redirectId . '.json',
            array('redirect' => $attributes)
        );
    }


    /**
     * delete a redirect by id
     *
     * @param string $redirectId
     */
    public static function delete($redirectId)
    {
        self::_validateId($redirectId);
        Tiendy_Http::delete('/redirects/' . $redirectId.'.json');
        return new Tiendy_Result_Successful();
    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $redirectAttribs array of redirect data
     * @return none
     */
    protected function _initialize($redirectAttribs)
    {
        // set the attributes
        $this->_attributes = $redirectAttribs;        
    }

    /**
     * returns a string representation of the redirect
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Tiendy_Redirect,
     * or is a Tiendy_Redirect with a different id
     *
     * @param object $otherProd redirect to compare against
     * @return boolean
     */
    public function isEqual($otherProd)
    {
        return !($otherProd instanceof Tiendy_Redirect) ? false : $this->id === $otherCust->id;
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of redirect data
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
     * verifies that a valid redirect id is being used
     * @ignore
     * @param string redirect id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected redirect id to be set'
                   );
        }
        
        if (!is_numeric($id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid redirect id.'
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
     * creates a new Tiendy_Redirect object and encapsulates
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
        if (isset($response['redirect'])) {
            // return a populated instance of Tiendy_Redirect
            return new Tiendy_Result_Successful(
                    self::factory($response['redirect'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Tiendy_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Tiendy_Exception_Unexpected(
            "Expected redirect or apiErrorResponse"
            );
        }
    }

    

    /**
     *  factory method: returns an instance of Tiendy_Redirect
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Redirect
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}