<?php

class wp_mailings_public {
	function signup_form($atts) {
		ob_start();
		extract($atts = shortcode_atts(
			array(
				'fields' => 'name,email',
			), $atts
		));
		$fields = explode(",", $fields);
		?>
			<form class="mailings-signup" method="post" action="<?php echo get_bloginfo("url") ?>">
				<input type="hidden" name="action" value="mailings" />
				<input type="hidden" name="subaction" value="signup" />
				<table>
					<tbody>
						<?php foreach ( $fields as $field ) : ?>
							<?php if ( 'name' == $field ) : ?>
								<tr>
									<td><?php _e('Your name:') ?> </td>
									<td><input type="text" name="name" value="" /></td>
								</tr>
							<?php endif ?>
							<?php if ( 'email' == $field ) : ?>
								<tr>
									<td><?php _e('Email Address:') ?> </td>
									<td><input class="hint required" type="text" name="email" value="" title="Required" /></td>
								</tr>
							<?php endif ?>
						<?php endforeach ?>
						<?php do_action('wpm_form_extra') ?>
						<tr>
							<td></td>
							<td>
								<input type="submit" name="" value="<?php echo apply_filters('wpm_signup_submit', __('Signup')) ?>" />
								<input type="reset" name="" value="<?php echo apply_filters('wpm_signup_reset', __('Reset')) ?>" />
							</td>
						</tr>
					</tbody>
				</table>
			</form>
			<div class="mailings-signup-result"></div>
			<script type="text/javascript">
				;(function($){
					$(".mailings-signup input.hint").hint();
					$(".mailings-signup").submit(function(){
						$this = $(this);
						$result = $this.next();
						var valid = true;
						$this.find(".required").each(function(){
							if ( $(this).val() == "" ) {
								valid = false;
							}
						});
						if ( valid ) {
							$this.ajaxSubmit({
								success: function(rsp){
									if ( rsp.success == undefined || (!rsp.success && rsp.error == undefined) ) {
										$result.html("<?php _e("An unknown error has occurred.") ?>");
									} else if ( rsp.success ) {
										$result.html("<?php _e("Thanks for signing up! You will receive a message to confirm your email address shortly.") ?>");
										$this.find("input[type=reset]").click();
										$this.find("input.hint").focus().blur();
									} else {
										$result.html(rsp.error);
									}
								}, 
								error: function(xhr, status, error){
									$result.addClass("error");
									if ( status == 'error' ) {
										$result.html("<?php _e("The following error was returned: %d %s") ?>".replace("%d", xhr.status).replace("%s", xhr.statusText));
									} else {
										$result.html("<?php _e("An unknown error has occurred.") ?>");
									}
								},
								dataType: 'json'
							});
						}
						return false;
					});
				})(jQuery);
				
				<?php do_action('wpm_form_scripts') ?>
			</script>
		<?php 
		return ob_get_clean();
	}
	
	function ajax_signup() {
		$args = do_action('wpm_signup');
		$user = new wpm_user();
		$user->name = $_POST['name'];
		$user->email = $_POST['email'];
		if ( $user->save() ) {
			do_action('_wpm_signup', $user);
			echo '{success: true, user: ' . $user->to_json() . '}';
		} else {
			echo '{success: false}';
		}
		
	}
}

add_shortcode('mailings-signup', array('wp_mailings_public', 'signup_form'));

add_action('init', array('wp_mailings_public', 'init'));

?>