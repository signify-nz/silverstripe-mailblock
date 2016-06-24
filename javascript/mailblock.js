(function($) {
	$(document).ready(function(){

		showHideButton();
		$('.cms-tabset-nav-primary li').on('click', showHideButton);
		
		function showHideButton() {
			if ($('#Root_Mailblock_set').is(':visible')) {
				$('#Form_EditForm_action_mailblockTestEmail').show();
			}
			else {
				$('#Form_EditForm_action_mailblockTestEmail').hide();
			}
		}
	})
})(jQuery);