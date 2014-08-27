<?php
/**
 * Tiendy Article module
 * Creates and manages blog articles
 *
 */
class Tiendy_Article extends Tiendy
{
    public static function all($parameters=array())
    {
        $response = Tiendy_Http::get('/articles.json', $parameters);
        if (isset($parameters['limit']) || isset($parameters['page'])) {
            return Tiendy_Util::extractAttributeAsArray(
                $response['articles'],
                'article'
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
        $response = Tiendy_Http::get('/articles/count.json');
        return $response['count'];
    }
    
    
    public static function fetch($parameters, $page=1, $limit=0)
    {
        $parameters['page'] = intval(isset($parameters['page'])? $parameters['page']:$page);
        if ($limit) {
            $parameters['limit'] = isset($parameters['limit'])? $parameters['limit']:$limit;
        }
        $response = Tiendy_Http::get('/articles.json', $parameters);
        return Tiendy_Util::extractAttributeAsArray(
            $response['articles'],
            'article'
        );
    }

    /**
     * find a article by id
     *
     * @access public
     * @param string id article Id
     * @return object Tiendy_Article
     * @throws Tiendy_Exception_NotFound
     */
    public static function find($id)
    {
        self::_validateId($id);
        try {
            $response = Tiendy_Http::get('/articles/'.$id.'.json');
            return self::factory($response['article']);
        } catch (Tiendy_Exception_NotFound $e) {
            throw new Tiendy_Exception_NotFound(
            'article with id ' . $id . ' not found'
            );
        }

    }


    /**
     * delete a article by id
     *
     * @param string $articleId
     */
    public static function delete($articleId)
    {
        self::_validateId($articleId);
        Tiendy_Http::delete('/articles/' . $articleId.'.json');
        return new Tiendy_Result_Successful();
    }

   

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @ignore
     * @access protected
     * @param array $articleAttribs array of article data
     * @return none
     */
    protected function _initialize($articleAttribs)
    {
        // set the attributes
        $this->_attributes = $articleAttribs;        
    }

    /**
     * returns a string representation of the article
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Tiendy_Util::attributesToString($this->_attributes) .']';
    }

    /**
     * returns false if comparing object is not a Tiendy_Article,
     * or is a Tiendy_Article with a different id
     *
     * @param object $otherProd article to compare against
     * @return boolean
     */
    public function isEqual($otherProd)
    {
        return !($otherProd instanceof Tiendy_Article) ? false : $this->id === $otherCust->id;
    }

    

    /* private class properties  */

    /**
     * @access protected
     * @var array registry of article data
     */
    protected $_attributes = array(
        'id'   => '',
        'title'     => '',
        );



    /**
     * verifies that a valid article id is being used
     * @ignore
     * @param string article id
     * @throws InvalidArgumentException
     */
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected article id to be set'
                   );
        }
        
        if (!is_numeric($id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid article id.'
                    );
        }
    }


    /* private class methods */

    

    /**
     *  factory method: returns an instance of Tiendy_Article
     *  to the requesting method, with populated properties
     *
     * @ignore
     * @return object instance of Tiendy_Article
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

}