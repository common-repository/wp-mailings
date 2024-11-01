var ajax_action_count = 0;

;(function($){
	$.fn.log = function() {
		return this.each(function(){
			if ( window.console ) {
				console.log(this);
			}
		});
	};
	
	$.log = function() {
		if ( window.console ) {
			for( var i = 0; i < arguments.length; i++ ) {
				console.log(arguments[i]);
			}
		}
	};
	/*
	$.wpm_post = function(options) {
		defaults = {
			type: "POST",
			type: "POST",
		}
	}
	*/
	$.ajaxSetup({
		beforeSend: function(){
			if ( ++ajax_action_count < 1 ) ajax_action_count = 1;
			$("#ajax-loading").removeClass("error").html('<img src="http://dev.ssdn.us/wp-content/plugins/wp-mailings/images/ajax-loader.gif"/>').show();
		}, success: function(){
			if ( --ajax_action_count < 1 ) {
				$("#ajax-loading:not(.error)").hide();
			}
		}, complete: function(){
			if ( --ajax_action_count < 1 ) {
				$("#ajax-loading:not(.error)").hide();
			}
		}, error: function(xhr, status, error){
			$.log(xhr, status, error);
			$("#ajax-loading").addClass("error").html('An error has occured - ' + xhr.status + ": " + xhr.statusText).show();
		}
	});
})(jQuery);

