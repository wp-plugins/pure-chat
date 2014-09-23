<?php
/**
 * @package Pure_Chat
 * @version 1.1
 */
/*
Plugin Name: Pure Chat
Plugin URI: 
Description: Website chat, simplified. Now from right inside WordPress!
Author: Pure Chat
Version: 1.1
Author URI: purechat.com
*/

include 'variables.php';

add_action('admin_menu', 'pure_chat_menu');
add_action('wp_ajax_pure_chat_update', 'pure_chat_update');

function pure_chat_menu() {
	add_menu_page('Pure Chat', 'Pure Chat', 'manage_options', 'purechat-menu', 'pure_chat_generateAcctPage', plugins_url().'/pure-chat/favicon.ico');
	add_option('purechat_widget_code', '', '', 'yes');
	add_option('purechat_widget_name', '', '', 'yes');
}

function pure_chat_update() {
		update_option('purechat_widget_code', $_POST['purechatwid']);
		update_option('purechat_widget_name', $_POST['purechatwname']);	
}

function pure_chat_generateAcctPage() {
	global $purechatHome;
	?>
	<head>
			<link rel="stylesheet" href="<?php echo plugins_url().'/pure-chat/purechatStyles.css'?>" type="text/css">
	</head>
	<?php
	if (isset($_POST['purechatwid']) && isset($_POST['purechatwname'])) {
		pure_chat_update();
	}
	?>
	<p>		
	<div class="purechatbuttonbox">
		<img src="<?php echo plugins_url().'/pure-chat/logo.png'?>"alt="Pure Chat logo"></img>
		<div class = "purechatcontentdiv">
			<?php
			if (get_option('purechat_widget_code') == '' ) {
				?>
				<p>
				Pure Chat allows you to chat in real time with visitors to your WordPress site. Click the button below to get started by logging in to Pure Chat and selecting a chat widget!
				<p>
				The button will open a widget selector in an external page. Keep in mind that your Pure Chat account is separate from your WordPress account.
				<?php
			} else {
				?>
				<h4>Your current chat widget is:</h4>
				<h1 class="purechatCurrentWidgetName"><?php echo get_option('purechat_widget_name'); ?></h1>
				<p>
				Would you like to switch widgets?
				<?php
			}
			?>
		</div>
		<form>
			<input type="button" class="purechatbutton" value="Pick a widget!" onclick="openPureChatChildWindow()">
		</form>
		<p>
	</div>
	<script>
		var pureChatChildWindow;
		var purechatNameToPass = "<?php echo get_option('purechat_widget_name');?>";
		var purechatIdToPass = "<?php echo get_option('purechat_widget_code');?>";
		function openPureChatChildWindow() {
		pureChatChildWindow = window.open('<?php echo $purechatHome;?>/home/pagechoicewordpress?widForDisplay=' + purechatIdToPass + 
										  '&nameForDisplay=' + purechatNameToPass, 'Pure Chat');
		}	
		var message_from_iframe;
		var url = ajaxurl;
		window.addEventListener('message', function(event) {
			var data = {
				'action': 'pure_chat_update',
				'purechatwid': event.data.id,
				'purechatwname': event.data.name
			};
			jQuery.post(url, data)
			.done(function(){
				pureChatChildWindow.postMessage({ id: id, name: name}, '*');
			})
			var purechatNamePassedIn = event.data.name;
			if(typeof purechatNamePassedIn != 'undefined')
			{
				document.getElementsByClassName('purechatcontentdiv')[0].innerHTML = '<h4>Your current chat widget is:</h4><h1 class="purechatCurrentWidgetName">' +
																					  purechatNamePassedIn + '</h1><p>Would you like to switch widgets?';
				purechatNameToPass = purechatNamePassedIn;
				purechatIdToPass = event.data.id;
			}
		}, false);
	</script>
	<div class="purechatlinkbox">
		<p>
		<a href="https://app.purechat.com/user/dashboard" target="_blank">Your Pure Chat dashboard page</a>
		is your place to answer chats, add more widgets, customize their appearance with images and text, manage users, and more!
	</div>
	<?php
}

if (!function_exists('load_pure_chat_scripts')) {
    function load_pure_chat_scripts() {
		global $pagenow;
        if (!is_admin() && 'wp-login.php' != $pagenow) {
			wp_deregister_script( 'w_script' );
			wp_register_script('w_script', plugins_url()."/pure-chat/purechatwidgetcode.js", false, false, true);
			$pc_wid = get_option('purechat_widget_code');
			wp_localize_script('w_script', 'purechat_widget_object', array('replacement_code' => $pc_wid));
			wp_enqueue_script('w_script');
        }
    }
}
add_action('init', 'load_pure_chat_scripts');