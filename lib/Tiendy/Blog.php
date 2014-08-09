<?php
/**
 * Tiendy Blog module
 * Creates and manages content blogs
 *
 */
class Tiendy_Blog extends Tiendy
{
    public static function all($parameters=array())
    {
        $response = Tiendy_Http::get('/blogs.json', $parameters);
        if (isset($parameters['limit']) || isset($parameters['page'])) {
            return Tiendy_Util::extractAttributeAsArray(
                $response['blogs'],
                'blog'
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
        $response = Tiendy_Http::get('/blogs/count.json');
        return $response['count'];
    }
    
    
    public static function fetch($parameters, $page=1, $limit=0)
    {
        $parameters['page'] = intval(isset($parameters['page'])? $parameters['page']:$page);
        if ($limit) {
            $parameters['limit'] = isset($parameters['limit'])? $parameters['limit']:$limit;
        }
        $response = Tiendy_Http::get('/blogs.json', $parameters);
        return Tiendy_Util::extractAttributeAsArray(
            $response['blogs'],
            'blog'
        );
    }

    /**
     * find a blog by id
     *
     * @access public
     * @param string id blog Id
     * @return object Tiendy_Blog
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($id)
    {
        self::_validateId($id);
        try {
            $response = Tiendy_Http::get('/blogs/'.$id.'.json');
            return self::factory($response['blog']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'blog with id ' . $id . ' not found'
            );
        }

    }


    /**
     * delete a blog by id
     *
     * @param string $blogId
     */
    public static function delete($blogId)
    {
        self::_validateId($blogId);
        Tiendy_Http::delete('/blogs/' . $blogId.'.json');
        return new Tiendy_Result_Successful();
    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $blogAttribs array of blog data
     * @return none
     */
    protected function _initialize($blogAttribs)
    {
        // set the attributes
        $this->_attributes = $blogAttribs;        
    }

    /**
     * returns a string representation of the blog
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Tiendy_Blog,
     * or is a Tiendy_Blog with a different id
     *
     * @param object $otherProd blog to compare against
     * @return boolean
     */
    public function isEqual($otherProd)
    {
        return !($otherProd instanceof Tiendy_Blog) ? false : $this->id === $otherCust->id;
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of blog data
     */
    protected $_attributes = array(
        'id'   => '',
        'title'     => '',
        );



    /**
     * verifies that a valid blog id is being used
     * @ignore
     * @param string blog id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected blog id to be set'
                   );
        }
        
        if (!is_numeric($id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid blog id.'
                    );
        }
    }


    /* private class methods */

    

    /**
     *  factory method: returns an instance of Tiendy_Blog
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Blog
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}