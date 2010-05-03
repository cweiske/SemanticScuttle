<?php
require_once 'HTML/QuickForm2/Renderer/Array.php';

/**
 * Custom HTML_QuickForm2 renderer allowing easy access
 * to elements by their ID
 *
 * FIXME
 */
class SemanticScuttle_QuickForm2_Renderer_CoolArray
    extends HTML_QuickForm2_Renderer_Array
    implements ArrayAccess
{
    protected $ids = array();


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Overwrite parent method to create ID index
     */
    public function pushScalar(array $element)
    {
        parent::pushScalar($element);
        $id   = $element['id']; 
        $cont =& $this->containers[
            count($this->containers) - 1
        ];
        $this->ids[$id] =& $cont[count($cont) - 1];
    }



   /**
    * Creates an array with fields that are common to all elements.
    * This method here also creates html labels.
    *
    * @param HTML_QuickForm2_Node $element Element being rendered
    *
    * @return array Array of attributes
    */
    public function buildCommonFields(HTML_QuickForm2_Node $element)
    {
        $ary = parent::buildCommonFields($element);
        if (isset($ary['label'])) {
            //FIXME: error class
            //FIXME: htmlspecialchars()?
            $ary['labelhtml'] = '<label for="' . $ary['id'] . '">'
                . $ary['label'] . '</label>';
        }
        return $ary;
    }



    public function offsetSet($offset, $value)
    {
        $this->ids[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        if (isset($this->array[$offset])) {
            return true; 
        }
        return isset($this->ids[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->ids[$offset]);
    }

    public function offsetGet($offset)
    {
        if (isset($this->array[$offset])) {
            return $this->array[$offset];
        }
        return isset($this->ids[$offset])
            ? $this->ids[$offset]
            : null;
    }

}

?>