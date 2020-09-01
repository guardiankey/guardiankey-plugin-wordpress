<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include( plugin_dir_path( __FILE__ ).'guardiankey.class.php');

error_reporting(E_ALL & ~E_NOTICE);


function guardiankey_set_content_type(){
    return "text/html";
}

function guardiankey_upgrade () {
	guardiankey_createtable();
}

function guardiankey_createtable() {
	global $wpdb;

   $table_name = $wpdb->prefix . "guardiankey"; 
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
   $sql = "CREATE TABLE $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  time datetime DEFAULT	CURRENT_TIMESTAMP() NOT NULL,
  ip tinytext NOT NULL,
   PRIMARY KEY  (id)
  )";
   $wpdb->query( $sql );
}
}
	
function guardiankey_install () {
   
	 if (! wp_next_scheduled ( 'gk_unlock' )) {
	wp_schedule_event(time(), 'hourly', 'gk_unlock');
    }
    guardiankey_createtable();
    guardiankey_register();
    
}

function gk_unlock_ips() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'guardiankey';
	$wpdb->query("DELETE FROM $table_name
			 WHERE time < (NOW() - INTERVAL 1 HOUR)");

}


function guardiankey_login_failed ($username) {
if (get_user_by( 'login', $username )) {
return;
} else {	
$user = new stdClass();
$user->user_login = $username;
	guardiankey_checkUser($user,'',1,'Authentication',1);
}
}

function guardiankey_options_page_html() {
	
    if (!current_user_can('manage_options')) {
        return;
    }
   if (esc_attr( get_option('gk_agentid')) == '' AND is_email(esc_attr( get_option('gk_admin_email')))) {
 		guardiankey_register();
	} else {

    ?>
    <div class="wrap">
        <h1>GuardianKey</h1>
		<p>Atention: Test if you is receiving mail from you WordPress in button below. If not receive test mail, is possible that your users not receive notifications too. We recommend that use WP Mail SMTP plugin (<a href="https://wordpress.org/plugins/wp-mail-smtp/">https://wordpress.org/plugins/wp-mail-smtp/</a>). </p>
		
		<form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
  <input type="hidden" name="action" value="guardiankey_test_mail">
  <input type="submit" value="Test mail">
</form>
        <form action="options.php" method="post">
            <?php
            
            settings_fields('guardiankey_options');
          
            do_settings_sections('guardiankey_options');
            $textsettings = array( 'textarea_name' => 'post_text' );
 ?>
                 <table class="form-table">
        <tr valign="top">
		<h2>GuardianKey</h2>
		   <?php
	if (get_option('gk_admin_email') == 'Please Insert!') { ?>
		<p>You admin e-mail is already used in GuardianKey. You can go to https://panel.guardiankey.io to create or retrieve keys, or put new email and click in "Save":</p> 
	<?php } ?>
	
		 <th scope="row">Registration Email</th>
        <td><input type="email" name="gk_admin_email" value="<?php echo esc_attr( get_option('gk_admin_email') ); ?>" size="50"/></td>
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
        
        
         <tr valign="top">
        <th scope="row">DNS reverse lookup</th>
        <td><select name="gk_dnsreverse">
			<?php $selected = esc_attr( get_option('gk_dnsreverse') ); ?>
				<option value="Yes" <?php if ($selected == "Yes") { echo "SELECTED";}?> >Yes</option>
				<option value="No" <?php if ($selected == "No") { echo "SELECTED";}?> >No</option>
        </select></td>
        </tr>
          <tr valign="top">
        <th scope="row">Notify Users</th>
        <td><select name="gk_notify_users">
			<?php $selected = esc_attr( get_option('gk_notify_users') ); ?>
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
        <td><?php  
		$settings = array( 'media_buttons' => false );
		wp_editor( get_option('gk_mailhtml'), 'gk_mailhtml', $settings); ?></td>
        </tr>
    </table>

		
             <?php 
	
         
             submit_button('Save Settings');
            ?>
        </form>
        	<h2>Push Notifications</h2>
	<p>Use the form below to send to users QRcode of receive push notifications in your smartphone.</p>
        		<form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
	<select name="sendto">
		<option value="all">All Users</option>
		<?php 
			$users = get_users();
			foreach ($users as $user) {
				echo '<<option value='.$user->data->user_login.'>'.$user->data->user_login.'</option>';
			} ?>			
	</select>

	<?php
	$htmltext = '<div style="background-color: #f9f9f9; height: 100%; padding: 0; width: 100%; margin: 0px;">
<table style="background-color: #f9f9f9; border-collapse: collapse; height: 100%; margin: 0; padding: 0; width: 100%;" border="0" cellspacing="0" cellpadding="0" align="center">
<tbody>
<tr>
<td style="height: 100%; vertical-align: top; width: 100%;">
<table style="background-color: #ffffff; border-collapse: collapse; border: 1px solid #d9d9d9; width: 600px;" border="1" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="vertical-align: top;">
<table style="border-collapse: collapse; width: 100%;" border="0" cellspacing="0" cellpadding="0" align="center">
<tbody>
<tr>
<td style="background-color: #691b1b;">
<div style="color: white; font-size: 35px;">
<h3 style="text-align: center;">Receive notifications on GuardianKey App</h3>
</div></td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td style="background-color: #ffffff;">
<div style="color: black; font-family: Helvetica,Arial,sans-serif; line-height: 160%; padding-bottom: 32px; text-align: center;">

Use the link below to generate the QRcode that will be used to link the GuardianKey app with your account:

<a href="[QRCODE_URL]" target="_blank" rel="noopener">[QRCODE_URL]</a>

</div></td>
</tr>
</tbody>
</table>
<table style="border-collapse: collapse; width: 600px;" cellspacing="0">
<tbody>
<tr>
<td style="background-color: #f7f7f7; text-align: center;">&nbsp;
<p style="margin-left: 0px; margin-right: 0px;"> GuardianKey</p>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>';
?>
   <p>Subject of e-mail:</p>
   <input type="text" size=100 name="gkqrsub" value="Your link of get QRcode to link GuardianKey App">
	<br> 
		<p>Text that will be sent</p>
	<?php
	$settings = array( 'media_buttons' => false );

	wp_editor($htmltext,'gksend',$settings); ?>
  <input type="hidden" name="action" value="sendqrcode">
  <input type="submit" value="Send">
</form>

    </div>
    <?php
}
}

function guardiankey_test_mail() {
	$msg = '<div style="background-color: #f9f9f9; height: 100%; padding: 0; width: 100%; margin: 0px;">
<table style="background-color: #f9f9f9; border-collapse: collapse; height: 100%; margin: 0; padding: 0; width: 100%;" border="0" cellspacing="0" cellpadding="0" align="center">
<tbody>
<tr>
<td style="height: 100%; vertical-align: top; width: 100%;">
<table style="background-color: #ffffff; border-collapse: collapse; border: 1px solid #d9d9d9; width: 600px;" border="1" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="vertical-align: top;">
<table style="border-collapse: collapse; width: 100%;" border="0" cellspacing="0" cellpadding="0" align="center">
<tbody>
<tr>
<td style="background-color: #691b1b;">
<div style="color: white; font-size: 35px;">
<h2 style="text-align: center;">GuardianKey</h2>
</div></td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td style="background-color: #ffffff;">
<div style="color: black; font-family: Helvetica,Arial,sans-serif; line-height: 160%; padding-bottom: 32px; text-align: center;">

If you received this email then you probably will not have any trouble receiving GuardianKey notifications

</div></td>
</tr>
</tbody>
</table>
<table style="border-collapse: collapse; width: 600px;" cellspacing="0">
<tbody>
<tr>
<td style="background-color: #f7f7f7; text-align: center;">&nbsp;
<p style="margin-left: 0px; margin-right: 0px;">GuardianKey</p>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>';
wp_mail(get_option('admin_email'),'Guardiankey mail test',$msg);
wp_redirect(admin_url('/tools.php?page=guardiankey', 'http'), 301);

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

function register_guardiankey_settings() { // whitelist options
  register_setting( 'guardiankey_options', 'gk_agentid' );
  register_setting( 'guardiankey_options', 'gk_key' );
  register_setting( 'guardiankey_options', 'gk_iv' );
  register_setting( 'guardiankey_options', 'gk_orgid' );
  register_setting( 'guardiankey_options', 'gk_authgroupid' );
  register_setting( 'guardiankey_options', 'gk_dnsreverse' );
  register_setting( 'guardiankey_options', 'gk_service' );
  register_setting( 'guardiankey_options', 'gk_mailsubject' );
  register_setting( 'guardiankey_options', 'gk_mailhtml' );
  register_setting( 'guardiankey_options', 'gk_admin_email' );
  register_setting( 'guardiankey_options', 'gk_notify_users' );




}


function guardiankey_register() {
	if (current_user_can('administrator')) {
			$guardiankey = new guardiankey();
			
			if (get_option('gk_admin_email')) {
				if (is_email(get_option('gk_admin_email'))) {
					$email = get_option('gk_admin_email');
				} else {
					update_option( 'gk_admin_email', 'Please Insert!', 'yes');
					wp_redirect(admin_url('/tools.php?page=guardiankey', 'http'), 301);
				}
			} else {
			$email = get_option('admin_email');
			}
					$returns = $guardiankey->register($email);
			if (is_array($returns)) {
				$url = admin_url();
				$bodymail = str_replace("YOUR_SYSTEM_URL",$url,guardiankey_texts_notifications('bodymail'));
				$subjectmail = str_replace("YOUR_SYSTEM_URL",$url,guardiankey_texts_notifications('subjectmail'));
				update_option( 'gk_agentid', $returns['agentid'], 'yes' );
				update_option( 'gk_key' , $returns['key'], 'yes' );
				update_option( 'gk_iv' , $returns['iv'], 'yes' );
				update_option( 'gk_orgid' , $returns['orgid'], 'yes' );
				update_option( 'gk_authgroupid' , $returns['groupid'], 'yes' );
				update_option( 'gk_dnsreverse' , 'yes');
				update_option( 'gk_mailsubject' , $subjectmail, 'yes' );
			    update_option(  'gk_mailhtml' ,$bodymail, 'yes');
			    update_option(  'gk_service' ,'WordPress', 'yes');
			    update_option( 'gk_notify_users', 'No', 'yes');
			    update_option( 'gk_admin_email', $email, 'yes');

				wp_redirect(admin_url('/tools.php?page=guardiankey', 'http'), 301);
				
			} else {
				$url = admin_url();
				$bodymail = str_replace("YOUR_SYSTEM_URL",$url,guardiankey_texts_notifications('bodymail'));
				$subjectmail = str_replace("YOUR_SYSTEM_URL",$url,guardiankey_texts_notifications('subjectmail'));
				update_option( 'gk_mailsubject' , $subjectmail, 'yes' );
			    update_option(  'gk_mailhtml' ,$bodymail, 'yes');
				update_option( 'gk_agentid', '', 'yes' );
				update_option( 'gk_key' , '', 'yes' );
				update_option( 'gk_iv' , '', 'yes' );
				update_option( 'gk_orgid' , '', 'yes' );
				update_option( 'gk_authgroupid' , '', 'yes' );

				if (strpos($returns,"already registered") !== False) {
					update_option(  'gk_admin_email' ,'Please Insert!', 'yes');
				}
			}
	}
}
	

function guardiankey_checkUser($user,$pas,$attempt=0,$event_type = 'Authentication',$failed=0) {
	global $wpdb;
	$guardiankey = new guardiankey();
	$table_name = $wpdb->prefix . 'guardiankey';
	$ip = $guardiankey->getUserIP();
	$blocked = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name WHERE ip = '$ip'" );
	if ($wpdb->last_error) {
		$blocked = 0;
	}

	if ($blocked == 1) {
		return new WP_Error( 'broke', __( "GuardianKey - Attempt of login Blocked! Code 3421", "GK" ) );
 }
  else {
	

	if (!$user->data->user_pass OR !$pas)  {
	 $failed = 1;
	} else {
		
	   if ( $user && wp_check_password($pas, $user->data->user_pass, $user->ID) ) {
		$failed=0;
		 } else {
		$failed = 1;
	   
		}
	}
	

	   $username = $user->user_login;
		if (isset($user->user_email)) {
				$usremail = '';
		} else {
			$usremail = $user->user_email;
		}
		
		
		if ($attempt == 1 OR $failed == 1 ) {
			$returned = $guardiankey->checkaccess($username,$usremail,'1',$event_type);
			$returns = json_decode($returned);
			if ($returns->response == 'BLOCK' ) {
				
					if ($returns->response_cache) {
						global $wpdb;
						$table_name = $wpdb->prefix . 'guardiankey';
						$sql = "INSERT INTO $table_name (ip) VALUES ('$ip')";
						$wpdb->query($sql);
					}
			}


					return new WP_Error('invalid_username', sprintf(__('<strong>ERROR</strong>: Invalid username/password. <a href="%s" title="Password Lost and Found">Lost your password</a>?'), site_url('wp-login.php?action=lostpassword', 'login')));			
		}
		 else {
		$returned = $guardiankey->checkaccess($username,$usremail,$attempt,$event_type);
		
		$returns = json_decode($returned);
			if (json_last_error() <> 0 OR $returns->response == 'ERROR') {
					return $user;
			} 
			else {
				if ($returns->response == 'BLOCK' ) {
					if ($returns->response_cache) {
						global $wpdb;
						$table_name = $wpdb->prefix . 'guardiankey';
						$sql = "INSERT INTO $table_name (ip) VALUES ('$ip')";
						$wpdb->query($sql);
					}
					return new WP_Error( 'broke', __( "GuardianKey - Attempt of login Blocked!", "GK" ) );
				} 
				
				elseif ($returns->response == 'NOTIFY' OR $returns->response == 'HARD_NOTIFY' ) {
	                               $user_info = get_user_by('login',$username);
        	                        $email = $user_info->data->user_email;
                	                if (get_option('timezone_string')) {
                        	                $locale = get_option('timezone_string');
                                	} else {
                                        	$locale = "UTC";
	                                }
	                               $tz = new DateTimeZone($locale);
        	                        $date = new DateTime(gmdate("D, d M y H:i:s", time())." GMT");
                	                $date->setTimezone($tz);
                        	        setlocale(LC_ALL,get_user_locale($user_info->data->user_id));
					$evdate = $date->format("Y-m-d H:i:s")." $locale";
					$urlconfirm = "https://panel.guardiankey.io/events/viewresolve/".$returns->eventId."/".$returns->event_token;

	                                $templatevars = array('[LOCATION]','[DATETIME]','[SYSTEM]','[USERNAME]','[IPADDRESS]','[CHECKURL]');
					$subsvars = array($returns->country,$evdate,$returns->client_os.'/'.$returns->client_ua,$username,$guardiankey->getUserIP(),$urlconfirm);
                	                $msg = str_replace($templatevars,$subsvars,get_option('gk_mailhtml'));
                        	        $subj = str_replace($templatevars,$subsvars,get_option('gk_mailsubject'));
					wp_mail($email,$subj,$msg);

				
				
					return $user;
				} 	
				
				
				
				else {
					return $user;
					
				}
				
		}
			}			
	}

}




function guardiankey_texts_notifications($type) {
	if ($type == 'subjectmail') {
		return 'Alert Security - Access in YOUR_SYSTEM_URL';
	}
	if ($type == 'bodymail') {
		return '<div style="background-color:#f9f9f9; height:100%; margin-bottom:0px; margin-left:0px; margin-right:0px; margin-top:0px; padding:0; width:100%">
<table align="center" border="0" cellpadding="0" cellspacing="0" style="background-color:#f9f9f9; border-collapse:collapse; height:100%; margin:0; padding:0; width:100%">
	<tbody>
		<tr>
			<td style="height:100%; vertical-align:top; width:100%">
			<table border="1" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-collapse:collapse; border:1px solid #d9d9d9; width:600px">
				<tbody>
					<tr>
						<td style="vertical-align:top">
						<table align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse; width:100%">
							<tbody>
								<tr>
									<td style="background-color:#691b1b">
									<div style="color:white; font-size:35px">
									<h2 style="text-align:center">Security Alert!</h2>
									</div>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
					<tr>
						<td style="background-color:#ffffff">
						<div style="color:black; font-family:Helvetica,Arial,sans-serif; line-height:160%; padding-bottom:32px; text-align:center">
						<p>We recently detected a new access to your account at YOUR_SYSTEM_URL.</p>

						<table border="0" style="width:100%">
							<tbody>
								<tr>
									<td style="text-align:right">Username:</td>
									<td style="text-align:left">&nbsp;[USERNAME]</td>
								</tr>
								<tr>
									<td style="text-align:right">Date/time:</td>
									<td style="text-align:left">&nbsp;[DATETIME]</td>
								</tr>
								<tr>
									<td style="text-align:right">System:</td>
									<td style="text-align:left">&nbsp;[SYSTEM]</td>
								</tr>
								<tr>
									<td style="text-align:right">Location:</td>
									<td style="text-align:left">&nbsp;[LOCATION]</td>
								</tr>
								<tr>
									<td style="text-align:right">IP address:</td>
									<td style="text-align:left">&nbsp;[IPADDRESS]</td>
								</tr>
							</tbody>
						</table>

						<p>Please, open our access verification page below to confirm or not if it was you! Our system learns with your responses to provide an intelligent security.</p>
						<strong>Access verification page:</strong> <a href="[CHECKURL]" target="_blank">[CHECKURL]</a></div>
						</td>
					</tr>
				</tbody>
			</table>

			<table cellspacing="0" style="border-collapse:collapse; width:600px">
				<tbody>
					<tr>
						<td style="background-color:#f7f7f7; text-align:center">&nbsp;
						<p style="margin-left:0px; margin-right:0px">GuardianKey</p>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
</div>';

	}
	
}


function sendQRcode() {
	$users = get_users();
		foreach ($users as $user) {
			if ($_POST['sendto'] == 'all') {
			$username = $user->data->user_login;
			$continue = 1;
			} else {
				if ($user->data->user_login == $_POST['sendto']) {
					$username = $user->data->user_login;
					$continue = 1;
			}
			
			if ($continue == 1) {
				$token = hash('sha256',get_option('gk_authgroupid').'-'.$username.'-'.get_option('gk_key'));
				$url = 'https://panel.guardiankey.io/authgroups/authqrcode/'.get_option('gk_authgroupid').'/'.$username.'/'.$token;
				$body = str_replace('\"','"',str_replace('[QRCODE_URL]',$url,$_POST['gksend']));
				wp_mail($user->data->user_email,$_POST['gkqrsub'],$body);
		    }
		}
	}
	
wp_redirect(admin_url('/tools.php?page=guardiankey', 'http'), 301);
	

}
