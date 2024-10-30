<?php
    global $brutebank_plugin_version, $wpdb;

    $_success  = 0;
    $_error    = 0;
    $table_name = $wpdb->prefix.'brutebank_settings';
    if (isset($_POST['update']) && wp_verify_nonce($_POST['_csrf'],'bb-csrf')) {
        if (isset($_POST['enabled'])) {
            $enabled = preg_replace('/[^0-9]/', '', $_POST['enabled']);
        } else {
            $enabled = 0;
        }
        if (isset($_POST['xmlrpc'])) {
            $xmlrpc = preg_replace('/[^0-9]/', '', $_POST['xmlrpc']);
        } else {
            $xmlrpc = 0;
        }
        if (isset($_POST['two_factor'])) {
            $two_factor = preg_replace('/[^0-9]/', '', $_POST['two_factor']);
        } else {
            $two_factor = 0;
        }
        $public_key     = preg_replace('/[^A-Za-z0-9]/', '', $_POST['public_key']);
        $secret_key     = preg_replace('/[^A-Za-z0-9]/', '', $_POST['secret_key']);

        if (!empty($public_key) && !empty($secret_key)) {
            $bb = new BruteBank_WP();
            $response = wp_remote_post($bb->brutebank_api.'/api/server/check_key',
                [
                    'body' => [
                        'public_key' => $public_key,
                        'secret_key' => $secret_key,
                    ],
                    'headers' => [
                        'accept' => 'application/json',
                    ]
                ]
            );

            if (isset($response) && isset($response['body'])) {
                $json = json_decode($response['body']);
                if ($json->status == 1) {
                    $wpdb->query('DELETE FROM '.$table_name);

                    $sql = 'INSERT INTO '.$table_name.' SET '.
                            'enabled = "'.$enabled.'", '.
                            'xmlrpc = "'.$xmlrpc.'", '.
                            'two_factor = "'.$two_factor.'", '.
                            'public_key = "'.$public_key.'", '.
                            'secret_key = "'.$secret_key.'" '.
                            '';
                    $wpdb->query($sql);

                    $_success = 1;
                } else {
                    $_error = 1;
                }
            } else {
                $_error = 1;
            }
        } else {
            $_error = 1;
        }
    } else if (isset($_POST['clear_cache'])) {
        $sql = 'UPDATE '.$table_name.' SET '.
                'cache_updated = "'.date('Y-m-d H:i:s', strtotime('-10 minutes')).'"';
        $wpdb->query($sql);

        $_success = 1;
    }

    // get server key data
    $_enabled       = 0;
    $_public_key    = '';
    $_secret_key    = '';
    $_xmlrpc        = 0;
    $_two_factor    = 0;
    $sql = 'SELECT * FROM '.$table_name;
    $results = $wpdb->get_results($sql);
    foreach ($results as $result) {
        $_enabled       = $result->enabled;
        $_xmlrpc        = $result->xmlrpc;
        $_two_factor    = $result->two_factor;
        $_public_key    = $result->public_key;
        $_secret_key    = $result->secret_key;
    }
    if (!empty($_public_key) && $_enabled != 1) {
        $_warning = 1;
    }
?>
<style>
.bb-switch{
  position:relative;
  width:75px;
  height:35px;
  display:inline-block;
  vertical-align:middle;
}
.bb-switch.animate .bb-switch-inner {
  -webkit-transition:margin-left .4s ease-in-out;
  -moz-transition:margin-left .4s ease-in-out;
  transition:margin-left .4s ease-in-out;
  -webkit-transition-property:margin-left, background-color, color;
  -moz-transition-property:margin-left, background-color, color;
  transition-property:margin-left, background-color, color;
  -webkit-transition-duration:.4s;
  -moz-transition-duration:.4s;
  transition-duration:.4s;
  -webkit-transition-timing-function:ease-in-out;
  -moz-transition-timing-function:ease-in-out;
  transition-timing-function:ease-in-out;
}
.bb-switch.animate .bb-switch-switch {
  -webkit-transition:left .4s ease-in-out;
  -moz-transition:left .4s ease-in-out;
  transition:left .4s ease-in-out;
}
.bb-switch .bb-switch-checkbox {
  display:none;
}
.bb-switch .bb-switch-checkbox:disabled + .bb-switch-label {
  opacity:0.6;
}
.bb-switch .bb-switch-checkbox:checked + .bb-switch-label .bb-switch-inner {
  margin-left:0;
  background:rgb(249,131,141);
  background:-moz-linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%);
  background:-webkit-linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%); background:linear-gradient(145deg, rgba(249,131,141,1) 0%, rgba(252,190,100,1) 100%); filter:progid:DXImageTransform.Microsoft.gradient(startColorstr="#f9838d",endColorstr="#fcbe64",GradientType=1);
  color:#ffffff;
}
.bb-switch .bb-switch-checkbox:checked + .bb-switch-label .bb-switch-switch {
  left:43px;
}
.bb-switch .bb-switch-label {
  display:block;
  overflow:hidden;
  border-radius:35px;
  height:35px;
}
.bb-switch .bb-switch-label .bb-switch-inner{
  width:200%;
  height:100%;
  margin-left:-100%;
  background-color:#dcc8cb;
  color:#ffffff;
}
.bb-switch .bb-switch-label .bb-switch-inner:before,
.bb-switch .bb-switch-label .bb-switch-inner:after {
  float:left;
  width:50%;
  height:35px;
  line-height:37px;
  padding:0;
  font-family:'Helvetica neue';
  font-size:14px;
}
.bb-switch .bb-switch-label .bb-switch-inner:before {
  content:"ON";
  padding-left:10px;
  text-align:left;
}
.bb-switch .bb-switch-label .bb-switch-inner:after {
  content:"OFF";
  padding-right:10px;
  text-align:right;
}
.bb-switch .bb-switch-label .bb-switch-switch {
  position:absolute;
  width:29px;
  height:29px;
  background:#fefefe;
  border-radius:35px;
  top:3px;
  left:3px;
}

*, *:before, *:after {
  -moz-box-sizing:border-box;
  -webkit-box-sizing:border-box;
  box-sizing:border-box;
}
</style>
<div class="wrap">
	<h2><img src="<?php echo BRUTEBANK_PLUGIN_URL;?>admin/images/brutebank_logo.svg" alt="BruteBank" style="width: 180px; height: auto;" /></h2>

    <p/>
    <div style="display: flex; flex-direction: row; justify-content: flex-start; ">
        <div style="padding: 0 1em 1em 0;">
            <a style="font-size: 1.2em; line-height: 1.3em;" href="https://www.brutebank.io" target="_blank">BruteBank.io</a>
        </div>
        <div style="padding: 0 1em 1em 0;">
            |
        </div>
        <div style="padding: 0 1em 1em 0;">
            <span style="font-size: 1.2em; line-height: 1.3em;">Wordpress Plugin v<?php echo $brutebank_plugin_version;?></span>
        </div>
    </div>
    <div style="border: solid 1px #ccc; background-color: white; padding: 1.5em; font-size: 1.2em; line-height: 1.3em; ">
        BruteBank is an interactive firewall plugin that allows Wordpress owners and server administrators to receive real time threat notifications via a mobile app. This app then allows for immediate threat mitigation by blocking attacking IP addresses.
    </div>
    <p/>
    <form method="post">
        <input name="_csrf" type="hidden" value="<?=wp_create_nonce('bb-csrf')?>" />
    <div style="border: solid 1px #ccc; background-color: white; padding: 1.5em; font-size: 1em; line-height: 1.3em; ">
        <?php
            if ($_success == 1) {
                ?>
                <div style="border: solid 1px #fff; background-color: #F37E8F; padding: 1em; color: white; font-size:1.2em;">Success: settings have been updated</div>
                <?php
            }
            if ($_error == 1) {
                ?>
                <div style="border: solid 1px #fff; background-color: #F37E8F; padding: 1em; color: white; font-size:1.2em;">Error: Please include a valid license from brutebank.io</div>
                <?php
            }
            if ($_error == 2) {
                ?>
                <div style="border: solid 1px #fff; background-color: #F37E8F; padding: 1em; color: white; font-size:1.2em;">Error: please check all required fields below</div>
                <?php
            }
            if ($_warning == 1) {
                ?>
                <div style="border: solid 1px #fff; background-color: #F37E8F; padding: 1em; color: white; font-size:1.2em;">Warning: Monitoring is disabled and therefore BruteBank will not be logging attacks</div>
                <?php
            }
        ?>
        <h3>Server Keys</h3>
        <div style="display: flex; flex-direction: column; justify-content: flex-start; ">
            <div style="margin: 1em 0 0.8em 0;">
                <label>Public Key</label>
            </div>
            <div style="margin: 0 0 0.8em 0;">
                <input type="text" name="public_key" value="<?php echo $_public_key;?>" size="40" />
            </div>
            <div style="margin: 0 0 0.8em 0;">
                <label>Secret Key</label>
            </div>
            <div style="margin: 0 0 2em 0;">
                <input type="text" name="secret_key" value="<?php echo $_secret_key; ?>" size="40" />
            </div>

            <h3>Plugin Options</h3>

            <div style="margin: 1em 0 0.8em 0;">
                <label for="bb_enabled">Disable XMLRPC - Recommended unless knowingly using XMLRPC</label>
            </div>
            <div style="margin: 0 0 2em 0;">
                <div class="bb-switch animate">
                    <input id="xmlrpc_enabled" type="checkbox" name="xmlrpc" class="bb-switch-checkbox" value="1" <?php echo $_xmlrpc ? 'checked' : '';?> />
                    <label class="bb-switch-label" for="xmlrpc_enabled">
                        <div class="bb-switch-inner"></div>
                        <div class="bb-switch-switch"></div>
                   </label>
                </div>
            </div>

            <div style="margin: 0 0 0.8em 0;">
                <label for="bb_enabled">Enable 2FA ( Two Factor Authentication )</label>
            </div>
            <div style="margin: 0 0 2em 0;">
                <div class="bb-switch animate">
                    <input id="two_factor_enabled" type="checkbox" name="two_factor" class="bb-switch-checkbox" value="1" <?php echo $_two_factor ? 'checked' : '';?> />
                    <label class="bb-switch-label" for="two_factor_enabled">
                        <div class="bb-switch-inner"></div>
                        <div class="bb-switch-switch"></div>
                   </label>
                </div>
            </div>

            <div style="margin: 0 0 0.8em 0;">
                <label for="bb_enabled">Monitoring Enabled</label>
            </div>
            <div style="margin: 0 0 2em 0;">
                <div class="bb-switch animate">
                    <input id="bb_enabled" type="checkbox" name="enabled" class="bb-switch-checkbox" value="1" <?php echo $_enabled ? 'checked' : '';?> />
                    <label class="bb-switch-label" for="bb_enabled">
                        <div class="bb-switch-inner"></div>
                        <div class="bb-switch-switch"></div>
                   </label>
                </div>
            </div>
            <div style="padding: 0 1em 1em 0;">
                <input type="submit" name="update" value="Update" style="background: #fcbe64; box-shadow: 0px 0px 0px 0px rgba(255,255,255,0.02); border: none; font-family: 'AvenirBlack', sans-serif;
                    position: relative;
                    width: 275px;
                    line-height: 1;
                    margin: 10px auto;
                    cursor: pointer;
                    border-radius: 30px;
                    color: #fff;
                    text-align: center;
                    text-transform: uppercase;
                    font-size: 0.80rem;
                    padding: 16px 36px;
                    font-weight: bold;
                    white-space: nowrap;
                    vertical-align: middle;
                    text-decoration: none;
                    box-shadow: 0px 6px 8px 0px rgba(0,0,0,0.15);
                    transition: box-shadow .3s linear;" />

                    <input type="submit" name="clear_cache" value="Clear Block Cache" style="background: #fcbe64; box-shadow: 0px 0px 0px 0px rgba(255,255,255,0.02); border: none; font-family: 'AvenirBlack', sans-serif;
                        position: relative;
                        width: 275px;
                        line-height: 1;
                        margin: 10px auto;
                        cursor: pointer;
                        border-radius: 30px;
                        color: #fff;
                        text-align: center;
                        text-transform: uppercase;
                        font-size: 0.80rem;
                        padding: 16px 36px;
                        font-weight: bold;
                        white-space: nowrap;
                        vertical-align: middle;
                        text-decoration: none;
                        box-shadow: 0px 6px 8px 0px rgba(0,0,0,0.15);
                        transition: box-shadow .3s linear;" />
            </div>
        </div>
    </div>
    </form>
</div>