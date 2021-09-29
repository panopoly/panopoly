(function (Drupal, $) {
  var nid_regex = / \(\d+\)$/;

  Drupal.behaviors.panopoly_widgets_content_item = {
    attach: function (context, settings) {
      $('.js-panopoly-widgets-content-item-type', context).once().each(function () {
        var self = this;
        var autocomplete_field = $(this).closest('form').find('.js-panopoly-widgets-content-item-autocomplete');
        var autocomplete_field_id = autocomplete_field.attr('id');
        var autocomplete_base_url = settings.panopoly_widgets_content_item.autocomplete_base_url;

        $(this).change(function () {
          // Change the autocomplete path and clear the current node.
          autocomplete_field
            .attr('data-autocomplete-path', autocomplete_base_url.replace(encodeURIComponent('@TYPE@'), $(self).val()))
            .val('');

          // Clear autocomplete cache so we don't get results that applied to
          // other content types.
          Drupal.autocomplete.cache[autocomplete_field_id] = {};
        });
      });

      $('.js-panopoly-widgets-content-item-autocomplete', context).once().on('autocompleteclose', function (e, ui) {
        var val = $(e.target).val();
        if (val.search(nid_regex) !== -1) {
          val = val.replace(nid_regex, '');
          $('.js-panopoly-widgets-content-item-label').val(val);
        }
      });
    }
  };

})(Drupal, jQuery);
