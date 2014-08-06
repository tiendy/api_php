<?php
/**
 * Tiendy Page module
 * Creates and manages content pages
 *
 */
class Tiendy_Page extends Tiendy
{
    public static function all($parameters=array())
    {
        $response = Tiendy_Http::get('/pages.json', $parameters);
        if (isset($parameters['limit']) || isset($parameters['page'])) {
            return Tiendy_Util::extractAttributeAsArray(
                $response['pages'],
                'page'
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
            $r = Tiendy_Http::get('/pages/count.json');
            $response["searchResults"]["count"] = $r['count'];
            
            return new Tiendy_ResourceCollection($response, $pager);
        }
    }
    
    
    public static function fetch($parameters, $page=1, $limit=0)
    {
        $parameters['page'] = intval(isset($parameters['page'])? $parameters['page']:$page);
        if ($limit) {
            $parameters['limit'] = isset($parameters['limit'])? $parameters['limit']:$limit;
        }
        $response = Tiendy_Http::get('/pages.json', $parameters);
        return Tiendy_Util::extractAttributeAsArray(
            $response['pages'],
            'page'
        );
    }

    /**
     * find a page by id
     *
     * @access public
     * @param string id page Id
     * @return object Tiendy_Page
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($id)
    {
        self::_validateId($id);
        try {
            $response = Tiendy_Http::get('/pages/'.$id.'.json');
            return self::factory($response['page']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'page with id ' . $id . ' not found'
            );
        }

    }


    /**
     * delete a page by id
     *
     * @param string $pageId
     */
    public static function delete($pageId)
    {
        self::_validateId($pageId);
        Tiendy_Http::delete('/pages/' . $pageId.'.json');
        return new Tiendy_Result_Successful();
    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $pageAttribs array of page data
     * @return none
     */
    protected function _initialize($pageAttribs)
    {
        // set the attributes
        $this->_attributes = $pageAttribs;        
    }

    /**
     * returns a string representation of the page
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Tiendy_Page,
     * or is a Tiendy_Page with a different id
     *
     * @param object $otherProd page to compare against
     * @return boolean
     */
    public function isEqual($otherProd)
    {
        return !($otherProd instanceof Tiendy_Page) ? false : $this->id === $otherCust->id;
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of page data
     */
    protected $_attributes = array(
        'id'   => '',
        'title'     => '',
        );



    /**
     * verifies that a valid page id is being used
     * @ignore
     * @param string page id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected page id to be set'
                   );
        }
        
        if (!is_integer($id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid page id.'
                    );
        }
    }


    /* private class methods */

    

    /**
     *  factory method: returns an instance of Tiendy_Page
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Page
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}