<?php

class wp_mailings_groups {
	function admin_page() {
		$groups = new wpm_group(); 
		$groups->search(null, 'name');
		//print_r($groups);
		echo WPM_TABLE_GROUPS;
		?>
			<div class="wrap">
				<h2><?php _e('WP-Mailings &rsaquo; Groups') ?></h2>
			</div>
			<div class="panels">
				<div class="panel" id="groups">
					<div class="head">
						<h3><?php _e('Groups') ?></h3>
						<div id="new-group-trigger" class="vat clickable">
							<img class="" alt="Add" title="Add a group" src="<?php echo wp_mailings::get_url() ?>images/add.png" />
							<?php _e('Add a new group') ?>
							<form style="display: none;" id="new-group-form" method="post" action="admin-ajax.php">
								<?php wp_nonce_field('wp-mailings') ?>
								<input type="hidden" name="action" value="mailings" />
								<input type="hidden" name="subaction" value="add_group" />
								<input type="hidden" name="selected" value="" />
								<?php _e('Name:') ?> <input type="text" name="name" id="new-name" />
								<input class="button" type="submit" value="Add Group" />
							</form>
						</div>
					</div>
					<div class="list">
						<?php while ( $groups->fetch() ) : ?>
							<div class="group" id="group-<?php echo $groups->id ?>"><?php echo $groups->name ?></div>
						<?php endwhile ?>
					</div>
				</div>
				<div class="panel" id="members">
					hello
						<form method="post" action="admin-ajax.php">
							<div class="vam">
								<img class="" alt="" title="" src="<?php echo wp_mailings::get_url() ?>images/magnifier.png" />
								<input type="text" value="" name="group-filter" id="group-filter" />
							</div>
						</form>

					<div class="list">
						<?php echo wp_mailings::get_path(); ?>
					</div>
				</div>
				<div class="panel" id="non-members">
					hello
					<div class="list">
						<?php echo wp_mailings::get_path(); ?>
					</div>
				</div>
			</div>
			
			<script type="text/javascript">
				;(function($){
				
					$("#new-group-trigger").click(function(){
						$("#new-group-form").show();
						reset_panels();
					});
					
					$("#new-group-form").ajaxForm(function(rsp){
						$("#groups .list").html(rsp);
					});
				
					reset_panels = function() {
						$(".panel .list").each(function() {
							$(this).height($(window).height() - $(this).offset().top);
							$.log($(this).offset());
						});
					};
					reset_panels();
					$(window).resize(function(){reset_panels()});
					$(".panel").resize(function(){reset_panels()});
				})(jQuery);
			</script>
		<?php
	}
	
	function add_group() {
		$group = new wpm_group(); 
		$group->name = $_POST['name'];
		var_dump($group->save());
		echo $group->get_error();
		$group->search(null, 'name');
		while ( $group->fetch() ) : ?>
			<div class="group" id="group-<?php echo $group->id ?>"><?php echo $group->name ?></div>
		<?php endwhile;
	}
	
}