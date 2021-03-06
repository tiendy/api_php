<?php
/**
 * Tiendy Successful Result
 *
 * A Successful Result will be returned from gateway methods when
 * validations pass. It will provide access to the created resource.
 *
 * For example, when creating a page, Tiendy_Result_Successful will
 * respond to <b>page</b> like so:
 *
 * <code>
 * $result = Tiendy_Page::create(array('title' => "shipping & handling"));
 * if ($result->success) {
 *     // Tiendy_Result_Successful
 *     echo "Created page {$result->page->id}";
 * } else {
 *     // Tiendy_Result_Error
 * }
 * </code>
 *
 *
 */
class Tiendy_Result_Successful extends Tiendy_Instance
{
    /**
     *
     * @var boolean always true
     */
    public $success = true;
    /**
     *
     * @var string stores the internal name of the object providing access to
     */
    private $_returnObjectName;

    /**
     * @ignore
     * @param string $classToReturn name of class to instantiate
     */
    public function __construct($objToReturn = null, $propertyName = null)
    {
        $this->_attributes = array();

        if(!empty($objToReturn)) {

            if(empty($propertyName)) {
                $propertyName = Tiendy_Util::cleanClassName(
                    get_class($objToReturn)
                );
            }

            // save the name for indirect access
            $this->_returnObjectName = $propertyName;

            // create the property!
            if (!empty($propertyName)) {
                $this->$propertyName = $objToReturn;
            }
        }
    }

   /**
    *
    * @ignore
    * @return string string representation of the object's structure
    */
   public function __toString()
   {
       $returnObject = $this->_returnObjectName;
       return __CLASS__ . '['.$this->$returnObject->__toString().']';
   }

}