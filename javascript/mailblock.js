(function($) {
    $(document).ready(function(){
        showHideButton();
        $(document).on('shown.bs.tab click', '.nav-tabs .nav-link', function() {
            setTimeout(showHideButton, 0);
        });
        $(document).ajaxComplete(function() {
            setTimeout(showHideButton, 0);
        });

        function showHideButton() {
            if ($('#Root_Mailblock').is(':visible')) {
                $('#Form_EditForm_action_mailblockTestEmail').show();
            }
            else {
                $('#Form_EditForm_action_mailblockTestEmail').hide();
            }
        }
    })
})(jQuery);
