<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include( plugin_dir_path( __FILE__ ).'install.php');
include( plugin_dir_path( __FILE__ ).'guardiankey.class.php');


function wpse27856_set_content_type(){
    return "text/html";
}
	
function gk_login_failed ($username) {
$user = new stdClass();
$user->user_login = $username;
	guardiankey_checkUser($username,$user,1);
}
function guardiankey_options_page_html() {
	
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
   if (esc_attr( get_option('gk_agentid')) == '' ) {
   // if (1==1) {
		guardiankey_register();
	} else {
		
    ?>
    <div class="wrap">
        <h1><? echo $GKTitle; ?></h1>
        <form action="options.php" method="post">
            <?php
            
            settings_fields('guardiankey_options');
          
            do_settings_sections('guardiankey_options');
            $textsettings = array( 'textarea_name' => 'post_text' );
 ?>
                 <table class="form-table">
        <tr valign="top">
		<h2>GuardianKey</h2>
		 <th scope="row">Registration Email</th>
        <td><input type="email" name="guardiankey_emailRegister" value="<?php echo esc_attr( get_option('admin_email') ); ?>" readonly size="50"/></td>
        </tr>
        <th scope="row">AgentID</th>
        <td><input type="text" name="gk_agentid" value="<?php echo esc_attr( get_option('gk_agentid') ); ?>" size="50"/></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">KEY</th>
        <td><input type="text" name="gk_key" value="<?php echo esc_attr( get_option('gk_key') ); ?>"  size="50"/></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">IV</th>
        <td><input type="text" name="gk_iv" value="<?php echo esc_attr( get_option('gk_iv') ); ?>" size="50"/></td>
        </tr>
         <tr valign="top">
        <th scope="row">OrgID</th>
        <td><input type="text" name="gk_orgid" value="<?php echo esc_attr( get_option('gk_orgid') ); ?>" size="50"/></td>
        </tr>
        <th scope="row">AuthGroupIP</th>
        <td><input type="text" name="gk_authgroupid" value="<?php echo esc_attr( get_option('gk_authgroupid') ); ?>" size="50"/></td>
        </tr>
          <tr valign="top">
        <th scope="row">Service name</th>
        <td><input type="text" name="gk_service" value="<?php echo esc_attr( get_option('gk_service') ); ?>" size="50"/></td>
        </tr>
         <tr valign="top">
        <th scope="row">DNS reverse lookup</th>
        <td><select name="dnsreverse">
			<?php $selected = esc_attr( get_option('gk_dnsreverse') ); ?>
				<option value="Yes" <?php if ($selected == "Yes") { echo "SELECTED";}?> >Yes</option>
				<option value="No" <?php if ($selected == "No") { echo "SELECTED";}?> >No</option>
        </select></td>
        </tr>
         <tr valign="top">
        <th scope="row">Email subject:</th>
        <td><input type="text" name="gk_mailsubject" value="<?php echo esc_attr( get_option('gk_mailsubject') ); ?>" size="50" /></td>
        </tr>
            <tr valign="top">
        <th scope="row">Email text:</th>
        <td><?php  wp_editor( get_option('gk_mailhtml'), 'gk_mailhtml', $settings); ?></td>
        </tr>
    </table>
			
             <?php 
        
         
             submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}
}

function guardiankey_options_page() {
    add_submenu_page(
        'tools.php',
        'GuardianKey',
        'GuardianKey',
        'manage_options',
        'guardiankey',
        'guardiankey_options_page_html'
    );
}

function register_mysettings() { // whitelist options
  register_setting( 'guardiankey_options', 'gk_agentid' );
  register_setting( 'guardiankey_options', 'gk_key' );
  register_setting( 'guardiankey_options', 'gk_iv' );
  register_setting( 'guardiankey_options', 'gk_orgid' );
  register_setting( 'guardiankey_options', 'gk_authgroupid' );
  register_setting( 'guardiankey_options', 'gk_dnsreverse' );
  register_setting( 'guardiankey_options', 'gk_service' );
  register_setting( 'guardiankey_options', 'gk_mailsubject' );
  register_setting( 'guardiankey_options', 'gk_mailhtml' );
    register_setting( 'guardiankey_options', 'gk_webhook' );


}


function guardiankey_register() {
			global $mailtexts;
			$guardiankey = new guardiankey();

			$email = get_option('admin_email');
			$randwh = preg_replace("/[^A-Za-z0-9 ]/", '',base64_encode(openssl_random_pseudo_bytes(6)));
            $weburl =  site_url().'/'.rest_get_url_prefix().'/guardiankey/'.$randwh;
            
 		    $notify_method = 'webhook';
			$notify_data = base64_encode('{"webhook_url":"'.$weburl.'","systemname":"WordPress","mailmsgsubject":"","usermailmsg":""}');
			$returns = $guardiankey->register($email,$notify_method,$notify_data);
			if (is_array($returns)) {
				$url = admin_url();
				$bodymail = str_replace("YOUR_SYSTEM_URL",$url,$mailtexts->bodymail);
				$subjectmail = str_replace("YOUR_SYSTEM_URL",$url,$mailtexts->subjectmail);
				update_option( 'gk_agentid', $returns['agentid'], 'yes' );
				update_option( 'gk_key' , $returns['key'], 'yes' );
				update_option( 'gk_iv' , $returns['iv'], 'yes' );
				update_option( 'gk_orgid' , $returns['orgid'], 'yes' );
				update_option( 'gk_authgroupid' , $returns['groupid'], 'yes' );
				update_option( 'gk_dnsreverse' , 'yes');
				update_option( 'gk_mailsubject' , $subjectmail, 'yes' );
			    update_option(  'gk_mailhtml' ,$bodymail, 'yes');
			    update_option(  'gk_service' ,'WordPress', 'yes');
			    update_option( 'gk_webwook', $randwh, 'yes');
			    
				wp_redirect(admin_url('/tools.php?page=guardiankey', 'http'), 301);
				echo 'If you are not redirected, <a href="'.admin_url('/tools.php?page=guardiankey').'">click here!</a>';
				
			} else {
				echo $returns;
			}
}
	

function guardiankey_checkUser($username, $user,$attempt=0,$event_type = 'Authentication') {
		if ($user->user_email) {
			$usremail = $user->user_email;
		} else {
			$usremail = '';
		}
		
		$guardiankey = new guardiankey();
		$returned = $guardiankey->checkaccess($username,$usremail,$attempt,$event_type);
		
		$returns = @json_decode($returned);
			if ($returns === null OR $returns->response == 'ERROR') {
				echo  'An error ocurred: '.$returned;
			} else {
				
				if ($returns->response <> 'ACCEPT' OR $returns->response <> 'TIMEOUT' ) {
					return new WP_Error( 'broke', __( "Attempt of login Blocked!", "GK" ) );
					
				} else {
					
					return $user;
				}
			}		
}

function gk_webhhook()
{
    global $wp_rewrite;
    $plugin_url = plugins_url( 'webhook.php', __FILE__ );
    $plugin_url = substr( $plugin_url, strlen( home_url() ) + 1 );
    $wp_rewrite->add_external_rule( 'webhook.php$', $plugin_url );
    return '';
}

function gk_register_webhook() {
	 register_rest_route( 
        'guardiankey',
        '/'.get_option('gk_webhook'),
        array(
            'methods' => 'POST',
            'callback' => 'GKdoAction',
            'args' => [
        'id'
    ],
        )
    );
}

function GKdoAction(WP_REST_Request $request ) {
    $GKjson = $request->get_json_params();
    
	$authgroupid = get_option('gk_authgroupid');
	$key = base64_decode(get_option('gk_key'));
	$iv = base64_decode(get_option('gk_iv'));
	
	
	
	if ($GKjson['authGroupId'] == $authgroupid)  {
		$key = base64_decode(get_option('gk_key'));
		$iv = base64_decode(get_option('gk_iv'));
		try {
				$msgcrypt = base64_decode($GKjson['data']);
				$output = openssl_decrypt($msgcrypt, 'aes-256-cfb8', $key, 1, $iv);
			    }
			 catch (Exception $e) {
				echo 'Error decrypting: ',  $e->getMessage(), "\n";
			}
			
			if ($output) {
				$GKdata = json_decode($output);
				
				$user_info = get_user_by('login',$GKdata->username);
				$email = $user_info->data->user_email;
				if (get_option('timezone_string')) {
					$locale = get_option('timezone_string');
				} else {
					$locale = "UTC";
				}
				$tz = new DateTimeZone($locale);
				$date = new DateTime(gmdate("D, d M y H:i:s", $GKdata->generatedTime)." GMT");
				$date->setTimezone($tz);
				setlocale(LC_ALL,get_user_locale($user_info->data->user_id));
				$evdate = $date->format("Y-m-d H:i:s")." $locale";
				
				$templatevars = array('[LOCATION]','[DATETIME]','[SYSTEM]','[USERNAME]','[IPADDRESS]','[CHECKURL]');
				$subsvars = array($GKdata->city.'/'.$GKdata->country,$evdate,$GKdata->client_os.'/'.$GKdata->client_ua,$GKdata->userName,$GKdata->clientIP,$GKdata->checkurl);
				$msg = str_replace($templatevars,$subsvars,get_option('gk_mailhtml'));
				$subj = str_replace($templatevars,$subsvars,get_option('gk_mailsubject'));
				
				
				wp_mail($email,$subj,$msg);
				
			}
	
	
	}
}
	
