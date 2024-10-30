<?php
/**
* Listen for Password Protected Plugin by Ben Huson
* https://wordpress.org/plugins/password-protected/
*/
function brutebank_password_protected_show_login($is_active) {
    if ($is_active && !empty($_POST)) {
        $path = '';
        if (isset($_SERVER['HTTP_REFERER'])) {
            $path = substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], '/', 7));
        }
        if (empty($path)) {
            $path = 'na';
        }
    
        $_SESSION['brutebank_pass_attempt'][ $path ] += 1;
    
        $no_auth_cookie = true;
        foreach (array_keys($_COOKIE) as $cookie) {
            if (strpos($cookie, '_password_protected_auth') !== FALSE) {
                $no_auth_cookie = false;
            }
        }
        if ($no_auth_cookie && $_SESSION['brutebank_pass_attempt_check'][ $path ] < $_SESSION['brutebank_pass_attempt'][ $path ]) {
            $_SESSION['brutebank_pass_attempt_check'][ $path ] += 1;   
            $brutebank = new BruteBank_WP();
            $brutebank->reportIP();
        }
    }
    
    return $is_active;
}
add_filter('password_protected_show_login', 'brutebank_password_protected_show_login');