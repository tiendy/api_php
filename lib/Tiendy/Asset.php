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
            $response = self::all($theme_id, ['id' => $id]);
            return self::factory(reset($response['assets']));
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'asset with id ' . $id . ' not found'
            );
        }

    }


    /**
     * delete a asset by id
     *
     * @param string $assetId
     */
    public static function delete($theme_id, $assetId)
    {
        self::_validateId($assetId);
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
        'title'     => '',
        );



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