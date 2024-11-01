<?php
/*
Plugin Name: WP-Mailings
Plugin URI: 
Description: 
Author: Dan Coulter
Version: 0.1-will-not-work
Author URI: http://co.deme.me/
*/
if ( defined('DOING_AJAX')  ) {
	require_once 'classes.php';
	require_once 'admin.emails.php';
	require_once 'admin.groups.php';
	require_once 'admin.users.php';
	require_once 'admin.users.php';
}

define('WP_MAILINGS_PLUGIN_VERSION', microtime(true));
//define('WP_MAILINGS_PLUGIN_VERSION', '0.1-bleeding');

class wp_mailings {
	function add_admin_pages() {
		add_object_page(
			'Mailings',
			'Mailings',
			'level_7', 
			'wpm-messages', 
			array("wp_mailings_emails", "admin_page")
		);

		require_once 'classes.php';
		require_once 'admin.emails.php';
		require_once 'admin.groups.php';
		require_once 'admin.users.php';

		add_submenu_page('wpm-messages', 'Emails', 'Emails', 'level_7', 'wpm-messages', array("wp_mailings_emails", "admin_page")); 
		add_submenu_page('wpm-messages', 'Emails', 'Twitter', 'level_7', 'wpm-messages', array("wp_mailings_emails", "admin_page")); 
		add_submenu_page('wpm-messages', 'Emails', 'Text Messages', 'level_7', 'wpm-messages', array("wp_mailings_emails", "admin_page")); 
		add_submenu_page('wpm-messages', 'WP-Mailings &rsaquo; Users', 'Users', 'level_7', 'wpm-users', array("wp_mailings_users", "admin_page")); 
		//add_submenu_page('wpm-messages', 'WP-Mailings &rsaquo; Groups', 'Groups', 'level_7', 'wpm-groups', array("wp_mailings_groups", "admin_page")); 
		add_submenu_page('wpm-messages', 'WP-Mailings > Groups', 'Settings', 'level_7', 'wpm-messages', array("wp_mailings_emails", "admin_page")); 

	}
	
	function get_path() {
		return dirname(__FILE__) . '/';
	}
	
	function get_url() {
		return WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) . '/';
	}
	
	function init() {
		if ( is_admin() ) {
			if ( substr($_GET['page'], 0, 4) == 'wpm-' ) {
				wp_enqueue_script('wp_mailings_scripts', wp_mailings::get_url() . 'js/custom.js', array('jquery'), WP_MAILINGS_PLUGIN_VERSION);
				wp_enqueue_script('jquery-hint', wp_mailings::get_url() . 'js/jquery.hint.js', array('jquery'), '20090408');
				wp_enqueue_script('jquery-form');
				wp_enqueue_style('wp_mailings_admin', wp_mailings::get_url() . 'css/admin.css', array(), WP_MAILINGS_PLUGIN_VERSION);
			}
		} else {
			wp_enqueue_script('jquery-form');
			wp_enqueue_script('jquery-hint', wp_mailings::get_url() . 'js/jquery.hint.js', array('jquery'), '20090408');
			wp_enqueue_style('wp_mailings_admin', wp_mailings::get_url() . 'css/public.css', array(), WP_MAILINGS_PLUGIN_VERSION);
			require_once 'wp_mailings_public.class.php';
			
			if ( isset($_POST['action']) && $_POST['action'] == 'mailings' ) {
				if ( in_array($_POST['subaction'], apply_filters('wpm_public_ajax_actions', array('signup'))) ){
					define('DOING_AJAX', 1);
					require_once 'classes.php';
					call_user_func(array('wp_mailings_public', 'ajax_' . $_POST['subaction']));
					exit;
				}
			}
		}
	}
	
	function install() {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;

		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}
		
		dbDelta('
			CREATE TABLE ' . $wpdb->prefix . 'mailings_groups (
				id int(10) unsigned NOT NULL auto_increment,
				name varchar(255) NOT NULL,
				user_can_subscribe tinyint(4) NOT NULL default 0,
				PRIMARY KEY  (id)
			) ENGINE=MyISAM ' . $charset_collate . ';	
			
			CREATE TABLE ' . $wpdb->prefix . 'mailings_users (
				id int(11) unsigned NOT NULL auto_increment,
				name varchar(255) NOT NULL,
				email varchar(255) NOT NULL,
				notes text NOT NULL,
				disabled tinyint(4) NOT NULL default 0,
				banned tinyint(4) NOT NULL default 0,
				subscription_date date NOT NULL,
				password char(32) default NULL,
				wp_user_id int(11) unsigned default NULL,
				confirmation char(32) default NULL,
				PRIMARY KEY  (id)
			) ENGINE=MyISAM ' . $charset_collate . ';	

			CREATE TABLE ' . $wpdb->prefix . 'mailings_users_groups (
				user_id int(10) unsigned NOT NULL,
				group_id int(10) unsigned NOT NULL,
				PRIMARY KEY  (user_id, group_id)
			) ENGINE=MyISAM ' . $charset_collate . ';	
			
		');
	}
	
	function o($key = null, $value = null) {
		$defaults = array(
			'default_from' => get_option('admin_email'),
		);
		$options = get_option("wp_mailings");
		if ( $options === false ) {
			$options = array();
			add_option('wp_mailings', array(), '', 'no');
		}
		$options = array_merge($defaults, $options);
		if ( is_null($key) ) {
			return $options;
		} elseif ( is_array($key) ) {
			update_option('wp_mailings', array_merge($options, $key));
		} elseif ( is_null($value) ) {
			return $options[$key];
		} else {
			$options[$key] = $value;
			update_option('wp_mailings', $options);
		}
	}
	
	function ajax_fields($subaction) {
		echo '<input type="hidden" name="action" value="mailings" />';
		echo '<input type="hidden" name="subaction" value="' . $subaction . '" />';
	}
	
	function admin_ajax() {
		check_admin_referer('wp-mailings');
		ereg("=wpm-([a-z]*)", $_POST['_wp_http_referer'], $matches);
		$page = $matches[1];
		if ( method_exists('wp_mailings_' . $page, $_POST['subaction']) ) {
			call_user_func(array('wp_mailings_' . $page, $_POST['subaction']));
			exit;
		}
	}
	
	function public_ajax() {
	
	}
}

add_action("init", array("wp_mailings", "init"));
add_action("admin_menu", array("wp_mailings", "add_admin_pages"));
register_activation_hook(plugin_basename(__FILE__), array("wp_mailings", "install"));

add_action('wp_ajax_mailings', array("wp_mailings", "admin_ajax"));
?>
