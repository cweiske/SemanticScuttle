<?php
require_once 'HTML/QuickForm2/Element/InputText.php';

/**
 * Text input element with pre-set text that vanishes when
 * the user focuses it.
 *
 * Example:
 * before:
 *   Name:   [John Do|   ]
 *   E-Mail: [Please type your email address]
 *
 * after:
 *   Name:   [John Doe   ]
 *   E-Mail: [|                             ]
 *
 * @author Christian Weiske <cweiske@php.net>
 */
class SemanticScuttle_QuickForm2_Element_BackgroundText
    extends HTML_QuickForm2_Element_InputText
{
    protected $btText  = null;
    protected $btClass = null;

    /**
     * Sets the background text to show when the text element is
     * empty and not focused
     *
     * @param string $text Background text to set
     *
     * @return SemanticScuttle_QuickForm2_BackgroundText This object
     */
    public function setBackgroundText($text)
    {
        $this->btText = $text;
        $this->btUpdateAttributes();

        return $this;
    }

    /**
     * Sets the HTML class to use when the text element is
     * empty and not focused
     *
     * @param string $class HTML class to set when the element
     *                      is not focused
     *
     * @return SemanticScuttle_QuickForm2_BackgroundText This object
     *
     * @FIXME: Class to set when it is focused
     */
    public function setBackgroundClass($class)
    {
        $this->btClass = $class;
        $this->btUpdateAttributes();

        return $this;
    }



    /**
     * Updates the attributes array after.
     * Used after setting the background text or background class.
     *
     * @return void
     */    
    protected function btUpdateAttributes()
    {
        if ($this->btText == '') {
            //deactivate it
            unset($this->attributes['onfocus']);
            unset($this->attributes['onblur']);
            return;
        }

        $this->attributes['onfocus']
            = 'if (this.value == '
            . json_encode((string)$this->btText)
            . ') this.value = "";';
        $this->attributes['onblur']
            = 'if (this.value == "") this.value = '
            . json_encode((string)$this->btText)
            . ';';

        //default when loading the form
        //FIXME: use some special char to distinguish that
        //value from a user inputted one (i.e. UTF-8 empty character)
        if (!isset($this->attributes['value']) || !$this->attributes['value']) {
            $this->attributes['value'] = $this->btText;
        }
        //FIXME: class
    }


   /**
    * Called when the element needs to update its value
    * from form's data sources.
    * This method overwrites the parent one to skip the background text
    * values.
    *
    * @return void
    */
    protected function updateValue()
    {
        $name = $this->getName();
        foreach ($this->getDataSources() as $ds) {
            if (null !== ($value = $ds->getValue($name))
                && $value !== $this->btText
            ) {
                $this->setValue($value);
                return;
            }
        }
    }
}
?>