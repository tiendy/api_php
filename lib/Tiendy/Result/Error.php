<?php
/**
 * Tiendy Error Result
 *
 * An Error Result will be returned from gateway methods when
 * the gateway responds with an error. It will provide access
 * to the original request.
 */
class Tiendy_Result_Error extends Tiendy
{
   /**
    *
    * @var boolean always false
    */
   public $success = false;

    /**
     * return original value for a field
     * For example, if a user tried to submit 'invalid-email' in the html field transaction[customer][email],
     * $result->valueForHtmlField("transaction[customer][email]") would yield "invalid-email"
     *
     * @param string $field
     * @return string
     */
   public function valueForHtmlField($field)
   {
       $pieces = preg_split("/[\[\]]+/", $field, 0, PREG_SPLIT_NO_EMPTY);
       $params = $this->params;
       foreach(array_slice($pieces, 0, -1) as $key) {
           $params = $params[Tiendy_Util::delimiterToCamelCase($key)];
       }
       if ($key != 'custom_fields') {
           $finalKey = Tiendy_Util::delimiterToCamelCase(end($pieces));
       } else {
           $finalKey = end($pieces);
       }
       $fieldValue = isset($params[$finalKey]) ? $params[$finalKey] : null;
       return $fieldValue;
   }

   /**
    * overrides default constructor
    * @ignore
    * @param array $response gateway response array
    */
   public function  __construct($response)
   {
       $this->_attributes = $response;
       $this->_set('errors',  new Tiendy_Error_ErrorCollection($response['errors']));
   }

   /**
     * create a printable representation of the object as:
     * ClassName[property=value, property=value]
     * @ignore
     * @return var
     */
    public function  __toString()
    {
        $output = Tiendy_Util::attributesToString($this->_attributes);
        return __CLASS__ .'['.$output.']';
    }
}