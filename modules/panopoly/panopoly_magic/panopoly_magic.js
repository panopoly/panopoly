(function ($) {

 Drupal.behaviors.panopolyMagic = {
   attach: function (context, settings) {

     /**
      * Title Hax for Panopoly
      *
      * Replaces the markup of a node title pane with
      * the h1.title page element
      */
     if ($('.pane-node-title .pane-content').val() == $('h1.title').val()) {
       $('.pane-node-title .pane-content').html('').prepend($('h1.title'));
     }

     /**
      * Submitted Hax for Panopoly
      *
      * Replaces the markup of a node created pane with
      * the submitted node value
      */
     if ($('.pane-node-created')) {
       $('.pane-node-created .pane-content').html('').prepend($('.pane-node-content .submitted'));
     }

   }
 }

})(jQuery);


