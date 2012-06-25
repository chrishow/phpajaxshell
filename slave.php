<?php

/*
 *  © Chris How, Primesolid 2011
 *  All rights reserved.
 */
ini_set('session.use_trans_sid', false);
session_start();

if (!isset($_SESSION['ajaxshell_logged_in']) || !$_SESSION['ajaxshell_logged_in']) {
    header('HTTP/1.1 400 Not authorized');
    exit;
}

$nonce = get_var('nonce');
if ($nonce != $_SESSION['nonce']) {
    header('HTTP/1.1 400 CSRF');
    exit;
}

if (isset($_SESSION['cwd'])) {
    $result = @chdir($_SESSION['cwd']);
    if (!$result) {
        header('HTTP/1.1 400 cannot change to cwd!');
        exit;
    }
} else {
    $_SESSION['cwd'] = getcwd();
}


$cwd = get_var('cwd');
$cmd = get_var('cmd');
$action = get_var('action');

if ($action) {
    if ($action == 'complete') {
        $type = get_var('type');
        $stem = get_var('stem');
        if ($type == 'file') {
            $files = glob("{$stem}*");
            foreach ($files as &$file) {
                if (is_dir($file)) {
                    $file .= '/';
                }
            }
            $json = json_encode($files);
        } elseif ($type == 'command') {
            if (strpos($stem, '/') !== FALSE) { // has explicit path
                $files = glob("{$stem}*");
                if ($files) {
                    foreach ($files as $key => &$file) {
                        if (is_dir($file)) {
                            $file .= '/';
                        } else {
                            if (is_executable($file)) {
                                $file .= ' ';
                            } else {
                                unset($files[$key]);
                            }
                        }
                    }
                    $files = array_values($files);
                }
                $json = json_encode($files);
            } else { // look in $PATH
                $path = exec('echo $PATH');
                $commands = array();
                if ($path) {
                    foreach (explode(':', $path) as $path_item) {
                        $files = glob("{$path_item}/{$stem}*");
                        if ($files) {
                            foreach ($files as $file) {
                                if (is_executable($file) && !is_dir($file)) {
                                    $path_parts = pathinfo($file);
                                    $commands[] = $path_parts['basename'];
                                }
                            }
                        }
                    }
                }
                $json = json_encode($commands);
            }
        }
        header('Content-type: application/json');
        print $json;
    }
    exit;
}

if ($cmd) {
    if (strpos($cmd, 'cd ') === 0) {
        $newDir = substr($cmd, 3);
        if (changeDir($newDir)) {
            print $_SESSION['cwd'];
        } else {
            print "{$newDir}: No such file or directory\n";
        }
    } else {
        header('Content-type: application/octet-stream');
        setup_stream_output();
        $result = system("{$cmd} 2>&1", $return_code);
    }
}

///////////

function changeDir($dir) {
    if (substr($dir, 0, 1) == '/') {
        $fullPath = $dir;
    } else {
        $fullPath = realpath($_SESSION['cwd'] . '/' . $dir);
    }
    if (file_exists($fullPath) && is_dir($fullPath)) {
        $_SESSION['cwd'] = $fullPath;
        return TRUE;
    } else {
        return FALSE;
    }
}

function get_var($what) {
    return isset($_REQUEST[$what]) ? $_REQUEST[$what] : NULL;
}

function setup_stream_output() {
    ob_start('ob_logstdout', 2);
    @apache_setenv('no-gzip', 1);
    @ini_set('zlib.output_compression', 0);
    @ini_set('implicit_flush', 1);
    for ($i = 0; $i < ob_get_level(); $i++) {
        ob_end_flush();
    }
    ob_implicit_flush(TRUE);
    flush();
}

