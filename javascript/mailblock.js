(function($) {
    $(document).ready(function(){
        showHideButton();
        $(document).on('tabsactivate click', '.cms-tabset-nav-primary a', function() {
            setTimeout(showHideButton, 0);
        });
        $(document).ajaxComplete(function() {
            setTimeout(showHideButton, 0);
        });

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

