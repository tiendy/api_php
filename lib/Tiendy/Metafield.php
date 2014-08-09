<?php
/**
 * Tiendy Metafield module
 * Creates and manages metafields
 *
 */
class Tiendy_Metafield extends Tiendy
{
    public static function all($parameters=array())
    {
        $response = Tiendy_Http::get('/metafields.json', $parameters);
        if (count($parameters) > 0) {
            return Tiendy_Util::extractAttributeAsArray(
                $response['metafields'],
                'metafield'
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
        $response = Tiendy_Http::get('/metafields/count.json');
        return $response['count'];
    }
    
    
    public static function fetch($parameters, $page=1, $limit=0)
    {
        $parameters['page'] = intval(isset($parameters['page'])? $parameters['page']:$page);
        if ($limit) {
            $parameters['limit'] = isset($parameters['limit'])? $parameters['limit']:$limit;
        }
        $response = Tiendy_Http::get('/metafields.json', $parameters);
        return Tiendy_Util::extractAttributeAsArray(
            $response['metafields'],
            'metafield'
        );
    }

    /**
     * find a metafield by id
     *
     * @access public
     * @param string id metafield Id
     * @return object Tiendy_Metafield
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($id)
    {
        self::_validateId($id);
        try {
            $response = Tiendy_Http::get('/metafields/'.$id.'.json');
            return self::factory($response['metafield']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'metafield with id ' . $id . ' not found'
            );
        }

    }
    
    
    /**
     * Creates a metafield using the given +attributes+. If <tt>:id</tt> is not passed,
     * the gateway will generate it.
     *
     * <code>
     *   $result = Tiendy_Metafield::create(array(
     *     'namespace' => 'seotool',
     *     'key' => 'meta_title',
     *     'value' => 'Mi zapato',
     *     'owner_resource' => 'product',
     *     'owner_id' => '12345',
     *   ));
     *   if($result->success) {
     *     echo 'Created metafield ' . $result->metafield->id;
     *   } else {
     *     echo 'Could not create metafield, see result->errors';
     *   }
     * </code>
     *
     * @access public
     * @param array $attribs
     * @return object Result, either Successful or Error
     */
    public static function create($attribs = array())
    {
        return self::_doCreate('/metafields.json', array('metafield' => $attribs));
    }
    
    
    /**
     * updates the metafield record
     *
     * if calling this method in static context, metafieldId
     * is the 2nd attribute. metafieldId is not sent in object context.
     *
     * @access public
     * @param string $metafieldId
     * @param array $attributes
     * @return object Tiendy_Result_Successful or Tiendy_Result_Error
     */
    public static function update($metafieldId, $attributes)
    {
        self::_validateId($metafieldId);
        return self::_doUpdate(
            'put',
            '/metafields/' . $metafieldId . '.json',
            array('metafield' => $attributes)
        );
    }


    /**
     * delete a metafield by id
     *
     * @param string $metafieldId
     */
    public static function delete($metafieldId)
    {
        self::_validateId($metafieldId);
        Tiendy_Http::delete('/metafields/' . $metafieldId.'.json');
        return new Tiendy_Result_Successful();
    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $metafieldAttribs array of metafield data
     * @return none
     */
    protected function _initialize($metafieldAttribs)
    {
        // set the attributes
        $this->_attributes = $metafieldAttribs;        
    }

    /**
     * returns a string representation of the metafield
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Tiendy_Metafield,
     * or is a Tiendy_Metafield with a different id
     *
     * @param object $otherProd metafield to compare against
     * @return boolean
     */
    public function isEqual($otherProd)
    {
        return !($otherProd instanceof Tiendy_Metafield) ? false : $this->id === $otherCust->id;
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of metafield data
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
     * verifies that a valid metafield id is being used
     * @ignore
     * @param string metafield id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected metafield id to be set'
                   );
        }
        
        if (!is_numeric($id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid metafield id.'
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
     * creates a new Tiendy_Metafield object and encapsulates
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
        if (isset($response['metafield'])) {
            // return a populated instance of Tiendy_Metafield
            return new Tiendy_Result_Successful(
                    self::factory($response['metafield'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Tiendy_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Tiendy_Exception_Unexpected(
            "Expected metafield or apiErrorResponse"
            );
        }
    }

    

    /**
     *  factory method: returns an instance of Tiendy_Metafield
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Metafield
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}