<?php
/**
 * Tiendy Asset module
 * Creates and manages theme assets
 *
 */
class Tiendy_Asset extends Tiendy
{
    public static function all($parameters=array(), $theme_id = 1)
    {
        $response = Tiendy_Http::get("/themes/$theme_id/assets.json", $parameters);
        return Tiendy_Util::extractAttributeAsArray(
            $response['assets'],
            'asset'
        );
    }
    

    /**
     * find a asset by id
     *
     * @access public
     * @param string id asset Id
     * @return object Tiendy_Asset
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($id, $theme_id = 1)
    {
        try {
            $response = Tiendy_Http::get("/themes/$theme_id/assets.json", ['id' => $id]);
            return self::factory($response['asset']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'asset with id ' . $id . ' not found'
            );
        }

    }
    
    
    /**
     * Creates a asset using the given +attributes+. If <tt>:id</tt> is not passed,
     * the gateway will generate it.
     *
     * <code>
     *   $result = Tiendy_Asset::create(array(
     *     'id' => 'snippets/seotool.tpl',
     *     'value' => '<html></html>',
     *   ));
     *   if($result->success) {
     *     echo 'Created asset ' . $result->asset->id;
     *   } else {
     *     echo 'Could not create asset, see result->errors';
     *   }
     * </code>
     *
     * @access public
     * @param array $attribs
     * @return object Result, either Successful or Error
     */
    public static function create($attribs = array(), $theme_id = 1)
    {
        return self::_doCreate("/themes/$theme_id/assets.json", array('asset' => $attribs));
    }


    /**
     * delete a asset by id
     *
     * @param string $assetId
     */
    public static function delete($theme_id, $assetId)
    {
//        self::_validateId($assetId);
        Tiendy_Http::delete("/themes/$theme_id/assets.json?id=" . $assetId);
        return new Tiendy_Result_Successful();
    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $assetAttribs array of asset data
     * @return none
     */
    protected function _initialize($assetAttribs)
    {
        // set the attributes
        $this->_attributes = $assetAttribs;        
    }

    /**
     * returns a string representation of the asset
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Tiendy_Asset,
     * or is a Tiendy_Asset with a different id
     *
     * @param object $otherProd asset to compare against
     * @return boolean
     */
    public function isEqual($otherProd)
    {
        return !($otherProd instanceof Tiendy_Asset) ? false : $this->id === $otherCust->id;
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of asset data
     */
    protected $_attributes = array(
        'id'   => '',
        'content_type'     => '',
        'url' => '',
        'size' => '',
        'attachment' => '',
        'value' => '',
        'created_at' => '',
        'updated_at' => ''
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
     * verifies that a valid asset id is being used
     * @ignore
     * @param string asset id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected asset id to be set'
                   );
        }
        
        if (!is_numeric($id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid asset id.'
                    );
        }
    }


    /* private class methods */
    
    /**
     * generic method for validating incoming gateway responses
     *
     * creates a new Tiendy_asset object and encapsulates
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
        if (isset($response['asset'])) {
            // return a populated instance of Tiendy_asset
            return new Tiendy_Result_Successful(
                    self::factory($response['asset'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Tiendy_Result_Error($response['apiErrorResponse']);
        } else {
            throw new Tiendy_Exception_Unexpected(
            "Expected asset or apiErrorResponse"
            );
        }
    }
    

    

    /**
     *  factory method: returns an instance of Tiendy_Asset
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Asset
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}