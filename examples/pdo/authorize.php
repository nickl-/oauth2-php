<?php
/**
 * @file
 * Sample authorize endpoint.
 *
 * This sample provides two click-jacking prevention methods, neither which are perfect.
 * The javascript solution is similar to what facebook used to have (but can be defeated with a
 * specially crafted frame-wrapper).
 */

// Clickjacking prevention (supported by IE8+, FF3.6.9+, Opera10.5+, Safari4+, Chrome 4.1.249.1042+)
header('X-Frame-Options: DENY');
require 'OAuth2/Storage/StoragePdo.php';
require_once 'OAuth2/Server/Server.php';
require_once 'OAuth2/Exception/ServerException.php';

$dsn = 'mysql:dbname=testdb;host=127.0.0.1';
$user = 'dbuser';
$password = 'dbpass';

try {
    $db = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

/*
 * You would need to authenticate the user before authorization.
 *
 * Below is some psudeo-code to show what you might do:
 *
session_start();
if (!isLoggedIn()) {
    redirectToLoginPage();
    exit();
}
 */

$oauth = new OAuth2\Server\Server(new OAuth2\Storage\StoragePdo($db));

if ($_POST) {
    $userId = $_SESSION['user_id']; // Use whatever method you have for identifying users.
    $oauth->finishClientAuthorization($_POST["accept"] == "Yep", $userId, $_POST);
}

try {
    $auth_params = $oauth->getAuthorizeParams();
} catch (OAuth2\Exception\ServerException $oauthError) {
    $oauthError->sendHttpResponse();
}

?>
<html>
<head>
<title>Authorize</title>
<script>
    if (top != self) {
        window.document.write("<div style='background:black; opacity:0.5; filter: alpha (opacity = 50); position: absolute; top:0px; left: 0px;"
        + "width: 9999px; height: 9999px; zindex: 1000001' onClick='top.location.href=window.location.href'></div>");
    }
  </script>
</head>
<body>
<form method="post" action="authorize.php">
      <?php foreach ($auth_params as $key => $value) : ?>
          <input type="hidden"
    name="<?php htmlspecialchars($key, ENT_QUOTES); ?>"
    value="<?php echo htmlspecialchars($value, ENT_QUOTES); ?>" />
      <?php endforeach; ?>
      Do you authorize the app to do its thing?
      <p><input type="submit" name="accept" value="Yep" /> <input
    type="submit" name="accept" value="Nope" /></p>
</form>
</body>
</html>
