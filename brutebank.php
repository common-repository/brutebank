<?php
/**
* Plugin Name: BruteBank - WP Security & Firewall
* Plugin URI: https://www.brutebank.io/wordpress-security-plugin
* Description: An interactive firewall plugin that allows Wordpress owners and server administrators to receive real time threat notifications via a mobile app.
* Version: 1.10
* Author: BruteBank
* Author URI: https://www.brutebank.io
**/

global $wpdb;

define( 'BRUTEBANK_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BRUTEBANK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
$brutebank_plugin_version   = '1.10';
$brutebank_db_version       = '1.3';

register_activation_hook(__FILE__, 'brutebank_db_install');
register_deactivation_hook(__FILE__, 'brutebank_db_uninstall');

/**
 * Upgrade database if needed
 */
if (get_option('brutebank_db_version') != $brutebank_db_version) {
    require_once( BRUTEBANK_PLUGIN_PATH . 'includes/db_upgrade.php' );
}

/**
 * Add settings link under plugin on plugins page
 */
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'brutebank_add_settings_link' );

function brutebank_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=brutebank-settings">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
      return $links;
}

/**
 * Add menu item to wp-admin
 */
function brutebank_admin_menu() {
    add_menu_page(
        'BruteBank', 
        'BruteBank', 
        'manage_options', 
        'brutebank-settings', 
        'brutebank_settings_page',
'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDI0LjIuMSwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiCgkgdmlld0JveD0iMCAwIDM2IDM2IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAzNiAzNjsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPgoJLnN0MHtmaWxsOiNGRkZGRkY7fQo8L3N0eWxlPgo8ZyBpZD0iTGF5ZXJfMSI+CjwvZz4KPGcgaWQ9IkxheWVyXzIiPgoJPHBhdGggaWQ9ImJibG9nbyIgY2xhc3M9InN0MCIgZD0iTTIyLjMsOC45bDYuNyw2LjdsLTQuOCw0LjhsLTIsMmwtNC44LDQuOEw2LjksMTYuNWwyLTJsMy45LDMuOWwyLjgtMi44bC0zLjktMy45bDItMmwzLjksMy45CgkJTDIyLjMsOC45IE0yMi4zLDE4LjRsMi44LTIuOGwtMi44LTIuOGwtMi44LDIuOEwyMi4zLDE4LjQgTTE3LjYsMjMuMmwyLjgtMi44bC0wLjctMC43bC0yLjEtMi4xbC0yLjgsMi44TDE3LjYsMjMuMiBNMjIuMyw1bC0yLDIKCQlsLTIuOCwyLjhsLTItMmwtMi0ybC0yLDJsLTIsMmwtMiwybDAsMGwtMC45LDAuOWwtMiwybC0yLDJsMiwybDEwLjcsMTAuN2wyLDJsMi0ybDQuOC00LjhsMi0ybDQuOC00LjhsMi0ybC0yLTJsLTYuNy02LjdMMjIuMyw1CgkJTDIyLjMsNXoiLz4KPC9nPgo8L3N2Zz4K'
    );
}
add_action( 'admin_menu', 'brutebank_admin_menu' );

/**
 * Create settings page
 */
function brutebank_settings_page() {
    if (!current_user_can( 'manage_options')) {
        wp_die( __('Access denied', 'brutebank') );
    }

    $brutebank_options = get_option( 'brutebank-settings' );
    require_once( BRUTEBANK_PLUGIN_PATH . 'admin/settings.php' );
}

/**
 * Register settings in the database
 */
function brutebank_register_settings() {
    register_setting('brutebank_settings_group', 'brutebank-settings');
}
add_action('admin_init', 'brutebank_register_settings');

/**
 * Database install
 */
function brutebank_db_install() {
    global $wpdb;
    global $brutebank_db_version;

    $table_name = $wpdb->prefix.'brutebank_settings';
   
    $charset_collate = $wpdb->get_charset_collate();
    
    $wpdb->query('DROP TABLE IF EXISTS '.$table_name);
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        enabled tinyint(1) DEFAULT 0 NOT NULL,
        xmlrpc tinyint(1) DEFAULT 0 NOT NULL,
        two_factor tinyint(1) DEFAULT 0 NOT NULL,
        public_key varchar(255) DEFAULT '' NOT NULL,
        secret_key varchar(255) DEFAULT '' NOT NULL,
        updated_at timestamp,
        cache_updated timestamp,
        PRIMARY KEY  (id)
    ) $charset_collate;";
   
    $wpdb->query($sql);
    
    $table_name = $wpdb->prefix.'brutebank_blocks';
    $wpdb->query('DROP TABLE IF EXISTS '.$table_name);
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ip_address varchar(255) DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    $wpdb->query($sql);
    
    add_option('brutebank_db_version', $brutebank_db_version);
}

/**
 * Database uninstall
 */
function brutebank_db_uninstall() {
    global $wpdb;
   
    $table_name = $wpdb->prefix.'brutebank_settings';
    $wpdb->query('DROP TABLE IF EXISTS '.$table_name);
    
    $table_name = $wpdb->prefix.'brutebank_blocks';
    $wpdb->query('DROP TABLE IF EXISTS '.$table_name);
}

/**
 * WP Admin login Header
 */
function brutebank_admin_login_header() {
    $brutebank = new BruteBank_WP();
    $brutebank->cachedCheckIP();
    $brutebank->checkIP();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $brutebank->reportIP();
    } else {
        if ($_GET['BB_2fa'] == 'expired' || $_GET['BB_2fa'] == 'denied') {
            $brutebank->deniedBanner();
        }
    }
}
add_action('login_header', 'brutebank_admin_login_header', 0);

/**
 * WP Admin logging
 */
function brutebank_admin_header() {
    $brutebank = new BruteBank_WP();
    $brutebank->handleTwoFactor();
}
add_action('admin_head', 'brutebank_admin_header', 0);

/**
 * Block at website level
 */
function brutebank_wp_header() {
    $brutebank = new BruteBank_WP();
    $brutebank->cachedCheckIP();
}
add_action('wp_head', 'brutebank_wp_header', 0);


function brutebank_login_post() {
    $path = substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], '/', 7));
    if (empty($path)) {
        $path = 'na';
    }
    $_SESSION['brutebank_pass_attempt'][ $path ] += 1;
}
add_filter('login_form_postpass', 'brutebank_login_post');


function brutebank_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'brutebank_session', 1);

function my_password_form($post = 0) {
    $path = '';
    if (isset($_SERVER['HTTP_REFERER'])) {
        $path = substr($_SERVER['HTTP_REFERER'], strpos($_SERVER['HTTP_REFERER'], '/', 7));
    }
    if (empty($path)) {
        $path = 'na';
    }
    
    if (isset($_COOKIE[ 'wp-postpass_' . COOKIEHASH ]) && $_SESSION['brutebank_pass_attempt_check'][ $path ] < $_SESSION['brutebank_pass_attempt'][ $path ]) {
        $_SESSION['brutebank_pass_attempt_check'][ $path ] += 1;
        $brutebank = new BruteBank_WP();
        $brutebank->reportIP();
    }
    
    return $post;
}
add_filter('the_password_form', 'my_password_form');


/**
* 3rd Party plugin support
*/
require_once( BRUTEBANK_PLUGIN_PATH . 'includes/plugin_setup.php' );


/**
 * Remove XMLRPC methods
 */
function remove_xmlrpc_methods( $methods ) {
    $methods = [];
    return $methods;
}
$table_name = $wpdb->prefix.'brutebank_settings';
$sql = 'SELECT * FROM '.$table_name;
$results = $wpdb->get_results($sql);
foreach ($results as $result) {
    if ($result->enabled && $result->xmlrpc) {
        add_filter('xmlrpc_methods', 'remove_xmlrpc_methods');
        add_filter('xmlrpc_enabled', '__return_false');
    }
}


/**
 * BruteBank object to manage requests
 */
class BruteBank_WP {
    
    public $brutebank_api = 'https://www.brutebank.io';
    // public $brutebank_api = 'http://brutebank2.dev7.etecc.com';
    public $enabled = 0;
    public $public_key = '';
    public $secret_key = '';
    public $two_factor = 0;
    public $cache_updated = 0;
    
    public function __construct() {
        global $wpdb;
        
        $table_name = $wpdb->prefix.'brutebank_settings';
        $sql = 'SELECT * FROM '.$table_name;
        $results = $wpdb->get_results($sql);
        foreach ($results as $result) {
            $this->enabled          = $result->enabled;
            $this->public_key       = $result->public_key;
            $this->secret_key       = $result->secret_key;
            $this->two_factor       = $result->two_factor;
            $this->cache_updated    = $result->cache_updated;
        }
    }
    
    /**
    * Handle 2fa requests
    */
    public function handleTwoFactor() {
        if ($this->two_factor && $this->enabled) {
            $bb_two_factor_meta = json_decode(get_user_meta(wp_get_current_user()->data->ID, 'BB_2fa', true));
            if ((is_user_logged_in() && !isset($bb_two_factor_meta)) || 
                (is_user_logged_in() && isset($bb_two_factor_meta) && (isset($bb_two_factor_meta->ip_address) && $bb_two_factor_meta->ip_address != $this->getClientIP() || $bb_two_factor_meta->expires < date('Y-m-d H:i:s')))
            ) {
                $this->twoFactorBannerAndApiCalls();
                exit;
            }
        }
    }
    
    /**
     * denied banner
     */
    public function twoFactorBannerAndApiCalls() {
        global $wpdb;
        ?>
        <!-- CSS CODE -->
        <style>
            html{font-family:sans-serif;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%}body{margin:0}article,aside,details,figcaption,figure,footer,header,hgroup,main,nav,section,summary{display:block}audio,canvas,progress,video{display:inline-block;vertical-align:baseline}audio:not([controls]){display:none;height:0}[hidden],template{display:none}a{background:transparent}a:active,a:hover{outline:0}abbr[title]{border-bottom:1px dotted}b,strong{font-weight:700}dfn{font-style:italic}mark{background:#ff0;color:#000}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sup{top:-.5em}sub{bottom:-.25em}img{border:0}svg:not(:root){overflow:hidden}figure{margin:1em 40px}hr{-moz-box-sizing:content-box;box-sizing:content-box;height:0}pre{overflow:auto}code,kbd,pre,samp{font-family:monospace,monospace;font-size:1em}button,input,optgroup,select,textarea{color:inherit;font:inherit;margin:0}button{overflow:visible}button,select{text-transform:none}button,html input[type="button"],input[type="reset"],input[type="submit"]{-webkit-appearance:button;cursor:pointer}button[disabled],html input[disabled]{cursor:default}button::-moz-focus-inner,input::-moz-focus-inner{border:0;padding:0}input{line-height:normal}input[type="checkbox"],input[type="radio"]{box-sizing:border-box;padding:0}input[type="number"]::-webkit-inner-spin-button,input[type="number"]::-webkit-outer-spin-button{height:auto}input[type="search"]{-webkit-appearance:textfield;-moz-box-sizing:content-box;-webkit-box-sizing:content-box;box-sizing:content-box}input[type="search"]::-webkit-search-cancel-button,input[type="search"]::-webkit-search-decoration{-webkit-appearance:none}fieldset{border:none;margin:0 2px}legend{border:0;padding:0}textarea{overflow:auto}optgroup{font-weight:700}table{border-collapse:collapse;border-spacing:0}td,th{padding:0}html{box-sizing:border-box}ul{list-style-type:none;padding:0}
            
            .alert-bb {
                padding:12px; 
                width:100%; 
                background:rgb(249,131,141); 
                background:-moz-linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%); 
                background:-webkit-linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%); background:linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%); filter:progid:DXImageTransform.Microsoft.gradient(startColorstr="#f9838d",endColorstr="#fcbe64",GradientType=1); display:flex; 
                position:absolute; 
                top:0;
                align-items:center;
                overflow: hidden;
                box-sizing: border-box;
            }
            
            .alert-bb > svg:last-child{
                margin-left:auto;
                margin-right:20px;
            }
            
            .alert-bb .bb, .alert-bb .fp{
                width:46px; 
                height:46px; 
                -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
            }
            
            .alert-bb p{
                color:#fff; 
                margin:0; 
                font-size:14px;
                font-family:helvetica, arial, sans-serif; 
                font-weight:normal;
            }
            
            .alert-bb p a{
                text-decoration:underline; 
                color:#fff;
            }
            
            .alert-bb p span{
                border:2px solid #fff; 
                border-radius:2px; 
                padding:4px 6px; 
                margin:0 10px 0 20px;
            }
            .lds-ring {
                /* display: inline-block; */
                /* position: relative; */
                width: 80px;
                height: 80px;
                margin: 0 auto;
                margin-top: 100px;
            }
            .lds-ring div {
                box-sizing: border-box;
                display: block;
                position: absolute;
                width: 64px;
                height: 64px;
                margin: 8px;
                border: 8px solid #fff;
                border-radius: 50%;
                animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
                border-color: #f9838d transparent transparent transparent;
            }
            .lds-ring div:nth-child(1) {
                animation-delay: -0.45s;
            }
            .lds-ring div:nth-child(2) {
                animation-delay: -0.3s;
            }
            .lds-ring div:nth-child(3) {
                animation-delay: -0.15s;
            }
            @keyframes lds-ring {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }
            
            @-webkit-keyframes pulsator{
              0%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }
              50%{
                  fill:#fffeaf; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1));
              }
              100%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }    
            }
            
            @keyframes pulsator{
              0%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }
              50%{
                  fill:#fffeaf; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1));
              }
              100%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }
            }
            
            .pulser{
                -webkit-animation:pulsate 6s infinite; 
                animation:pulsator 6s infinite;
            }
            
            @media (max-width:767px){
                .alert-bb p{
                    flex:1 0 auto; 
                    align-self:center;
                }
            }
            
            @media (max-width:500px){
                .alert-bb p{
                    flex:1 0 auto; 
                    align-self:center;
                }
                
                .alert-bb > svg:last-child{
                    display:none;
                }
            }
        </style>
        <!-- BANNER HTML CODE -->
        <div class="alert-bb"><div></div>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 17.4" class="bb">
        <path d="M22.9,14.4l4.5,4.5-3.2,3.2-1.3,1.3-3.2,3.2-7.1-7.1,1.3-1.3,2.6,2.6,1.9-1.9-2.6-2.6L17.1,15l2.6,2.6,3.2-3.2m0,6.4,1.9-1.9L22.9,17,21,18.9l1.9,1.9M19.7,24l1.9-1.9-.5-.5-1.4-1.4-1.9,1.9L19.7,24m3.2-12.2-1.3,1.3L19.7,15l-1.3-1.3-1.3-1.3-1.3,1.3L14.5,15l-1.3,1.3h0l-.6.6-1.3,1.3L10,19.5l1.3,1.3,7.1,7.1,1.3,1.3L21,27.9l3.2-3.2,1.3-1.3,3.2-3.2L30,18.9l-1.3-1.3-4.5-4.5-1.3-1.3Z" transform="translate(-10 -11.8)" style="fill:#fff;"/>
        </svg>
        <p><span>2FA Requested</span> Protected by <a href="https://www.brutebank.io" target="_blank"> BruteBank.io </a></p>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 35 30.8" class="fp">
          <path d="M34.5,4.6H5.5a3.1,3.1,0,0,0-3,3V32.4a3.1,3.1,0,0,0,3,3h29a3,3,0,0,0,3-3V7.6A3,3,0,0,0,34.5,4.6Zm-29,2h29a1,1,0,0,1,1,1v4.5H4.5V7.6A1.1,1.1,0,0,1,5.5,6.6Zm29,26.8H5.5a1.1,1.1,0,0,1-1-1V14.1h31V32.4A1,1,0,0,1,34.5,33.4Zm-25-24a1.4,1.4,0,0,1-1.4,1.4A1.4,1.4,0,0,1,8.1,8,1.4,1.4,0,0,1,9.5,9.4ZM11.4,8a1.4,1.4,0,0,0,0,2.8,1.4,1.4,0,0,0,0-2.8Zm3.2,0a1.3,1.3,0,0,0-1.3,1.4,1.3,1.3,0,0,0,1.3,1.4,1.4,1.4,0,0,0,0-2.8ZM27.9,23.9A7.9,7.9,0,1,1,20,16,7.9,7.9,0,0,1,27.9,23.9Zm-3.8-1.1H15.9v2.1h8.2Z" transform="translate(-2.5 -4.6)" style="fill: #fff"/>
        </svg>
    </div>
    <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
        <?php
        $this->doFlush();
        
        // Create request @ BB
        $response = wp_remote_post($this->brutebank_api.'/api/2fa', 
            [
                'body' => [
                    'user' => wp_get_current_user()->data->user_login,
                    'ip_address' => $this->getClientIP(),
                    'public_key' => $this->public_key
                ],
                'headers' => [
                    'accept' => 'application/json',
                ]
            ]
        );
        
        if (isset($response) && is_array($response) && isset($response['body'])) {
            $json = json_decode($response['body']);
            if ($json->status == 1) {
                for ($i=0; $i<30; $i++) {
                    usleep(1000000);
                    $this->doFlush();
                    $this->getTwoFactorResponse($json->request->id);
                }
                
                $this->updateTwoFactor($json->request->id, 'expired');
                wp_logout();
                ?>
                <meta http-equiv="refresh" content="0; url=<?php echo wp_login_url(); ?>?BB_2fa=expired" />
                <?php
                $this->doFlush();
                exit;
            } else {
                // Invalid server keys
                $table_name = $wpdb->prefix.'brutebank_settings';
                $sql = 'UPDATE '.$table_name.' SET '.
                        'enabled = 0, two_factor = 0';
                $wpdb->query($sql);
                ?>
                <meta http-equiv="refresh" content="0; url=?BB_2fa=invalid_keys" />
                <?php
                $this->doFlush();
                exit;
            }
        }
    }
    
    /** 
    * Flush the ob
    */
    private function doFlush() {
        if (!headers_sent()) {
            // Disable gzip in PHP.
            ini_set('zlib.output_compression', 0);
    
            // Force disable compression in a header.
            // Required for flush in some cases (Apache + mod_proxy, nginx, php-fpm).
            header('Content-Encoding: none');
        }
        
        // Fill-up 4 kB buffer (should be enough in most cases).
        echo str_pad('', 4 * 1024);
        
        // Flush all buffers.
        do {
            $flushed = @ob_end_flush();
        } while ($flushed);
        
        @ob_flush();
        flush();
    }
    
    /**
    * Check to see if the request was denied
    */
    private function getTwoFactorResponse($id) {
        $response = wp_remote_get($this->brutebank_api.'/api/2fa/'.$id, 
            [
                'headers' => [
                    'accept' => 'application/json',
                ]
            ]
        );
        
        if (isset($response) && isset($response['body'])) {
            $json = json_decode($response['body']);
            if ($json->status == 1) {
                if ($json->request->status == "denied") {
                    wp_logout();
                    ?>
                    <meta http-equiv="refresh" content="0; url=<?php echo wp_login_url(); ?>?BB_2fa=denied" />
                    <?php
                    $this->doFlush();
                    exit;
                } else if ($json->request->status == "allowed") {
                    update_user_meta(wp_get_current_user()->data->ID, 'BB_2fa', json_encode([
                        'status' => 'allowed',
                        'ip_address' => $this->getClientIP(),
                        'expires' => date('Y-m-d H:i:s', strtotime('+24 hours'))
                    ]));
                    ?>
                    <meta http-equiv="refresh" content="0; url=?BB_2fa=allowed" />
                    <?php
                    $this->doFlush();
                    exit;
                }
            }
        }
    }
    
    /**
    * Update two factor request
    */
    private function updateTwoFactor($id, $status) {
        $response = wp_remote_post($this->brutebank_api.'/api/2fa/'.$id, 
            [
                'body' => [
                    'status' => $status,
                ],
                'headers' => [
                    'accept' => 'application/json',
                ]
            ]
        );
    }
    
    /**
     * denied banner
     */
    public function deniedBanner() {
        ?>
        <!-- CSS CODE -->
        <style>
            html{font-family:sans-serif;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%}body{margin:0}article,aside,details,figcaption,figure,footer,header,hgroup,main,nav,section,summary{display:block}audio,canvas,progress,video{display:inline-block;vertical-align:baseline}audio:not([controls]){display:none;height:0}[hidden],template{display:none}a{background:transparent}a:active,a:hover{outline:0}abbr[title]{border-bottom:1px dotted}b,strong{font-weight:700}dfn{font-style:italic}mark{background:#ff0;color:#000}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sup{top:-.5em}sub{bottom:-.25em}img{border:0}svg:not(:root){overflow:hidden}figure{margin:1em 40px}hr{-moz-box-sizing:content-box;box-sizing:content-box;height:0}pre{overflow:auto}code,kbd,pre,samp{font-family:monospace,monospace;font-size:1em}button,input,optgroup,select,textarea{color:inherit;font:inherit;margin:0}button{overflow:visible}button,select{text-transform:none}button,html input[type="button"],input[type="reset"],input[type="submit"]{-webkit-appearance:button;cursor:pointer}button[disabled],html input[disabled]{cursor:default}button::-moz-focus-inner,input::-moz-focus-inner{border:0;padding:0}input{line-height:normal}input[type="checkbox"],input[type="radio"]{box-sizing:border-box;padding:0}input[type="number"]::-webkit-inner-spin-button,input[type="number"]::-webkit-outer-spin-button{height:auto}input[type="search"]{-webkit-appearance:textfield;-moz-box-sizing:content-box;-webkit-box-sizing:content-box;box-sizing:content-box}input[type="search"]::-webkit-search-cancel-button,input[type="search"]::-webkit-search-decoration{-webkit-appearance:none}fieldset{border:none;margin:0 2px}legend{border:0;padding:0}textarea{overflow:auto}optgroup{font-weight:700}table{border-collapse:collapse;border-spacing:0}td,th{padding:0}html{box-sizing:border-box}ul{list-style-type:none;padding:0}
            
            .alert-bb {
                padding:12px; 
                width:100%; 
                background:rgb(249,131,141); 
                background:-moz-linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%); 
                background:-webkit-linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%); background:linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%); filter:progid:DXImageTransform.Microsoft.gradient(startColorstr="#f9838d",endColorstr="#fcbe64",GradientType=1); display:flex; 
                position:relative; 
                align-items:center;
                overflow: hidden;
                box-sizing: border-box;
            }
            
            .alert-bb > svg:last-child{
                margin-left:auto;
                margin-right:20px;
            }
            
            .alert-bb .bb, .alert-bb .fp{
                width:46px; 
                height:46px; 
                -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
            }
            
            .alert-bb p{
                color:#fff; 
                margin:0; 
                font-size:14px;
                font-family:helvetica, arial, sans-serif; 
                font-weight:normal;
            }
            
            .alert-bb p a{
                text-decoration:underline; 
                color:#fff;
            }
            
            .alert-bb p span{
                border:2px solid #fff; 
                border-radius:2px; 
                padding:4px 6px; 
                margin:0 10px 0 20px;
            }
            
            @-webkit-keyframes pulsator{
              0%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }
              50%{
                  fill:#fffeaf; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1));
              }
              100%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }    
            }
            
            @keyframes pulsator{
              0%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }
              50%{
                  fill:#fffeaf; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1));
              }
              100%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }
            }
            
            .pulser{
                -webkit-animation:pulsate 6s infinite; 
                animation:pulsator 6s infinite;
            }
            
            @media (max-width:767px){
                .alert-bb p{
                    flex:1 0 auto; 
                    align-self:center;
                }
            }
            
            @media (max-width:500px){
                .alert-bb p{
                    flex:1 0 auto; 
                    align-self:center;
                }
                
                .alert-bb > svg:last-child{
                    display:none;
                }
            }
        </style>
        <!-- BANNER HTML CODE -->
        <div class="alert-bb"><div></div>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 17.4" class="bb">
        <path d="M22.9,14.4l4.5,4.5-3.2,3.2-1.3,1.3-3.2,3.2-7.1-7.1,1.3-1.3,2.6,2.6,1.9-1.9-2.6-2.6L17.1,15l2.6,2.6,3.2-3.2m0,6.4,1.9-1.9L22.9,17,21,18.9l1.9,1.9M19.7,24l1.9-1.9-.5-.5-1.4-1.4-1.9,1.9L19.7,24m3.2-12.2-1.3,1.3L19.7,15l-1.3-1.3-1.3-1.3-1.3,1.3L14.5,15l-1.3,1.3h0l-.6.6-1.3,1.3L10,19.5l1.3,1.3,7.1,7.1,1.3,1.3L21,27.9l3.2-3.2,1.3-1.3,3.2-3.2L30,18.9l-1.3-1.3-4.5-4.5-1.3-1.3Z" transform="translate(-10 -11.8)" style="fill:#fff;"/>
        </svg>
        <p><span>Request Denied</span> Protected by <a href="https://www.brutebank.io" target="_blank"> BruteBank.io </a></p>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 35 30.8" class="fp">
          <path d="M34.5,4.6H5.5a3.1,3.1,0,0,0-3,3V32.4a3.1,3.1,0,0,0,3,3h29a3,3,0,0,0,3-3V7.6A3,3,0,0,0,34.5,4.6Zm-29,2h29a1,1,0,0,1,1,1v4.5H4.5V7.6A1.1,1.1,0,0,1,5.5,6.6Zm29,26.8H5.5a1.1,1.1,0,0,1-1-1V14.1h31V32.4A1,1,0,0,1,34.5,33.4Zm-25-24a1.4,1.4,0,0,1-1.4,1.4A1.4,1.4,0,0,1,8.1,8,1.4,1.4,0,0,1,9.5,9.4ZM11.4,8a1.4,1.4,0,0,0,0,2.8,1.4,1.4,0,0,0,0-2.8Zm3.2,0a1.3,1.3,0,0,0-1.3,1.4,1.3,1.3,0,0,0,1.3,1.4,1.4,1.4,0,0,0,0-2.8ZM27.9,23.9A7.9,7.9,0,1,1,20,16,7.9,7.9,0,0,1,27.9,23.9Zm-3.8-1.1H15.9v2.1h8.2Z" transform="translate(-2.5 -4.6)" style="fill: #fff"/>
        </svg>
    </div>
        <?php
    }
    
    /**
     * logged banner
     */
    public function loggedBanner() {
        ob_start();
        ?>
        <!-- CSS CODE -->
        <style>
            .alert-bb {
                padding:12px; 
                width:100%; 
                background:rgb(249,131,141); 
                background:-moz-linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%); 
                background:-webkit-linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%); background:linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%); filter:progid:DXImageTransform.Microsoft.gradient(startColorstr="#f9838d",endColorstr="#fcbe64",GradientType=1); display:flex; 
                position:absolute; 
                top:0;
                align-items:center;
                overflow: hidden;
                box-sizing: border-box;
            }
            
            .alert-bb > svg:last-child{
                margin-left:auto;
                margin-right:20px;
            }
            
            .alert-bb .bb, .alert-bb .fp{
                width:46px; 
                height:46px; 
                -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
            }
            
            .alert-bb p{
                color:#fff; 
                margin:0; 
                font-size:14px;
                font-family:helvetica, arial, sans-serif; 
                font-weight:normal;
            }
            
            .alert-bb p a{
                text-decoration:underline; 
                color:#fff;
            }
            
            .alert-bb p span{
                border:2px solid #fff; 
                border-radius:2px; 
                padding:4px 6px; 
                margin:0 10px 0 20px;
            }
            
            @-webkit-keyframes pulsator{
              0%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }
              50%{
                  fill:#fffeaf; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1));
              }
              100%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }    
            }
            
            @keyframes pulsator{
              0%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }
              50%{
                  fill:#fffeaf; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(200, 193, 0, 1));
              }
              100%{
                  fill:#ffffff; 
                  -webkit-filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1)); 
                  filter:drop-shadow(2px 2px 0px rgba(227, 116, 130, 1));
              }
            }
            
            .pulser{
                -webkit-animation:pulsate 6s infinite; 
                animation:pulsator 6s infinite;
            }
            
            @media (max-width:767px){
                .alert-bb p{
                    flex:1 0 auto; 
                    align-self:center;
                }
            }
            
            @media (max-width:500px){
                .alert-bb p{
                    flex:1 0 auto; 
                    align-self:center;
                }
                
                .alert-bb > svg:last-child{
                    display:none;
                }
            }
        </style>
        <!-- BANNER HTML CODE -->
        <div class="alert-bb"><div></div>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 17.4" class="bb">
        <path d="M22.9,14.4l4.5,4.5-3.2,3.2-1.3,1.3-3.2,3.2-7.1-7.1,1.3-1.3,2.6,2.6,1.9-1.9-2.6-2.6L17.1,15l2.6,2.6,3.2-3.2m0,6.4,1.9-1.9L22.9,17,21,18.9l1.9,1.9M19.7,24l1.9-1.9-.5-.5-1.4-1.4-1.9,1.9L19.7,24m3.2-12.2-1.3,1.3L19.7,15l-1.3-1.3-1.3-1.3-1.3,1.3L14.5,15l-1.3,1.3h0l-.6.6-1.3,1.3L10,19.5l1.3,1.3,7.1,7.1,1.3,1.3L21,27.9l3.2-3.2,1.3-1.3,3.2-3.2L30,18.9l-1.3-1.3-4.5-4.5-1.3-1.3Z" transform="translate(-10 -11.8)" style="fill:#fff;"/>
        </svg>
        <p><span>Invalid Login</span>Reported to <a href="http://www.brutebank.io" target="_blank">BruteBank.io</a> for fingerprinting</p>
        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 94.9 112" class="fp pulser">
            <path class="st0" d="M70.8,32.3c1,0.9,1.1,1.9,0.5,2.6c-0.7,0.7-1.6,0.6-2.6-0.3c-5.9-5.8-13.1-8.5-21.3-8.7c-0.3,0-0.6,0-0.8,0 c-8.4,0.4-15.4,3.6-21.3,9.6c-2.7,2.8-4.7,5.9-6.2,9.5c-0.1,0.2-0.2,0.4-0.3,0.7c-0.4,0.8-1,1.2-1.9,0.9c-0.9-0.4-1.2-1.1-0.9-1.9 c0.7-1.7,1.4-3.4,2.3-5c6.7-11,16.6-16.7,29.4-16.7C56.7,22.8,64.4,26.1,70.8,32.3z M83,37.9c-1.7-3.4-3.9-6.5-6.6-9.2 c-7.7-8-17.2-12.1-28.3-12.3c-7.8-0.2-15,1.9-21.6,5.9c-1.1,0.7-1.4,1.5-0.9,2.4c0.5,0.8,1.4,0.9,2.4,0.3c5.5-3.4,11.5-5.3,18-5.5 c8.7-0.3,16.5,2.1,23.5,7.3c4.5,3.3,8,7.5,10.7,12.4c0.3,0.6,0.9,1,1.1,1.3C83.1,40.5,83.6,39.2,83,37.9z M70,75.2
            c-0.1,0.8,0.4,1.6,1.2,1.9c0.6,0.2,1.2-0.1,1.4-0.7c0.5-1.4,0.8-2.9,0.8-4.3c0-0.6,0.6-5.1,0.8-6c0.1-0.3,0.1-1.5,0.1-1.5
            c0.1-1.2,0.2-2.4,0.3-3.6c0.6-7.6-0.7-14.8-5.6-21c-6.7-8.4-15.5-11.8-26.1-10.2c-0.9,0.1-1.4,0.7-1.3,1.7c0.1,0.9,0.8,1.5,1.8,1.3 c0.6-0.1,1.2-0.2,1.8-0.2c12.9-1.4,25,8.3,26.3,21.2c0.6,5.7-0.2,11.3-0.8,16.9C70.5,72.3,70.2,73.3,70,75.2z M67.9,52.7 c-2.7-13.9-17.6-21.1-30.2-14.5c-3.3,1.7-6,4.2-7.9,7.5c-0.5,0.9-0.4,1.6,0.4,2.2c0.9,0.6,1.6,0.2,2.2-0.5c0.1-0.2,0.2-0.4,0.4-0.6 c6.3-9.4,19.7-10.6,27.5-2.4c3.6,3.8,5.2,8.4,4.9,13.7c0,0.9,0,1.8,0,2.8c0,1,0.4,1.7,1.4,1.7c1,0.1,1.6-0.6,1.6-1.5 C68.1,58.1,68.4,55.3,67.9,52.7z M48.9,56.1c-0.3-0.5-0.9-1-1.5-1.1c-0.7-0.1-1.3,0.4-1.4,1.3c0,0.4,0,0.9,0,1.3
            c-0.1,4.6-0.6,9.1-1.5,13.5c-2.4,11.6-7.4,22-14.8,31.3c-0.8,1-0.8,1.9,0,2.5c0.8,0.6,1.7,0.4,2.5-0.6c10.8-13.5,16.4-29,17-46.3 C49,57.4,49.2,56.6,48.9,56.1z M54.6,68.7c-0.1-1-0.4-1.7-1.4-1.8c-0.9-0.1-1.6,0.3-1.7,1.2c-0.3,1.6-0.6,3.1-0.9,4.7 c-2.5,11.8-7.5,22.5-14.9,32.1c-0.8,1-0.7,1.9,0.1,2.5c0.8,0.6,1.5,0.4,2.3-0.7c7.1-8.9,11.9-19,14.7-30
            C53.6,74,54.1,71.2,54.6,68.7z M30.1,52.7c0.1-0.9-0.2-1.6-1.2-1.7c-1-0.2-1.7,0.4-1.8,1.3c-0.2,1.9-0.4,3.9-0.5,5.8
            c-0.3,9.3-2.8,17.9-7.6,25.8c-1.3,2.1-2.8,4.1-4.3,6.2c-0.6,0.8-0.5,1.6,0.2,2.2c0.7,0.6,1.5,0.6,2.1-0.2c0.3-0.3,0.6-0.6,0.9-1 c4.5-5.9,7.8-12.5,9.8-19.7C29.5,65.3,29.7,59,30.1,52.7z M42.8,46.2c0.5-0.2,0.8-0.8,1-1c0-1.6-1.2-2.3-2.5-1.6 c-5.3,2.7-8.1,7-8.2,12.9c-0.1,5.7-0.8,11.4-2.5,16.9c-0.5,1.8-1.2,3.6-1.9,5.4c-0.3,1-0.2,1.8,0.8,2.2c1,0.4,1.6-0.1,2.1-1 c0.1-0.2,0.2-0.4,0.3-0.7c2.7-7,4.2-14.3,4.2-21.8C36.2,52.3,38.2,48.5,42.8,46.2z M56.9,85.8c1.1-4.3,2.3-8.5,3.4-12.8 c0.3-1-0.3-1.8-1.2-2c-0.9-0.2-1.5,0.4-1.8,1.5c-0.6,2.6-1.2,5.2-1.9,7.8c-2.7,9.4-6.9,18.1-12.6,26c-0.8,1.1-0.7,1.9,0.1,2.5 c0.8,0.5,1.7,0.3,2.3-0.7c4-5.5,7.2-11.4,9.9-17.6C55.4,89.9,56.9,85.9,56.9,85.8z M55.4,56.5c-0.1-4.5-3.5-7.9-8-7.9 c-4.4,0-7.8,3.4-7.9,7.9c0,1.8-0.1,3.6-0.2,5.4c-0.1,1.3-0.3,2.6-0.4,3.9c-0.1,0.9,0.3,1.6,1.3,1.7c0.9,0.1,1.5-0.3,1.8-1.2 c0.1-0.5,0.2-0.9,0.2-1.4c0.2-2.8,0.4-5.6,0.5-8.4c0.1-2.7,2.1-4.8,4.6-4.9c2.7-0.1,4.9,1.7,5.1,4.5c0.1,1.9-0.1,3.7-0.1,5.6 c0,1,0.4,1.6,1.4,1.7c0.9,0.1,1.6-0.5,1.7-1.6c0.1-1,0-2,0-3c0.1,0,0.1,0,0.2,0C55.4,58.1,55.5,57.3,55.4,56.5z M25.2,15.6 c-0.8,0.4-1.3,1.1-0.8,2.1c0.4,0.8,1.3,1.1,2.3,0.6c0.4-0.2,0.8-0.4,1.3-0.7c7.6-3.7,15.5-5.2,23.9-4.4c2.5,0.3,5,0.9,7.5,1.4 c0.9,0.2,1.7-0.1,2-1c0.3-1-0.2-1.6-1.1-1.9c-0.3-0.1-0.5-0.2-0.8-0.2c-3.9-1-7.9-1.6-11.7-1.5c-6.5,0-12.4,1.2-18.1,3.5 C28.1,14.1,26.7,14.9,25.2,15.6z M64.3,83.9c0.5-1.4,0.9-2.8,1.1-4.3c0.1-0.4,0.1-0.6,0.1-0.6c0.4-1.7,0.8-3.3,1.1-4.9 c0.2-0.9-0.1-1.7-1.1-1.9c-1-0.2-1.6,0.3-1.8,1.3c-1.1,6.9-3.6,13.5-3.7,13.9c-2.5,6.6-5.5,13-9.4,18.9c-0.7,1.1-0.6,2,0.2,2.5 c0.9,0.6,1.8,0.3,2.5-0.9c3-4.8,5.7-9.7,7.8-14.9C62.2,90,63.2,87,64.3,83.9z M21.6,60.6c0.9,0.1,1.6-0.5,1.7-1.6 c0.1-0.6,0.1-1.3,0.1-1.9c-0.1-7.7,2.9-14.1,8.7-19.1c1-0.9,1.2-1.7,0.6-2.5c-0.6-0.8-1.6-0.8-2.6,0.1c-6.4,5.4-9.6,12.4-9.9,20.7 c0,0.8,0,1.6,0,2.4C20.2,59.7,20.7,60.5,21.6,60.6z M70.3,80.4c-0.9-0.3-1.7,0.3-2,1.4c-2.1,7.9-5.2,15.4-9.1,22.5
            c-0.2,0.4-0.4,0.8-0.7,1.4c0.5,0.4,0.9,1.1,1.4,1.2c0.5,0.1,1.3-0.3,1.7-0.7c0.7-0.9,1.2-2,1.7-3c3.4-6.6,6.1-13.4,8-20.6
            C71.6,81.5,71.2,80.7,70.3,80.4z M10.5,55.7c0.1-1.4,0.1-2.9,0.3-4.3c0.9-6.5,3.4-12.4,7.5-17.6c0.3-0.3,0.6-0.8,0.6-1.1
            c-0.1-0.6-0.4-1.3-0.8-1.5c-0.9-0.6-1.6,0.1-2.2,0.8c-5.3,6.9-8.1,14.7-8.4,23.4c0,0.1,0,0.2,0,0.4c0,1.3,0.6,2,1.5,2.1
            C9.9,57.7,10.5,57,10.5,55.7z M39,78.6c0-0.1,0-0.3-0.1-0.4c-0.4-0.4-0.9-1-1.4-1.1c-1-0.2-1.4,0.6-1.7,1.5
            c-2.6,7.5-6.6,14.3-11.7,20.4c-0.9,1-0.9,2-0.1,2.6c0.8,0.6,1.6,0.4,2.5-0.6c5.4-6.4,9.5-13.6,12.3-21.5
            C38.8,79.1,38.9,78.9,39,78.6z M80,47.9c-0.9-3.5-2.3-6.8-4.4-9.8c-0.3-0.5-1.2-0.8-1.8-0.8c-0.4,0-1,0.7-1.1,1.2
            c-0.1,0.4,0.2,1,0.4,1.5c0.9,1.9,2,3.7,2.8,5.6c1.9,4.8,2.2,9.7,1.9,14.7l0,0l-0.5,7.6l0,0c-0.1,1-0.3,1.9-0.4,2.8
            C76.2,76.9,74.9,83,72.8,89c-1.2,3.5-2.7,7-4,10.5c-0.4,1-0.2,1.9,0.6,2.3c0.8,0.4,1.7,0,2.2-1c0.1-0.1,0.1-0.3,0.2-0.4
            c4-9.1,6.8-18.6,8.2-28.4c0,0,0,0,0,0c0.3-1.2,0.5-3.6,0.7-5.9c0-0.2,0-0.4,0-0.5c0.2-2.6,0.3-5,0.3-5.6c0,0,0-0.1,0-0.1
            c0,0,0-0.1,0-0.1l0,0C81.2,55.7,81.1,51.8,80,47.9z M59.7,67.7c0.9,0.1,1.5-0.5,1.6-1.6c0.2-2.1,0.4-4.1,0.6-6.2c0,0,0,0-0.1,0 c0-1.2,0-2.3,0-3.5c0-4.8-2.1-8.7-5.9-11.6c-1-0.8-2.1-0.7-2.6,0.1c-0.6,0.8-0.3,1.6,0.8,2.4c3.1,2.4,4.8,5.5,4.7,9.5 c-0.1,3-0.3,6-0.5,9C58.2,66.9,58.7,67.6,59.7,67.7z M20.9,14.3c2.2-1.2,4.5-2.5,6.8-3.6c4.1-1.9,8.5-3.1,13-3.7 c1.3-0.2,1.9-0.8,1.8-1.8c-0.1-1.1-0.9-1.3-1.8-1.3c-0.1,0-0.2,0-0.2,0c-7.6,1-14.6,3.6-21.1,7.7c-0.8,0.5-1.1,1.3-0.6,2.2 C19.3,14.7,20.1,14.8,20.9,14.3z M22.8,65.7c-0.2-1-0.6-1.7-1.6-1.8c-1-0.1-1.6,0.4-1.8,1.4c-1.3,6.8-4,13-8,18.6 c-0.7,1-0.6,1.9,0.2,2.5c0.8,0.5,1.5,0.3,2.3-0.8c2.4-3.2,4.4-6.7,5.8-10.4C20.9,72.1,21.8,68.9,22.8,65.7z M16.9,58.5 c0-0.2,0-0.3,0-0.5c0-1.2-0.5-1.9-1.5-1.9c-1,0-1.5,0.6-1.6,1.8c-0.3,6.5-2,12.6-5.1,18.2c-0.6,1.1-0.4,1.9,0.4,2.4 c0.9,0.5,1.7,0.2,2.3-0.9C14.7,71.7,16.5,65.3,16.9,58.5z M70.3,17.6c-0.6,0.7-0.3,1.6,0.7,2.3c5.1,3.4,9.4,7.5,12.7,12.6 c0.3,0.4,0.9,0.7,1.1,0.9c1.6-0.1,2.2-1.4,1.4-2.6c-3.6-5.5-8.2-10-13.7-13.5C71.7,16.8,70.9,16.8,70.3,17.6z M9.1,33.2 c0.8,0.5,1.6,0.3,2.2-0.6c2.7-4.1,6-7.6,9.9-10.6c0.4-0.3,0.7-0.8,1.1-1.4c-0.4-0.5-0.7-1.2-1.1-1.3c-0.6-0.1-1.4-0.1-1.9,0.3 c-4.1,3.2-7.7,6.9-10.6,11.3C8.1,31.8,8.2,32.7,9.1,33.2z M28.7,86.2c0.7-1.2-0.1-2.4-1.6-2.3c-0.2,0.1-0.7,0.4-1,0.8 c-2.4,3.5-4.8,7-7.2,10.6c-0.6,0.9-0.4,1.9,0.4,2.4c0.9,0.5,1.6,0,2.2-0.7c1.1-1.4,2.2-2.7,3.2-4.2C26.1,90.6,27.4,88.4,28.7,86.2z M85.8,44.9c-0.3-1.3-1.1-1.7-2-1.5c-0.9,0.3-1.2,1.1-0.9,2.4c0.5,2.2,1,4.4,1.3,6.6c0.2,1.3,0.2,2.5,0.3,3.8 c0.1,0.9,0.7,1.5,1.6,1.5c0.9,0,1.4-0.5,1.4-1.4C87.5,55.7,86.5,48.1,85.8,44.9z M64.4,15.4c0,0.9,0.7,1.6,1.6,1.6 c0.9-0.1,1.5-0.7,1.5-1.6c0-0.9-0.6-1.4-1.5-1.5C65,14,64.5,14.5,64.4,15.4z M50,43.7c-0.1-0.9-0.6-1.5-1.5-1.4 c-0.9,0-1.5,0.6-1.5,1.5c0,0.9,0.6,1.4,1.3,1.5C49.4,45.2,50.1,44.5,50,43.7z M39.2,73.9c0.9-0.1,1.5-0.5,1.5-1.3 c0-1-0.6-1.7-1.5-1.7c-0.9,0-1.5,0.5-1.6,1.4C37.6,73.1,38.4,74,39.2,73.9z M14.3,51.1c0,1,0.5,1.5,1.4,1.7c0.8,0.1,1.7-0.6,1.7-1.5 c0-0.9-0.5-1.5-1.4-1.6C15,49.7,14.5,50.2,14.3,51.1z M37,34.6c0.8,0,1.6-0.8,1.5-1.6c-0.1-0.9-0.7-1.4-1.6-1.4 c-0.9,0-1.5,0.7-1.4,1.6C35.5,34.1,36.1,34.5,37,34.6z M64.6,67.4c0.1,0.9,0.6,1.4,1.6,1.4c0.9,0,1.4-0.6,1.5-1.4 c0-1-0.6-1.5-1.5-1.6C65.3,65.7,64.5,66.5,64.6,67.4z M23.3,27.7c0.1-0.8-0.7-1.5-1.6-1.4c-0.9,0.1-1.5,0.6-1.5,1.5 c0,1,0.6,1.4,1.6,1.5C22.8,29.2,23.3,28.6,23.3,27.7z M50,6c4.5,0.1,9,0.9,13.3,2.3c2.4,0.8,4.7,1.9,7.1,2.8 c0.9,0.4,1.7,0.2,2.1-0.7c0.5-0.9,0.1-1.7-0.8-2.1C64.9,5,57.6,3.1,50,2.9c-0.1,0-0.2,0-0.2,0C48.8,3,48.1,3.4,48,4.4
            C48,5.4,48.7,6,50,6z"/>
        </svg>
    </div>
        <?php
    }
    
    /**
     * Check IP address for block
     */
    public function checkIP() {
        global $wpdb;
        
        if ($this->enabled == 1 && !empty($this->public_key) && !empty($this->secret_key)) {
            $response   = wp_remote_get($this->brutebank_api.'/api/blocks/ip_check/'.$this->public_key.'/'.$this->secret_key.'/'.$this->getClientIP());
            $body       = wp_remote_retrieve_body($response);
            if (!empty($body) && wp_remote_retrieve_response_code($response) == 200) {
                $json = json_decode($body);
                if ($json->status == 1) {
                    $this->deniedBanner();
                    exit;
                }
            } else if (wp_remote_retrieve_response_code($response) == 401) {
                $table_name = $wpdb->prefix.'brutebank_settings';
                $sql = 'UPDATE '.$table_name.' SET '.
                        'enabled = 0';
                $wpdb->query($sql);
            }
        }
    }
    
    /**
     * Check IP address for block
     */
    public function cachedCheckIP() {
        global $wpdb;
        
        if ($this->enabled == 1 && !empty($this->public_key) && !empty($this->secret_key)) {
            // Update cache every 5 minutes
            if ($this->cache_updated <= date('Y-m-d H:i:s', strtotime('-5 minutes'))) {
                $table_name = $wpdb->prefix.'brutebank_settings';
                $wpdb->query('UPDATE '.$table_name.' SET cache_updated = "'.date('Y-m-d H:i:s').'"');
                
                $response   = wp_remote_get($this->brutebank_api.'/api/blocks/flat_list/'.$this->public_key.'/'.$this->secret_key.'/1');
                $body       = wp_remote_retrieve_body($response);
                if (!empty($body) && wp_remote_retrieve_response_code($response) == 200) {
                    $table_name = $wpdb->prefix.'brutebank_blocks';
                    $wpdb->query('TRUNCATE '.$table_name);
                    
                    foreach (explode(PHP_EOL, $body) as $ip_address) {
                        if (!empty($ip_address) && filter_var($ip_address, FILTER_VALIDATE_IP)) {
                            $wpdb->query('INSERT INTO '.$table_name.' SET ip_address = "'.$ip_address.'"');
                        }
                    }
                } else if (wp_remote_retrieve_response_code($response) == 401) {
                    $table_name = $wpdb->prefix.'brutebank_settings';
                    $sql = 'UPDATE '.$table_name.' SET '.
                            'enabled = 0';
                    $wpdb->query($sql);
                }
            }
            
            // Lookup IP in database
            $table_name = $wpdb->prefix.'brutebank_blocks';
            $results = $wpdb->get_results('SELECT ip_address FROM '.$table_name);
            foreach ($results as $result) {
                if ($result->ip_address == $this->getClientIP()) {
                    $this->deniedBanner();
                    exit;
                }
            }
        }
    }
    
    /**
     * Report IP address for block
     */
    public function reportIP() {
        global $_POST;

        if ($this->enabled == 1 && !empty($this->public_key) && !empty($this->secret_key)) {
            $user = filter_var($_POST['log'], FILTER_SANITIZE_STRING);
            $fields = [
                'public_key'    => $this->public_key,
                'secret_key'    => $this->secret_key,
                'objects'       => [[
                    'date'          => strtotime(date('Y-m-d H:i:s')),
                        'localUser'     => $user ?: '',
                        'ip_address'    => $this->getClientIP(),
                        'type'          => 'wordpress',
                    ]],
            ];
            
            $options = [
                'body'        => $fields,
                'timeout'     => 60,
                'redirection' => 5,
                'blocking'    => true,
                'httpversion' => '1.0',
                'sslverify'   => false,
                'data_format' => 'body',
            ];
            
            $response   = wp_remote_post($this->brutebank_api.'/api/log', $options);
            $body       = wp_remote_retrieve_body($response);
            
            $body       = json_decode($body);
            if (!empty($body) && $body->status == 1) {
                $this->loggedBanner();
            }
        }
    }
    
    public function getClientIP() {
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            return  $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            return $_SERVER['REMOTE_ADDR'];
        } else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }
    
        return '';
    }
}
?>