(function($) {
  CKEDITOR.plugins.add('panopoly_wysiwyg_kitchensink', {
    icons: 'kitchensink',
    requires: [],

    init: function( editor )
    {
      // Register the toolbar button.
      editor.ui.addButton('panopoly_wysiwyg_kitchensink', {
        label: Drupal.t('Show/hide toolbars'),
        icon: this.path + 'icons/kitchensink.png',
        command: 'panopoly_wysiwyg_kitchensink'
      });

      // Register the editor command.
      editor.addCommand('panopoly_wysiwyg_kitchensink', {
        readOnly: 1,
        editorFocus: false,
        canUndo: false,
        exec: function (editor) {
          var $toolbox = $('.cke_toolbox', editor.container.$),
              isCollapsed = $toolbox.hasClass('panopoly_wysiwyg_kitchensink_collapsed'),
              isFirstRow = true;

          // Toggle collapsed mode.
          isCollapsed = !isCollapsed;

          // Update class marking as collapsed or not, and the button state.
          $toolbox.toggleClass('panopoly_wysiwyg_kitchensink_collapsed', isCollapsed);
          editor.getCommand('panopoly_wysiwyg_kitchensink').setState(isCollapsed ? CKEDITOR.TRISTATE_ON : CKEDITOR.TRISTATE_OFF);

          // Hide/show the secondary toolbars.
          $('.cke_toolbar,.cke_toolbar_break', $toolbox).each(function () {
            isFirstRow = isFirstRow && !$(this).hasClass('cke_toolbar_break');
            if (!isFirstRow && $(this).hasClass('cke_toolbar')) {
              $(this).toggle(!isCollapsed);
            }
          });

          // Record the current state in localStorage.
          window.localStorage.setItem('panopoly_wysiwyg_kitchensink', isCollapsed);
        }
      });

      editor.on('instanceReady', function (e) {
        var editor = e.editor;

        // Enable if it was previously enabled, or this is the first time.
        var enabledByDefault = window.localStorage.getItem('panopoly_wysiwyg_kitchensink');
        if (enabledByDefault === null || enabledByDefault === "true") {
          editor.execCommand('panopoly_wysiwyg_kitchensink');
        }
      });
    }
  });
})(jQuery);
