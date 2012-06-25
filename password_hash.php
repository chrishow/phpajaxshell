<?php
/*
 *  © Chris How, Primesolid 2011
 *  All rights reserved.
 */

require_once('config.php');

$action = get_var('action');
$password = get_var('password');
$confirm = get_var('confirm');

$hash = NULL;
$error = NULL;

if ($action == 'generate') {
    if ($password != $confirm) {
        $error = "Your password and confirmation did not match!";
    } elseif (!$password) {
        $error = "Please enter and confirm your chosen password";
    } else {
        // generate 
        // find best hashing algorithm
        if (defined('CRYPT_BLOWFISH') && CRYPT_BLOWFISH == 1) {
            $salt = make_salt();
            $hash = crypt($password, '$2a$07$' . $salt . '$') . ':' . $salt;
        } else {
            $error = "This PHP does not support blowfish password hashing!";
        }
    }
} else {
    
}

/////
function get_var($what) {
    return isset($_REQUEST[$what]) ? $_REQUEST[$what] : NULL;
}

function make_salt($length = 22) {
    $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $str = '';
    $count = strlen($charset);
    while ($length--) {
        $str .= $charset[mt_rand(0, $count - 1)];
    }
    return $str;
}
?>
<!doctype html>
<html lang="en">
    <head>        
        <meta charset="utf-8">
        <link rel="stylesheet" href="shell.css">


    </head>
    <div class="login">
        <form method="post">
            <? if (!$hash) { ?>
                <h2>Enter your chosen password here:</h2>
                <?
                if ($error) {
                    echo "<p class=error>{$error}</p>";
                }
                ?>
                <label for="password">Password:</label> <input name="password" id="password" type="password" value="<?= $password ?>"/><br>
                <label for="confirm">Confirm:</label> <input name="confirm" id="confirm" type="password" value="<?= $confirm ?>"/><br>
                <input type="submit" name="action" value="generate" />
                <?
            } else {
                echo "Please copy and paste this string into 'hashed_password' in config.php:<br>";
                echo $hash;
            }
            ?>
        </form>
    </div>
</body>
</html>