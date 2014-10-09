<?php
/**
 * Tiendy Category module
 * Creates and manages content categories
 *
 */
class Tiendy_Category extends Tiendy
{
    public static function all($parameters=array())
    {
        $response = Tiendy_Http::get('/categories.json', $parameters);
        if (isset($parameters['limit']) || isset($parameters['page'])) {
            return Tiendy_Util::extractAttributeAsArray(
                $response['categories'],
                'category'
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
        $response = Tiendy_Http::get('/categories/count.json');
        return $response['count'];
    }
    
    
    public static function fetch($parameters, $page=1, $limit=0)
    {
        $parameters['page'] = intval(isset($parameters['page'])? $parameters['page']:$page);
        if ($limit) {
            $parameters['limit'] = isset($parameters['limit'])? $parameters['limit']:$limit;
        }
        $response = Tiendy_Http::get('/categories.json', $parameters);
        return Tiendy_Util::extractAttributeAsArray(
            $response['categories'],
            'category'
        );
    }

    /**
     * find a category by id
     *
     * @access public
     * @param string id category Id
     * @return object Tiendy_Category
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($id, $parameters=null)
    {
        self::_validateId($id);
        try {
            $response = Tiendy_Http::get('/categories/'.$id.'.json', $parameters);
            return self::factory($response['category']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'category with id ' . $id . ' not found'
            );
        }

    }
    
    
    /**
     * Creates a category using the given +attributes+.
     *
     * <code>
     *   $result = Tiendy_Category::create(array(
     *     'title' => 'Featured products',
     *     'description' => 'The best products on the market',
     *   ));
     *   if($result->success) {
     *     echo 'Created category ' . $result->category->id;
     *   } else {
     *     echo 'Could not create category, see result->errors';
     *   }
     * </code>
     *
     * @access public
     * @param array $attribs
     * @return object Result, either Successful or Error
     */
    public static function create($attribs = array())
    {
        return self::_doCreate('/categories.json', array('category' => $attribs));
    }
    
    
    public static function addProduct($categoryId, $attribs = array())
    {
        return self::_doCreate('/categories' . intval($categoryId) . '/products.json', $attribs);
    }


    /**
     * delete a category by id
     *
     * @param string $categoryId
     */
    public static function delete($categoryId)
    {
        self::_validateId($categoryId);
        Tiendy_Http::delete('/categories/' . $categoryId.'.json');
        return new Tiendy_Result_Successful();
    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $categoryAttribs array of category data
     * @return none
     */
    protected function _initialize($categoryAttribs)
    {
        // set the attributes
        $this->_attributes = $categoryAttribs;        
    }

    /**
     * returns a string representation of the category
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Tiendy_Category,
     * or is a Tiendy_Category with a different id
     *
     * @param object $otherProd category to compare against
     * @return boolean
     */
    public function isEqual($otherProd)
    {
        return !($otherProd instanceof Tiendy_Category) ? false : $this->id === $otherCust->id;
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of category data
     */
    protected $_attributes = array(
        'id'   => '',
        'title'     => '',
        );



    /**
     * verifies that a valid category id is being used
     * @ignore
     * @param string category id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected category id to be set'
                   );
        }
        
        if (!is_numeric($id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid category id.'
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
     *  factory method: returns an instance of Tiendy_Category
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Category
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}