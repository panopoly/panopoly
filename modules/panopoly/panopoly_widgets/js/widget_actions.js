(function (Drupal, $) {

    //argument passed from InvokeCommand
    $.fn.cleanNodeAutoComplete = function(argument)
    {
        //Clear input
        $('#node-selector-wrapper').find('input').val('');
    };

})(Drupal, jQuery);
