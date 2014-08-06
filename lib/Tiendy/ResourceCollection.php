<?php
/**
 * Tiendy ResourceCollection
 * ResourceCollection is a container object for result data
 *
 * stores and retrieves search results and aggregate data
 *
 * example:
 * <code>
 * $result = Tiendy_Product::all();
 *
 * foreach($result as $product) {
 *   print_r($product->id);
 * }
 * </code>
 *
 */
class Tiendy_ResourceCollection implements Iterator
{
    private $_index;
    private $_batchIndex;
    private $_items;
    private $_pageSize;
    private $_pager;

    /**
     * set up the resource collection
     *
     * expects an array of attributes with literal keys
     *
     * @param array $attributes
     * @param array $pagerAttribs
     */
    public function  __construct($response, $pager)
    {
        $this->_pageSize = $response["searchResults"]["pageSize"];
        $this->_count = $response["searchResults"]["count"];
        $this->_pager = $pager;
    }

    /**
     * returns the current item when iterating with foreach
     */
    public function current()
    {
        return $this->_items[$this->_index];
    }

    /**
     * returns the first item in the collection
     *
     * @return mixed
     */
    public function firstItem()
    {
        $ids = $this->_ids;
        $page = $this->_getPage(array($ids[0]));
        return $page[0];
    }

    public function key()
    {
        return null;
    }

    /**
     * advances to the next item in the collection when iterating with foreach
     */
    public function next()
    {
        ++$this->_index;
    }

    /**
     * rewinds the testIterateOverResults collection to the first item when iterating with foreach
     */
    public function rewind()
    {
        $this->_batchIndex = 0;
        $this->_getNextPage();
    }

    /**
     * returns whether the current item is valid when iterating with foreach
     */
    public function valid()
    {
        if ($this->_index == count($this->_items) && $this->_batchIndex < $this->_count) {
            $this->_getNextPage();
        }

        if ($this->_index < count($this->_items)) {
            return true;
        } else {
            return false;
        }
    }

    public function maximumCount()
    {
        return $this->_count;
    }

    private function _getNextPage()
    {
        if ($this->_count == 0) {
            $this->_items = array();
        } else {
            $this->_items = $this->_getPage($this->_pageSize, $this->_batchIndex);
            $this->_batchIndex += $this->_pageSize;
            if ($this->_batchIndex > $this->_count) {
                $this->_batchIndex = $this->_count;
            }
            $this->_index = 0;
        }
    }

    /**
     * requests the next page of results for the collection
     *
     * @return none
     */
    private function _getPage($limit, $offset)
    {
        $className = $this->_pager['className'];
        $classMethod = $this->_pager['classMethod'];
        $methodArgs = array();
        foreach ($this->_pager['methodArgs'] as $arg) {
            array_push($methodArgs, $arg);
        }
        if ($limit) {
            if (!$limit) {
                $page = 1;
            } else {
                $page = ceil($offset/$limit) + 1;
            }
            if ($page == 0) {
                $page = 1;
            }
        }
        array_push($methodArgs, $page);
        array_push($methodArgs, $limit);
        
       // die (print_r($methodArgs,1));

        return call_user_func_array(
            array($className, $classMethod),
            $methodArgs
        );
    }
}