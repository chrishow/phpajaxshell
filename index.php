<?php
/*
 *  © Chris How, Primesolid 2011
 *  All rights reserved.
 */

require_once('config.php');


$show_login_form = TRUE;

ini_set('session.use_trans_sid', false);
session_start();

$username = get_var('username');
$password = get_var('password');
$action = get_var('action');

$login_failed = FALSE;

// check config has been set up
if (!isset($config['username']) || !isset($config['hashed_password'])) {
    die('You have not edited config.php to include your username and hashed password!');
}

if (!defined('CRYPT_BLOWFISH') || CRYPT_BLOWFISH != 1) {
    die('This server does not support blowfish password hashing!');
}


if (isset($_SESSION['ajaxshell_logged_in']) && $_SESSION['ajaxshell_logged_in']) {
    $show_login_form = FALSE;
}

if ($action == 'login') {
    echo $config['hashed_password'] . '<br>';
    $salt = substr($config['hashed_password'], strpos($config['hashed_password'], ':') + 1);
    echo $salt;
    if ($username == $config['username'] && (crypt($password, '$2a$07$' . $salt . '$') . ':' . $salt) == $config['hashed_password']) {
        $_SESSION['ajaxshell_logged_in'] = TRUE;
        $nonce = base64_encode(make_random_string());
        $_SESSION['nonce'] = $nonce;
        header('Location: ' . $_SERVER['PHP_SELF']);
    } else {
        $login_failed = TRUE;
    }
} elseif ($action == 'logout') {
    unset($_SESSION['ajaxshell_logged_in']);
    header('Location: ' . $_SERVER['PHP_SELF']);
}

/////


function get_var($what) {
    return isset($_REQUEST[$what]) ? $_REQUEST[$what] : NULL;
}

function make_random_string($bits = 256) {
    $bytes = ceil($bits / 8);
    $return = '';
    for ($i = 0; $i < $bytes; $i++) {
        $return .= chr(mt_rand(0, 255));
    }
    return $return;
}
?>
<!doctype html>
<html lang="en">
    <head>        
        <meta charset="utf-8">
        <link rel="stylesheet" href="shell.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script src="shell.js"></script>

        <script>
            var NONCE = '<?= $_SESSION['nonce'] ?>';
        </script>

    </head>
    <body>
<?
if ($show_login_form) {
    if ($login_failed) {
        echo "<p>Login failed!</p>";
    }
    ?>
            <div class="login">
                <form method="post">
                    username: <input name="username" value="<?= $username ?>"/><br>
                    password: <input name="password" type="password" value="<?= $password ?>"/><br>
                    <input type="submit" name="action" value="login" />
                </form>
            </div>
<? } else { ?>
            <div id=shell><div id="output"></div><textarea id=input spellcheck="false"></textarea></div>
            <div id="editor"><textarea></textarea>
                <div id="editor_links">

                </div>
            </div>
<? } ?>
    </body>
</html>