<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//require( plugin_dir_path( __FILE__ ).'wp-routes.php');
include( plugin_dir_path( __FILE__ ).'install.php');


function wpse27856_set_content_type(){
    return "text/html";
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
        <td><input type="text" name="gk_agentid" value="<?php echo esc_attr( get_option('gk_agentid') ); ?>" readonly size="50"/></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">KEY</th>
        <td><input type="text" name="gk_key" value="<?php echo esc_attr( get_option('gk_key') ); ?>" readonly size="50"/></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">IV</th>
        <td><input type="text" name="gk_iv" value="<?php echo esc_attr( get_option('gk_iv') ); ?>" readonly size="50"/></td>
        </tr>
         <tr valign="top">
        <th scope="row">OrgID</th>
        <td><input type="text" name="gk_orgid" value="<?php echo esc_attr( get_option('gk_orgid') ); ?>" readonly size="50"/></td>
        </tr>
        <th scope="row">AuthGroupIP</th>
        <td><input type="text" name="gk_authgroupid" value="<?php echo esc_attr( get_option('gk_authgroupid') ); ?>" readonly size="50"/></td>
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
			$email = get_option('admin_email');
			$guardianKeyWS='https://api.guardiankey.io/register';
            // Create new Key
            $key = openssl_random_pseudo_bytes(32);
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cfb'));
            $keyb64 = base64_encode($key);
            $ivb64 =  base64_encode($iv);
            $agentid = base64_encode(openssl_random_pseudo_bytes(20));
            $randwh = preg_replace("/[^A-Za-z0-9 ]/", '',base64_encode(openssl_random_pseudo_bytes(6)));
            $weburl =  rest_get_url_prefix().'/guardiankey/'.$randwh;
            
 		    $notify_method = 'webhook';
			$notify_data = base64_encode('{"webhook_url":"'.$weburl.'","systemname":"WordPress","mailmsgsubject":"","usermailmsg":""}');
			
			$data = array(
					'email' => $email,
					'keyb64' => $keyb64,
					'ivb64' => $ivb64,
					 'notify_method' => $notify_method,
					'notify_data' => $notify_data
					);
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $guardianKeyWS);
			curl_setopt($ch,CURLOPT_POST, true);
			curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$returned = curl_exec($ch);
			curl_close($ch);
			$returns = @json_decode($returned);
			if ($returns === null) {
				echo  'An error ocurred: '.$returned;
			} else {
			
				$url = admin_url();
				$bodymail = str_replace("YOUR_SYSTEM_URL",$url,$mailtexts->bodymail);
				$subjectmail = str_replace("YOUR_SYSTEM_URL",$url,$mailtexts->subjectmail);
				update_option( 'gk_agentid', $agentid, 'yes' );
				update_option( 'gk_key' , $keyb64, 'yes' );
				update_option( 'gk_iv' , $ivb64, 'yes' );
				update_option( 'gk_orgid' , $returns->organizationId, 'yes' );
				update_option( 'gk_authgroupid' , $returns->authGroupId, 'yes' );
				update_option( 'gk_dnsreverse' , 'yes');
				update_option( 'gk_mailsubject' , $subjectmail, 'yes' );
			    update_option(  'gk_mailhtml' ,$bodymail, 'yes');
			    update_option(  'gk_service' ,'WordPress', 'yes');
			    update_option( 'gk_webwook', $randwh, 'yes');
			    
				wp_redirect(admin_url('/tools.php?page=guardiankey', 'http'), 301);
				echo 'If you are not redirected, <a href="'.admin_url('/tools.php?page=guardiankey').'">click here!</a>';
			}
}
	
function _json_encode($obj) {
         array_walk_recursive($obj, function (&$item, $key) {
         $item = utf8_encode($item);
      });
      return json_encode($obj);
}

function create_message($username = '',$attempt = 0) {
		 $keyb64 = get_option('gk_key');
		 $ivb64 = get_option('gk_iv');
		 $agentid = get_option('gk_agentid');
		 $service = get_option('gk_service');
		 $orgid = get_option('gk_orgid');
		 $authgroupid =  get_option('gk_authgroupid');
		 $reverse = get_option('dnsreverse');
		 $timestamp = time();
		 
        if(strlen($agentid)>0){

          $key=base64_decode($keyb64);
          $iv=base64_decode($ivb64);
          $json = new stdClass();
          $json->generatedTime=$timestamp;
          $json->agentId=$agentid;
          $json->organizationId=$orgid;
          $json->authGroupId=$authgroupid;
          $json->service=$service;
          $json->clientIP=$_SERVER['REMOTE_ADDR'];
          $json->clientReverse = ($reverse=="Yes")?  gethostbyaddr($json->clientIP) : "";
          $json->userName=$username;
          $json->authMethod=$method;
          $json->loginFailed=$attempt;
          $json->userAgent=substr($_SERVER['HTTP_USER_AGENT'],0,500);
          $json->psychometricTyped="";
          $json->psychometricImage="";
          $tmpmessage = _json_encode($json);
		  $blocksize=8;
          $padsize = $blocksize - (strlen($tmpmessage) % $blocksize);
          $message=str_pad($tmpmessage,$padsize," ");
		 
		  $cipher = openssl_encrypt($message, 'aes-256-cfb8', $key, 0, $iv);
		  return $cipher;
		}
	}


function guardiankey_checkUser($user, $password = '') {
		$guardianKeyWS='https://api.guardiankey.io/checkaccess';
		$attempt = 0;
		$message = create_message($user->user_login,$attempt);
		$tmpdata = new stdClass();
		$tmpdata->id = get_option('gk_authgroupid');
		$tmpdata->message = $message;
		$data = _json_encode($tmpdata);
		
        $ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $guardianKeyWS);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',                                                                                
	            'Content-Length: ' . strlen($data)));  
			curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$returned = curl_exec($ch);
		curl_close($ch);
		$returns = @json_decode($returned);
			if ($returns === null) {
				echo  'An error ocurred: '.$returned;
				
			} else {
									return $user;

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
				$subsvars = array($GKdata->location,$evdate,$GKdata->system,$GKdata->username,$GKdata->ipaddress,$GKdata->checkurl);
				$msg = str_replace($templatevars,$subsvars,get_option('gk_mailhtml'));
				$subj = str_replace($templatevars,$subsvars,get_option('gk_mailsubject'));
				
				
				wp_mail($email,$subj,$msg);
				
			}
	
	
	}
}
	
