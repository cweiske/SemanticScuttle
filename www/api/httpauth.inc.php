<?php
/**
 * Checks if the user is logged on and sends a HTTP basic auth
 * request to the browser if not. In that case the script ends.
 * If username and password are available, the user service's
 * login method is used to log the user in.
 *
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
require_once '../www-header.php';

/**
 * Sends HTTP auth headers to the browser
 */
function authenticate()
{
	header('WWW-Authenticate: Basic realm="SemanticScuttle API"');
	header('HTTP/1.0 401 Unauthorized');

	die(T_("Use of the API calls requires authentication."));
}

if (!$userservice->isLoggedOn()) {
	/* Maybe we have caught authentication data in $_SERVER['REMOTE_USER']
	 ( Inspired by http://www.yetanothercommunitysystem.com/article-321-regle-comment-utiliser-l-authentification-http-en-php-chez-ovh ) */
	if ((!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']))
        && isset($_SERVER['REMOTE_USER'])
        && preg_match('/Basic\s+(.*)$/i', $_SERVER['REMOTE_USER'], $matches)
    ) {
        list($name, $password) = explode(':', base64_decode($matches[1]));
        $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
        $_SERVER['PHP_AUTH_PW'] = strip_tags($password);
	}

    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        authenticate();
    } else {
        $login = $userservice->login(
            $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']
        );
        if ($login) {
            $currentUser = $userservice->getCurrentObjectUser();
        } else {
            authenticate();
        }
    }
}
?>
