<?php
require_once 'SemanticScuttle/Exception/User.php';

class SemanticScuttle_Exception extends Exception
{
    /**
     * Returns an error message that can be shown to the user.
     *
     * FIXME: maybe move that method to somewhere else
     *
     * @return string Error message to display to the user
     */
    public static function getErrorMessage(Exception $e)
    {
        if ($e instanceof SemanticScuttle_Exception_User) {
            if (isset($GLOBALS['debugMode']) && $GLOBALS['debugMode']) {
                $prev = $e->getPrevious();
                if ($prev) {
                    $msg = $prev->getMessage();
                } else {
                    $msg = $e->getMessage();
                }
            } else {
                $msg = $e->getMessage();
            }
        } else {
            $msg = $e->getMessage();
        }

        return $msg;
    }
}
?>