(function ($, Drupal) {

  // Used to only update preview after changes stop.
  let timer = null;

  // Holds the un-monkey-patched version of Drupal.behaviors.dialog.
  let drupalBehaviorsDialogAttach = null;

  // Used to as a "kill switch" to prevent the dialog from stealing focus on preview reload.
  let preventDialogStealingFocus = false;

  Drupal.behaviors.panopolyMagicLiveUpdate = {
    attach: function (context) {
      // e.keyCode: key
      const discardKeyCode = [
        16, // shift
        17, // ctrl
        18, // alt
        20, // caps lock
        33, // page up
        34, // page down
        35, // end
        36, // home
        37, // left arrow
        38, // up arrow
        39, // right arrow
        40, // down arrow
        9, // tab
        13, // enter
        27  // esc
      ];

      const form_selector = 'form#layout-builder-update-block';
      // Finds matching children or the current context itself.
      // @see https://stackoverflow.com/questions/2828019/looking-for-jquery-find-method-that-includes-the-current-node
      const $form = $(context).find(form_selector).addBack(form_selector);
      const $preview = $form.find('.panopoly-magic-live-preview');

      // When the preview is rendered, misc/dialog/dialog.ajax.js will steal focus from whatever it was on. Here we
      // monkey-patch Drupal.behaviors.dialog.attach to give ourselves a "kill switch" for when the preview is
      // rendering, that is the preventDialogStealFocus variable.
      if (!drupalBehaviorsDialogAttach && Drupal.behaviors.dialog) {
        drupalBehaviorsDialogAttach = Drupal.behaviors.dialog.attach;
        Drupal.behaviors.dialog.attach = function (context, settings) {
          if (preventDialogStealingFocus && $(context).closest('.ui-dialog-content').length > 0) {
            preventDialogStealingFocus = false;
          }
          else {
            drupalBehaviorsDialogAttach(context, settings);
          }
        };
      }

      // Trigger "Cancel" button when off canvas tray closed using the close button.
      $(form_selector)
        .closest('.ui-dialog-off-canvas')
        .find('.ui-dialog-titlebar-close')
        .off('click')
        .click(function (e) {
          return triggerCancel(form_selector, e);
        });

      // Trigger "Cancel" button when off canvas tray closed using the ESC key.
      $(form_selector)
        .closest('.ui-dialog-off-canvas')
        .off('keydown')
        .keydown(function (e) {
          if (e.keyCode === 27) {
            return triggerCancel(form_selector, e);
          }
        });

      function triggerCancel(form_selector, e) {
        // Rather than refer to $form, this sets this event handler only once, and then looks up if there is a cancel
        // button available on the right form. This way we don't have to worry about attaching and detaching this
        // event handler.
        var cancel = $(form_selector).find('[data-drupal-selector="edit-actions-cancel"]');
        if (cancel.length) {
          cancel.trigger('mousedown');
          e.preventDefault();
          return false;
        }
      }

      if ($form.length === 0 || $preview.length === 0) {
        return;
      }

      function triggerSubmit() {
        if (timer) {
          clearTimeout(timer);
        }
        // Prevent preview if 'Reusable' is checked because previews don't work with reusable widgets.
        if (!$form.find('[data-drupal-selector="edit-reusable"]').is(':checked')) {
          timer = setTimeout(submitForm, 500);
        }
        else {
          timer = null;
        }
      }

      // Submits the form.
      function submitForm() {
        // Clear timer.
        timer = null;

        // Disable the preview button.
        $preview.prop('disabled', true);

        // Prevent dialog.ajax.js from stealing focus.
        preventDialogStealingFocus = true;

        // Note: .click does not work here.
        // See https://drupal.stackexchange.com/questions/11638/how-to-programmatically-trigger-a-click-on-an-ajax-enabled-form-submit-button
        $preview.mousedown();
      }

      // Hide the preview button if 'Reusable' is checked because previews don't work with reusable widgets.
      $form.find('[data-drupal-selector="edit-reusable"]').once().change(function () {
        $preview.prop('disabled', this.checked);
        $preview.toggle(!this.checked)
      });

      // Text input.
      $form.find('[type="text"], textarea, [type="number"]').on('keyup', function (e) {
        // Filter out discarded keys.
        if ($.inArray(e.keyCode, discardKeyCode) !== -1) {
          return;
        }

        triggerSubmit();
      });

      // WYSIWYG.
      if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.on('instanceCreated', function(e) {
          let editor = e.editor,
              element = e.element;
          editor.on('change', triggerSubmit);
        });
      }

      // Checkboxes, radios, colors, ranges, selects.
      $form.find('[type="checkbox"], [type="radio"], [type="number"], [type="color"], [type="range"], select').on('change', function () {
        triggerSubmit();
      });

      // Media Library widget.
      var trackChange = function(index, element) {
        var observer = new MutationObserver(function(mutations, observer) {
          triggerSubmit();
        });
        observer.observe(element, { childList: true });
      }
      $form.find('.media-library-widget, .js-media-library-widget').parent().each(trackChange);

      // Autocomplete fields.
      $form.find('.form-autocomplete').on('autocompleteclose', function (e, ui) {
        triggerSubmit();
      });

    }
  }

})(jQuery, Drupal);
