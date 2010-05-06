<?php
require_once 'HTML/QuickForm2/Rule/Callback.php';

/**
 * Custom rule that behaves like a callback but inverts the result.
 *
 * @author Christian Weiske <cweiske@php.net>
 */
class SemanticScuttle_QuickForm2_Rule_ICallback
    extends HTML_QuickForm2_Rule_Callback
{

   /**
    * Validates the owner element.
    * Inverts the return value of the callback.
    *
    * @return bool The value returned by a callback function
    */
    protected function validateOwner()
    {
        return !parent::validateOwner();
    }
}

?>