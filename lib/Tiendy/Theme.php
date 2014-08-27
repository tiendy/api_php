<?php
/**
 * Tiendy Theme module
 * Creates and manages themes
 *
 */
class Tiendy_Theme extends Tiendy
{
    public static function all($parameters=array())
    {
        $response = Tiendy_Http::get('/themes.json', $parameters);
        if (isset($parameters['limit']) || isset($parameters['page'])) {
            return Tiendy_Util::extractAttributeAsArray(
                $response['themes'],
                'theme'
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
        $response = Tiendy_Http::get('/themes/count.json');
        return $response['count'];
    }
    
    
    public static function fetch($parameters, $page=1, $limit=0)
    {
        $parameters['page'] = intval(isset($parameters['page'])? $parameters['page']:$page);
        if ($limit) {
            $parameters['limit'] = isset($parameters['limit'])? $parameters['limit']:$limit;
        }
        $response = Tiendy_Http::get('/themes.json', $parameters);
        return Tiendy_Util::extractAttributeAsArray(
            $response['themes'],
            'theme'
        );
    }

    /**
     * find a theme by id
     *
     * @access public
     * @param string id theme Id
     * @return object Tiendy_Theme
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($id)
    {
        self::_validateId($id);
        try {
            $response = Tiendy_Http::get('/themes/'.$id.'.json');
            return self::factory($response['theme']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'theme with id ' . $id . ' not found'
            );
        }

    }


    /**
     * delete a theme by id
     *
     * @param string $themeId
     */
    public static function delete($themeId)
    {
        self::_validateId($themeId);
        Tiendy_Http::delete('/themes/' . $themeId.'.json');
        return new Tiendy_Result_Successful();
    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $themeAttribs array of theme data
     * @return none
     */
    protected function _initialize($themeAttribs)
    {
        // set the attributes
        $this->_attributes = $themeAttribs;        
    }

    /**
     * returns a string representation of the theme
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Tiendy_Theme,
     * or is a Tiendy_Theme with a different id
     *
     * @param object $otherProd theme to compare against
     * @return boolean
     */
    public function isEqual($otherProd)
    {
        return !($otherProd instanceof Tiendy_Theme) ? false : $this->id === $otherCust->id;
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of theme data
     */
    protected $_attributes = array(
        'id'   => '',
        'title'     => '',
        );



    /**
     * verifies that a valid theme id is being used
     * @ignore
     * @param string theme id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected theme id to be set'
                   );
        }
        
        if (!is_numeric($id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid theme id.'
                    );
        }
    }


    /* private class methods */

    

    /**
     *  factory method: returns an instance of Tiendy_Theme
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Theme
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}