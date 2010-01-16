<?php
require_once '../../src/SemanticScuttle/header.php';

// Provides HTTP Basic authentication of a user
// and logs the user in if necessary

function authenticate() {
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
        $login = $userservice->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        if (!$login) {
            authenticate();
        }
    }
}
?>
