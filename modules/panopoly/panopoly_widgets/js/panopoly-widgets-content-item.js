(function (Drupal, $) {

  $.fn.panopolyWidgetsCleanNodeAutoComplete = function(argument)
  {
    // Clear input.
    $('#node-selector-wrapper').find('input').val('');
  };

  Drupal.behaviors.panopoly_widgets_content_item = {
    attach: function (context, settings) {
      $('.js-panopoly-widgets-content-item-autocomplete', context).once().on('autocompleteclose', function (e, ui) {
        var val = $(e.target).val();
        val = val.replace(/ \(\d+\)$/, '');
        $('.js-panopoly-widgets-content-item-label').val(val);
      });
    }
  };

})(Drupal, jQuery);
