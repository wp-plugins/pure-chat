<?php
/**
 * @package Pure_Chat
 * @version 1.33
 */
/*
Plugin Name: Pure Chat
Plugin URI:
Description: Website chat, simplified. Now from right inside WordPress!
Author: Pure Chat, Inc.
Version: 1.33
Author URI: purechat.com
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include 'variables.php';

class Pure_Chat_Plugin {
	var $version = 5;

	public static function activate()	{
		Pure_Chat_Plugin::clear_cache();
	}

	public static function deactivate()	{
		Pure_Chat_Plugin::clear_cache();
	}

	function Pure_Chat_Plugin() {
		add_option('purechat_widget_code', '', '', 'yes');
		add_option('purechat_widget_name', '', '', 'yes');

		add_action('admin_menu', array( &$this, 'pure_chat_menu' ) );
		add_action('wp_ajax_pure_chat_update', array( &$this, 'pure_chat_update' ) );

		if ( get_option('purechat_widget_code') != '') {
			add_action('wp_enqueue_scripts', array( &$this, 'load_pure_chat_scripts' ) );
		}

		$this->update_plugin();
	}

	function update_plugin() {
		if ( get_option('purechat_plugin_ver', 0) < $this->version && get_option('purechat_widget_code') != '') {
			$this->pure_chat_update_script();
		}

		update_option('purechat_plugin_ver', $this->version);
	}

	function pure_chat_menu() {
		add_menu_page('Pure Chat', 'Pure Chat', 'manage_options', 'purechat-menu', array( &$this, 'pure_chat_generateAcctPage' ), plugins_url().'/pure-chat/favicon.ico');
	}

	function pure_chat_update() {
		update_option('purechat_widget_code', $_POST['purechatwid']);
		update_option('purechat_widget_name', $_POST['purechatwname']);
		$this->pure_chat_update_script();
		add_action('wp_enqueue_scripts', array( &$this, 'load_pure_chat_scripts' ) );
	}

	function pure_chat_update_script() {
		$w = get_option('purechat_widget_code');
		$file = plugin_dir_path(__FILE__)."/purechatwidgetcode.js";
		$wjs = "(function () { var done = false; var script = document.createElement('script'); script.async = true; script.type = 'text/javascript'; script.src = 'https://app.purechat.com/VisitorWidget/WidgetScript'; document.getElementsByTagName('HEAD').item(0).appendChild(script); script.onreadystatechange = script.onload = function (e) { if (!done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) { var w = new PCWidget({pureServerUrl: 'https://app.purechat.com', c: '{$w}', f: true }); done = true; } }; })();";
		file_put_contents($file, $wjs);
		Pure_Chat_Plugin::clear_cache();
	}

	private static function clear_cache() {
		if (function_exists('wp_cache_clear_cache')) {
			wp_cache_clear_cache();
		}
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
					<p>Pure Chat allows you to chat in real time with visitors to your WordPress site. Click the button below to get started by logging in to Pure Chat and selecting a chat widget!</p>
					<p>The button will open a widget selector in an external page. Keep in mind that your Pure Chat account is separate from your WordPress account.</p>
				<?php
				} else {
				?>
					<h4>Your current chat widget is:</h4>
					<h1 class="purechatCurrentWidgetName"><?php echo get_option('purechat_widget_name'); ?></h1>
					<p>Would you like to switch widgets?</p>
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
			var url = ajaxurl;
			window.addEventListener('message', function(event) {
				var data = {
					'action': 'pure_chat_update',
					'purechatwid': event.data.id,
					'purechatwname': event.data.name
				};
				jQuery.post(url, data).done(function(){})
				var purechatNamePassedIn = event.data.name;
				if(typeof purechatNamePassedIn != 'undefined') {
					document.getElementsByClassName('purechatcontentdiv')[0].innerHTML = '<h4>Your current chat widget is:</h4><h1 class="purechatCurrentWidgetName">' +
																						  purechatNamePassedIn + '</h1><p>Would you like to switch widgets?</p>';
					purechatNameToPass = purechatNamePassedIn;
					purechatIdToPass = event.data.id;
				}
			}, false);
		</script>
		<div class="purechatlinkbox">
			<p><a href="https://app.purechat.com/user/dashboard" target="_blank">Your Pure Chat dashboard page</a> is your place to answer chats, add more widgets, customize their appearance with images and text, manage users, and more!</p>
		</div>
		<?php
	}

  function load_pure_chat_scripts() {
		global $pagenow;
		if (!is_admin() && 'wp-login.php' != $pagenow) {
			wp_deregister_script( 'w_script' );
			wp_register_script('w_script', plugins_url('purechatwidgetcode.js', __FILE__), array(), 1, true);
			wp_enqueue_script('w_script');
  	}
  }

}

register_activation_hook(__FILE__, array('Pure_Chat_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('Pure_Chat_Plugin', 'deactivate'));

new Pure_Chat_Plugin();
