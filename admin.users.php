<?php

class wp_mailings_users {
	function admin_page() {
		$groups = new wpm_group(); 
		$groups->search(null, 'name');
		
		?>
			<div class="wrap">
				<h2><?php _e('WP-Mailings &rsaquo; Users') ?></h2>
					<div id="ajax-loading">
						<img src="<?php echo wp_mailings::get_url() ?>images/ajax-loader.gif" />
					</div>

				<div id="user_global_operations" style="">
					<div style="padding: 5px;" class="vam">
						<img id="new-user-trigger" class="clickable" alt="<?php _e('Add User') ?>" title="<?php _e('Add a user') ?>" src="<?php echo wp_mailings::get_url() ?>images/user_add.png" />
						<img id="new-group-trigger" class="clickable" alt="<?php _e('Add Group') ?>" title="<?php _e('Add a group') ?>" src="<?php echo wp_mailings::get_url() ?>images/group_add.png" />
						<input id="user-search" class="search" title="Search users" />
					</div>
				</div>
				<div class="panels">
					<div class="panel" id="groups" style="width: 25%;">
						<div class="head">
							<h3><?php _e('Groups') ?></h3>
						</div>
						<div class="list">
							<div class="group virtual" id="group-all"><?php _e('All Contacts') ?></div>
							<div class="group virtual" id="group-search"><?php _e('Search Results') ?></div>
							<hr />
							<?php while ( $groups->fetch() ) : ?>
								<div class="group real" id="group-<?php echo $groups->id ?>"><?php echo $groups->name ?></div>
							<?php endwhile ?>
						</div>
					</div>
					<div class="panel" id="users" style="width: 25%">
						<div class="head">
							<h3><?php _e('Users') ?></h3>
							Select: <a href="#" id="select-all">All</a>, <a href="#" id="select-none">None</a>
						</div>
						<div class="list">
							<?php echo wp_mailings::get_path(); ?>
						</div>
					</div>
					<div class="panel" id="edit" style="width: 45%">
						<div class="head">
							<h3><?php _e('Edit') ?></h3>
						</div>
						<div class="list">
							<div id="dynamic-editor"></div>
							<form style="display: none;" id="new-group" method="post" action="admin-ajax.php">
								<?php wp_nonce_field('wp-mailings'); ?>
								<?php wp_mailings::ajax_fields('add_group') ?>
								<table>
									<tbody>
										<tr>
											<td><?php _e('New group name:') ?> </td>
											<td><input type="text" name="name" /></td>
										</tr>
										<tr>
											<td></td>
											<td><input type="submit" class="button" value="Create group" /></td>
										</tr>
									</tbody>
								</table>
							</form>
							<form style="display: none;" id="new-user" method="post" action="admin-ajax.php">
								<?php wp_nonce_field('wp-mailings'); ?>
								<?php wp_mailings::ajax_fields('add_user') ?>
								<table>
									<tbody>
										<tr>
											<td><?php _e('Name:') ?> </td>
											<td><input type="text" name="name" /></td>
										</tr>
										<tr>
											<td><?php _e('Email Address:') ?> </td>
											<td><input type="text" name="email" /></td>
										</tr>
										<tr>
											<td><label for="new_user_confirmation"><?php _e('Send email confirmation:') ?></label> </td>
											<td><input type="checkbox" id="new_user_confirmation" name="confirmation" value="1" checked="checked" /></td>
										</tr>
										<tr>
											<td><label for="new_user_add_to_group"><?php _e('Add to selected group:') ?></label> </td>
											<td><input type="checkbox"  id="new_user_add_to_group" name="add_to_group" value="0" checked="checked" /></td>
										</tr>
										<tr>
											<td valign="top"><?php _e('Other Notes:') ?> </td>
											<td><textarea name="notes" cols="30" rows="7"></textarea></td>
										</tr>
										<tr>
											<td></td>
											<td><input type="submit" class="button" value="Create user" /></td>
										</tr>
									</tbody>
								</table>
							</form>
						</div>
					</div>
				</div>
			</div>
			
			<script type="text/javascript">
				;(function($){
					setup_events = function() {
						$(".group").unbind("click").click(function(){
							select_group(this);
						});
						$(".user").unbind("click").click(function(e){
							if ( !$(e.target).is("input") ) {
								select_user(this);
							}
						});
						$(".user input").unbind("change").change(function(){
							$checked = $(".user input:checked");
							if ( $checked.length == 0 ) {
								blank_edit();
							} else if ( $checked.length == 1 ) {	
								select_user($checked.parent());
							} else if ( $("#users-groups-management").length == 0 ) {
								$("#edit h3").html("<?php _e('%d Contacts Selected') ?>".replace("%d", $checked.length));
								$("#edit .list form, #edit .list > div").hide();
								$("#dynamic-editor").load("admin-ajax.php", {
									"_wpnonce":"<?php echo wp_create_nonce('wp-mailings') ?>", 
									"_wp_http_referer":"<?php echo $_SERVER['REQUEST_URI'] ?>", 
									action:"mailings",
									subaction:"user_group_list"
								}, function(){
									$("#dynamic-editor").fadeIn();
								}).html("<div id='users-groups-management'></div>");
							}
						});
					}
					
					setup_events();
					
					$("#new-group-trigger").click(function(){
						$("#edit h3").html("<?php _e('Create a new group') ?>");
						$("#edit .list form, #edit .list > div").hide();
						$("#new-group").fadeIn();
						$("#new-group input[name=name]").focus();
						reset_panels();
					});
					
					$("#new-user-trigger").click(function(){
						$("#edit h3").html("<?php _e('Create a new user') ?>");
						$("#edit .list form, #edit .list > div").hide();
						if ( $(".group.selected").hasClass("real") ) {
							$("#new_user_add_to_group").attr("disabled", false).parents("tr").eq(0).find("td").css("color", "black");
						} else {
							$("#new_user_add_to_group").attr("disabled", true).parents("tr").eq(0).find("td").css("color", "gray");
						}
						$("#new-user").fadeIn();
						$("#new-user input[name=name]").focus();
						reset_panels();
					});
					
					$("#select-all").click(function(){
						$(".user input").attr("checked", true).change();
						return false;
					});
					
					$("#select-none").click(function(){
						$(".user input").attr("checked", false).change();
						return false;
					});
					
					$(document).ready(function(){
						$("#new-group").ajaxForm({
							dataType: 'json',
							success: function(rsp){
								$("#new-group input[name=name]").val("");
								$old_groups = $(".list .group.real");
								if ( $old_groups.eq(0).html() > rsp.name ) {
									$old_groups.eq(0).before('<div id="group-' + rsp.id + '" class="group real">' + rsp.name + '</div>');
								} else if ( $old_groups.eq($old_groups.length - 1).html() < rsp.name ) {
									$old_groups.eq($old_groups.length - 1).after('<div id="group-' + rsp.id + '" class="group real">' + rsp.name + '</div>');
								} else {
									for ( var i = 1; i < $old_groups.length; i++ ) {
										if ( $old_groups.eq(i).html() > rsp.name ) {
											$old_groups.eq(i).before('<div id="group-' + rsp.id + '" class="group real">' + rsp.name + '</div>');
											break;
										}
									}
								}
								setup_events();
								select_group("group-" + rsp.id);
							}
						});
						
						$("#new-user").ajaxForm({
							dataType: 'json',
							success: function(rsp){
								if ( rsp.success ) {
									if ( $("div#group-all").hasClass("selected") || ($("#new_user_add_to_group").attr("checked") && $("div.group.selected").hasClass("selected/") ) ) {
										refresh_users();
									}
									$("#new-user input[type=text], #new-user textarea").val("");
									$("#new-user input[name=email]").focus();
									setup_events();
								} else {
									show_error(rsp.error);
								}								
							}
						});
						
						select_group("#group-all");
					});
				
					select_group = function(group) {
						$(".group.selected").removeClass("selected");
						$group = $(group).addClass("selected");
						if ( $group.attr("id") == 'group-all' ) {
							$("#edit h3").html("<?php _e('Select a group or user') ?>");
						} else {
							$("#edit h3").html("<?php _e('Edit %s') ?>".replace("%s", $group.html()));
						}
						$("#edit .list form, #edit .list > div").hide();
						if ( $group.hasClass("real") ) {
							$("#dynamic-editor").load("admin-ajax.php", {
								"_wpnonce":"<?php echo wp_create_nonce('wp-mailings') ?>", 
								"_wp_http_referer":"<?php echo $_SERVER['REQUEST_URI'] ?>", 
								action:"mailings",
								subaction:"edit_group_form",
								group:$group.attr("id").split("-")[1]
							}, function(){
								$("#dynamic-editor").fadeIn();
							});
							$("#new_user_add_to_group").val($group.attr("id").split("-")[1]);
						} else {
						
						}
						
						refresh_users();
					}
					
					select_user = function(user) {
						$(".user.selected").removeClass("selected");
						$user = $(user).addClass("selected");
						$("#edit h3").html("<?php _e('Edit %s') ?>".replace("%s", $user.find(".value").html()));
						$("#edit .list form, #edit .list > div").hide();
						$(".user input").attr("checked", false);
						$user.find("input").attr("checked", true);
						$("#dynamic-editor").load("admin-ajax.php", {
							"_wpnonce":"<?php echo wp_create_nonce('wp-mailings') ?>", 
							"_wp_http_referer":"<?php echo $_SERVER['REQUEST_URI'] ?>", 
							action:"mailings",
							subaction:"edit_user_form",
							user:$user.attr("id").split("-")[1]
						}, function(){
							$("#dynamic-editor").fadeIn();
							$("#user_form_groups").load("admin-ajax.php", {
								"_wpnonce":"<?php echo wp_create_nonce('wp-mailings') ?>", 
								"_wp_http_referer":"<?php echo $_SERVER['REQUEST_URI'] ?>", 
								action:"mailings",
								subaction:"user_group_list",
								id:$user.attr("id").split("-")[1]
							});

						});
					}
					
					reset_panels = function() {
						$(".panel .list").each(function() {
							$(this).height($(window).height() - $(this).offset().top);
							//$.log($(this).offset());
						});
					};
					reset_panels();
					
					refresh_users = function() {
						$('#groups .group.selected');
						$('#users .list').load('admin-ajax.php', {
							"_wpnonce":"<?php echo wp_create_nonce('wp-mailings') ?>", 
							"_wp_http_referer":"<?php echo $_SERVER['REQUEST_URI'] ?>", 
							action:"mailings",
							subaction:"list_users",
							group: $('#groups .group.selected').attr("id").split("-")[1]
						}, setup_events);
					};
					
					blank_edit = function() {
						$("#edit .head h3").html("<?php _e("Select a group or user") ?>");
						$("#edit .list form, #dynamic-editor").hide();
						$("#dynamic-editor").html("");
					}
					
					show_error = function(error) {
						if ( error == undefined ) {
							$("#ajax-loading").addClass("error").html("<?php _e('An error has occurred') ?>").show();
						} else {
							$("#ajax-loading").addClass("error").html(error).show();
						}
					}
					
					$(window).resize(function(){reset_panels()});
					$(".panel:not(:last)").css('border-right', 'none');
					$("input[type=text]").hint();
				})(jQuery);
			</script>
		<?php
	}
	
	function add_group() {
		$group = new wpm_group(); 
		$group->name = $_POST['name'];
		$group->save();
		echo $group->to_json();
	}
	
	function add_user() {
		$user = new wpm_user(); 
		$user->name = $_POST['name'];
		$user->email = $_POST['email'];
		$user->notes = $_POST['notes'];
		$user->confirmation = (bool) $_POST['confirmation'];
		$result = $user->save();
		if ( $result ) {
			if ( isset($_POST['add_to_group']) && $_POST['add_to_group'] ) {
				$group = new wpm_group($_POST['add_to_group']); 
				$group->add_user($user);
			}
			echo '{success: true, user: ' . $user->to_json() . '}';
		} else {
			echo '{success: false, error:"' . __('This email address already exists') . '"}';
		}
	}
	
	function list_users() {
		$user = new wpm_user();
		$group = new wpm_group();
		if ( $_POST['group'] == 'all' ) {
			$user->search(null, 'name, email');
		} else {
			$group->get($_POST['group']);
			$user = $group->get_users();
		}
		while ( $user->fetch() ) {
			?>
				<div class="user" id="user-<?php $user->e('id') ?>">
					<input type="checkbox" name="user[]" class="user-input" id="user-input-<?php $user->e('id') ?>" value="<?php $user->e('id') ?>" />
					<?php if ( !empty($user->name) ) : ?>
						<span class="value"><?php $user->e('name') ?></span>
					<?php else : ?>
						<span class="value"><?php $user->e('email') ?></span>
					<?php endif; ?>
				</div>
			<?php
		}
	}
	
	
	function delete_group() {
		$group = new wpm_group($_POST['id']);
		if ( $group->delete() ) {
			echo '{success: true}';
		} else {
			echo '{success: false}';
		}
	}
	
	function delete_user() {
		$user = new wpm_user($_POST['id']);
		if ( $user->delete() ) {
			echo '{success: true}';
		} else {
			echo '{success: false}';
		}
	}

	function edit_group() {
		$group = new wpm_group($_POST['id']);
		$group->name = $_POST['name'];
		$group->save();
		echo $group->to_json();
	}
	
	function edit_user() {
		$user = new wpm_user($_POST['id']);
		$user->name = $_POST['name'];
		$user->email = $_POST['email'];
		$user->notes = $_POST['notes'];
		$user->save();
		echo $user->to_json();
	}
	
	function edit_group_form() {
		$group = new wpm_group($_POST['group']);
		?>
			<div class="alignright">
				<button id="delete-group-<?php $group->e('id') ?>" class="delete-group button">
					<img src="<?php echo wp_mailings::get_url() ?>images/group_delete.png" />
					<?php _e('Delete Group') ?>
				</button>
			</div>
			<form id="edit-group-<?php $group->e('id') ?>" method="post" action="admin-ajax.php">
				<input type="hidden" name="_wpnonce" value="<?php echo $_POST['_wpnonce'] ?>" />
				<input type="hidden" name="_wp_http_referer" value="<?php echo $_POST['_wp_http_referer'] ?>" />
				<input type="hidden" name="action" value="mailings" />
				<input type="hidden" name="subaction" value="edit_group" />
				<input type="hidden" name="id" value="<?php $group->i('id') ?>" />
				<table>
					<tbody>
						<tr>
							<td><?php _e('Group name:') ?> </td>
							<td><input type="text" name="name" value="<?php $group->i('name') ?>" /></td>
						</tr>
						<tr>
							<td></td>
							<td><input type="submit" class="button" value="<?php _e('Save group') ?>" /></td>
						</tr>
					</tbody>
				</table>
			</form>
			<script type="text/javascript">
				(function($){
					$("#delete-group-<?php $group->e('id') ?>").click(function(){
						$.post("admin-ajax.php", {
							'_wpnonce': "<?php echo $_POST['_wpnonce'] ?>",
							'_wp_http_referer': "<?php echo $_POST['_wp_http_referer'] ?>",
							'action': "mailings",
							'subaction': "delete_group",
							'id': "<?php $group->e('id') ?>"
						}, function(rsp){
							$("#group-<?php $group->e('id') ?>").remove();
							select_group("#group-all");
						}, 'json');
					});
					
					$("#edit-group-<?php $group->e('id') ?>").ajaxForm({
						dataType: 'json',
						success: function(rsp){
							$("#edit h3").html("<?php _e('Edit %s') ?>".replace("%s", rsp.name));
							$("#group-" + rsp.id).html(rsp.name);
						}
					});
				})(jQuery);
			</script>
		<?php
	}
	
	function edit_user_form() {
		$user = new wpm_user($_POST['user']);
		?>
			<div class="alignright">
				<button id="delete-user-<?php $user->e('id') ?>" class="delete-user button">
					<img src="<?php echo wp_mailings::get_url() ?>images/user_delete.png" />
					<?php _e('Delete User') ?>
				</button>
			</div>
			<form id="edit-user-<?php $user->e('id') ?>" method="post" action="admin-ajax.php">
				<input type="hidden" name="_wpnonce" value="<?php echo $_POST['_wpnonce'] ?>" />
				<input type="hidden" name="_wp_http_referer" value="<?php echo $_POST['_wp_http_referer'] ?>" />
				<input type="hidden" name="action" value="mailings" />
				<input type="hidden" name="subaction" value="edit_user" />
				<input type="hidden" name="id" value="<?php $user->i('id') ?>" />
				<table>
					<tbody>
						<tr>
							<td><?php _e('Name:') ?> </td>
							<td><input type="text" name="name" value="<?php echo $user->i('name') ?>" /></td>
						</tr>
						<tr>
							<td><?php _e('Email Address:') ?> </td>
							<td><input type="text" name="email" value="<?php echo $user->i('email') ?>" /></td>
						</tr>
						<tr>
							<td valign="top"><?php _e('Other Notes:') ?> </td>
							<td><textarea name="notes" cols="30" rows="7"><?php echo htmlspecialchars($user->notes) ?></textarea></td>
						</tr>
						<tr>
							<td></td>
							<td><input type="submit" class="button" value="Save user" /></td>
						</tr>
					</tbody>
				</table>
			</form>
			<div id="user_form_groups"></div>
			<script type="text/javascript">
				(function($){
					$("#delete-user-<?php $user->e('id') ?>").click(function(){
						$.post("admin-ajax.php", {
							'_wpnonce': "<?php echo $_POST['_wpnonce'] ?>",
							'_wp_http_referer': "<?php echo $_POST['_wp_http_referer'] ?>",
							'action': "mailings",
							'subaction': "delete_user",
							'id': "<?php $user->e('id') ?>"
						}, function(rsp){
							$("#user-<?php $user->e('id') ?>").remove();
							refresh_users();
							blank_edit();
						}, 'json');
					});
					
					$("#edit-user-<?php $user->e('id') ?>").ajaxForm({
						dataType: 'json',
						success: function(rsp){
							if ( rsp.name == "" ) {
								var value = rsp.email;
							} else {
								var value = rsp.name;
							}
							$("#edit h3").html("<?php _e('Edit %s') ?>".replace("%s", value));
							$("#user-" + rsp.id + " .value").html(value);
						}
					});
				})(jQuery);
			</script>
		<?php
	}
	
	function user_group_list() {
		$groups = new wpm_group(); 
		$groups->search(null, 'name');
		if ( isset($_POST['id']) ) {
			$user = new wpm_user();
			$user->get($_POST['id']);
			$memberof = $user->get_groups();
			$memberof_array = array();
			?>
				<h4 style="margin-bottom: 0px;"><?php _e('Member of:') ?></h4>
				<table id="groups_remove" class="groups"><tbody>
					<?php while ( $memberof->fetch() ) : $memberof_array[] = $memberof->id; ?>
						<tr class="group" id="manage-group-<?php $memberof->e('id') ?>">
							<td><?php $memberof->e('name') ?></td>
							<td>
								<button class="button icon icon_group_delete remove" title="<?php _e("Remove From Group") ?>"><?php _e("Remove") ?></button>
							</td>
						</tr>
					<?php endwhile ?>
				</tbody></table>
				
				<h4 style="margin-bottom: 0px;"><?php _e('Other groups:') ?></h4>
				<table id="groups_add" class="groups"><tbody>
					<?php while ( $groups->fetch() ) : if ( !in_array($groups->id, $memberof_array) ) :?>
						<tr class="group" id="manage-group-<?php $groups->e('id') ?>">
							<td><?php $groups->e('name') ?></td>
							<td>
								<button class="button icon icon_group_add add" title="<?php _e("Add to Group") ?>"><?php _e("Add") ?></button>
							</td>
						</tr>
					<?php endif; endwhile ?>
				</tbody></table>

				<script type="text/javascript">
					(function($){
						$("#user_form_groups table.groups").click(function(e){
							if ( $(e.target).is("button.icon_group_add") ) {
								$target = $(e.target);
								$.post("admin-ajax.php", {
									'_wpnonce': "<?php echo $_POST['_wpnonce'] ?>",
									'_wp_http_referer': "<?php echo $_POST['_wp_http_referer'] ?>",
									'action': "mailings",
									'subaction': "user_group_add",
									'group_id': $target.parents("tr").eq(0).attr("id").split("-")[2],
									'user_id': <?php $user->e("id") ?>
								}, function(){
									$target.removeClass("icon_group_add add").addClass("icon_group_delete remove").attr("title", "<?php _e("Remove From Group") ?>").html("<?php _e("Remove") ?>").parents("tr").clone().appendTo("#groups_remove");
									$target.parents("tr").remove();
								});
							} else if ( $(e.target).is("button.icon_group_delete") ) {
								$target = $(e.target);
								$.post("admin-ajax.php", {
									'_wpnonce': "<?php echo $_POST['_wpnonce'] ?>",
									'_wp_http_referer': "<?php echo $_POST['_wp_http_referer'] ?>",
									'action': "mailings",
									'subaction': "user_group_remove",
									'group_id': $target.parents("tr").eq(0).attr("id").split("-")[2],
									'user_id': <?php $user->e("id") ?>
								}, function(){
									$target.removeClass("icon_group_delete remove").addClass("icon_group_add add").attr("title", "<?php _e("Add to Group") ?>").html("<?php _e("Add") ?>").parents("tr").clone().appendTo("#groups_add");
									$target.parents("tr").remove();
								});
							}
						});

					})(jQuery);
				</script>

			<?php
		} else {
			?>
				<div id="users-groups-management">
					<b><?php _e('Add or remove the selected users from groups') ?></b>
					<table><tbody>
						<?php while ( $groups->fetch() ) : ?>
							<tr class="group" id="manage-group-<?php $groups->e('id') ?>">
								<td><?php $groups->e('name') ?></td>
								<td>
									<button class="button icon icon_group_add add" title="<?php _e("Add to Group") ?>"><?php _e("Add") ?></button>
									<button class="button icon icon_group_delete remove" title="<?php _e("Remove From Group") ?>"><?php _e("Remove") ?></button>
								</td>
							</tr>
						<?php endwhile ?>
					</tbody></table>
				</div>
				<script type="text/javascript">
					(function($){
						$("#users-groups-management .add").click(function(){
							users = [];
							$("#users .user input:checked").each(function(){
								users.push($(this).parent().attr("id").split("-")[1]);
							});
							users = users.join(",");
							$.post("admin-ajax.php", {
								'_wpnonce': "<?php echo $_POST['_wpnonce'] ?>",
								'_wp_http_referer': "<?php echo $_POST['_wp_http_referer'] ?>",
								'action': "mailings",
								'subaction': "user_group_add",
								'group_id': $(this).parents("tr").eq(0).attr("id").split("-")[2],
								'user_id': users
							});
						});
						$("#users-groups-management .remove").click(function(){
							users = [];
							$("#users .user input:checked").each(function(){
								users.push($(this).parent().attr("id").split("-")[1]);
							});
							users = users.join(",");
							$.post("admin-ajax.php", {
								'_wpnonce': "<?php echo $_POST['_wpnonce'] ?>",
								'_wp_http_referer': "<?php echo $_POST['_wp_http_referer'] ?>",
								'action': "mailings",
								'subaction': "user_group_remove",
								'group_id': $(this).parents("tr").eq(0).attr("id").split("-")[2],
								'user_id': users
							});
						});
						
					})(jQuery);
				</script>

			<?php
		}
	}
	
	function user_group_add() {
		$group = new wpm_group($_POST['group_id']);
		$users = explode(",", $_POST['user_id']);
		foreach ( $users as $user ) {
			$group->add_user($user);
		}
	}
	
	function user_group_remove() {
		$group = new wpm_group($_POST['group_id']);
		$users = explode(",", $_POST['user_id']);
		foreach ( $users as $user ) {
			$group->remove_user($user);
		}
	}
}