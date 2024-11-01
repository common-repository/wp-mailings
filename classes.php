<?php
global $wpdb;
define('WPM_TABLE_GROUPS', $wpdb->prefix . 'mailings_groups');
define('WPM_TABLE_USERS', $wpdb->prefix . 'mailings_users');
define('WPM_TABLE_USERS_GROUPS', $wpdb->prefix . 'mailings_users_groups');

class cm_mailings_baseAR {
	var $wpdb, $_rows, $_row, $_new = true, $_table, $_count;
	
	function cm_mailings_baseAR($id = null){
		global $wpdb;
		$this->wpdb = $wpdb;
		if ( $id !== null ) {
			$this->get($id);
		}
		foreach ( $this->_fields as $field ) {
			if ( !isset($this->$field) ) {
				$this->$field = null;
			}
		}
	}
	
	function e($key) {
		echo $this->$key;
	}
	
	function fetch() {
		if ( count($this->_rows) ) {
			$this->_row = each($this->_rows);
			if ( $this->_row ) {
				$this->load();
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	
	function get($id) {
		$this->search('`id` = ' . (int) $id);
		return $this->fetch();
	}
	
	function get_count() {
		return $this->_count;
	}
	
	function get_row() {
		$row = array();
		foreach ( $this->_fields as $f ) {
			$row[$f] = $this->$f;
		}
		return $row;
	}
	
	function get_tags($slugs = false, $prefix = '') {
		if ( function_exists('wp_get_post_tags') ) {
			$tags = wp_get_post_tags($this->postID);
			foreach ( $tags as $key => $tag ) {
				$tags[$key] = $prefix . ($slugs ? $tag->slug : $tag->name);
			}
			return $tags;
		} else {
			return array();
		}
	}
	
	function get_custom() {
		if ( !empty($this->postID) ) {
			return get_post_custom($this->postID);
		}
	}
	
	function set_tags($tags) {
		if ( function_exists('wp_set_post_tags') )
			wp_set_post_tags($this->postID, $tags, false);
	}
	
	function load() {
		foreach ( $this->_fields as $f ) {
			if ( is_null($this->_row['value']->$f) ) {
				$this->$f = null;
			} else {
				$this->$f = stripslashes($this->_row['value']->$f);
			}
		}
	}
	
	function search($where = null, $order = null) {
		$this->_new = false;
		$this->_rows = $this->wpdb->get_results('SELECT * FROM `' . $this->_table . '` ' . ($where !== null ? 'WHERE ' . $where : '') . ($order !== null ? ' ORDER BY ' . $order : ''));
		$this->_count = count($this->_rows);
		return count($this->_rows);
	}
	
	function custom_search($query) {
		$this->_new = false;
		$this->_rows = $this->wpdb->get_results($query);
		$this->_count = count($this->_rows);
		return count($this->_rows);
	}
	
	function save($get_after = true) {
		$first = true;
		$name = $this->_name;
		if ( $this->_new ) {
			$fields = $this->_fields;
			$values = array();
			unset($fields[0]);
			
			if ( !$this->pre_insert() ) return false;

			foreach ( $fields as $f ) {
				if ( is_null($this->$f) ) {
					$values[$f] = 'null';
				} else {
					$values[$f] = '"' . $this->wpdb->escape(stripslashes($this->$f)) . '"';
				}
			}
			
			if ( !$this->wpdb->query('INSERT INTO `' . $this->_table . '` (`' . implode('`, `', $fields) . '`) VALUES (' . implode(', ', $values) . ')') ) {
				return false;
			}
			$this->id = $this->wpdb->insert_id;

			$this->post_insert();
			if ( $get_after ) 
				$this->get((int) $this->id);

			return true;
		} else {
			$fields = $this->_fields;
			$values = array();
			unset($fields[0]);

			if ( !$this->pre_update() ) return false;
			
			foreach ( $fields as $f ) {
				if ( is_null($this->$f) ) {
					$values[$f] = '`' . $f . '` = null';
				} else {
					$values[$f] = '`' . $f . '` = "' . $this->wpdb->escape(stripslashes($this->$f)) . '"';
				}
			}
			
			$result = $this->wpdb->query('UPDATE `' . $this->_table . '` SET ' . implode(', ', $values) . ' WHERE `id` = ' . (int) $this->id);
			
			if ( $result ) $this->post_update();
			
			if ( $get_after ) 
				$this->get((int) $this->id);
			
			return $result;
		}
	}
	
	function pre_insert(){return true;}
	function post_insert(){return true;}
	function pre_update(){return true;}
	function post_update(){return true;}
	function extra_json() {return array();}
	
	function delete() {
		if ( !empty($this->id) ) {
			return (bool) $this->wpdb->query('DELETE FROM `' . $this->_table . '` WHERE `id` = ' . (int) $this->id);
		} else {
			return false;
		}
	}
	
	function to_json($single = true) {
		if ( $single ) {
			$fields = array();
			if ( function_exists("json_encode") ) {
				foreach ( $this->_fields as $f ) {
					$fields[$f] = $this->$f;
				}
				
				$extra = (array) $this->extra_json();
				$fields = array_merge($fields, $extra);
				return json_encode($fields);
			} else {
				foreach ( $this->_fields as $f ) {
					$fields[] = '"' . $f . '":"' . str_replace("\n", '\n', addslashes($this->$f)) . '"';
				}
			
				$extra = (array) $this->extra_json();
				$fields = array_merge($fields, $extra);
				return '{' . implode(', ', $fields) . '}';
			}
		}
	}
	
	function get_error() {
		return $this->wpdb->last_error;
	}
	
	function i($key, $return = false) {
		if ( $return ) return str_replace('"', '\\"', $this->$key);
		else echo str_replace('"', '\\"', $this->$key);
	}
}

class wpm_group extends cm_mailings_baseAR {
	var $_table = WPM_TABLE_GROUPS, $_name = 'name';
	var $user_can_subscribe = 0;
	var $_fields = array('id', 'name', 'user_can_subscribe',);
	
	function add_user($user) {
		if ( is_a($user, 'wpm_user') ) {
			$user = $user->id;
		}
		$this->wpdb->query('INSERT INTO `' . WPM_TABLE_USERS_GROUPS . '` VALUES(' . (int) $user . ', ' . (int) $this->id . ')');
	}
	
	function get_users() {
		$user = new wpm_user();
		$user->custom_search("SELECT u.* FROM " . WPM_TABLE_USERS . " u, " . WPM_TABLE_USERS_GROUPS . " ug WHERE u.id = ug.user_id AND ug.group_id = " . (int) $this->id);
		return $user;
	}
	
	function delete() {
		if ( !empty($this->id) ) {
			$this->wpdb->query('DELETE FROM `' . WPM_TABLE_USERS_GROUPS . '` WHERE `group_id` = ' . (int) $this->id);
			return parent::delete();
		} else {
			return false;
		}
	}
	
	function remove_user($user) {
		if ( is_a($user, 'wpm_user') ) {
			$user = $user->id;
		}
		$this->wpdb->query('DELETE FROM `' . WPM_TABLE_USERS_GROUPS . '` WHERE `group_id` = ' . (int) $this->id . ' AND user_id = ' . (int) $user);
	}

}

class wpm_user extends cm_mailings_baseAR {
	var $_table = WPM_TABLE_USERS, $_name = 'name';
	var $disabled = 0, $banned = 0, $confirmation = true, $notes = '';
	var $_fields = array('id', 'name', 'email', 'notes', 'disabled', 'banned', 'subscription_date', 'password', 'wp_user_id', 'confirmation',);
	
	function pre_insert(){
		$user = new wpm_user();
		if ( $user->search($user->search('email = "' . $user->wpdb->escape($this->email) . '"')) ) {
			return false;
		}
		if ( is_null($this->subscription_date) ) {
			$this->subscription_date = date('Y-m-d');
		}
		
		if ( $this->confirmation ) {
			$this->confirmation = substr(md5(microtime()), 0, 10);
		} else {
			$this->confirmation = null;
		}
		return true;
	}
	
	function post_insert() {
		if ( !is_null($this->confirmation) ) {
			//send da email
		}
		return true;
	}
	
	function delete() {
		if ( !empty($this->id) ) {
			$this->wpdb->query('DELETE FROM `' . WPM_TABLE_USERS_GROUPS . '` WHERE `user_id` = ' . (int) $this->id);
			return parent::delete();
		} else {
			return false;
		}
	}
	
	function get_groups() {
		$groups = new wpm_group();
		$groups->custom_search("SELECT g.* FROM " . WPM_TABLE_GROUPS . " g, " . WPM_TABLE_USERS_GROUPS . " ug WHERE g.id = ug.group_id AND ug.user_id = " . (int) $this->id);
		return $groups;
	}
}

?>