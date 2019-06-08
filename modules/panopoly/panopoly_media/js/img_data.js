(function (Drupal, $) {

  Drupal.behaviors.panopolyMediaImgData = {
    attach: function (context, settings) {
      $('[data-type="panopolyMediaImgData"]', context).each(function () {
        var el = this;
        var form = $(el).parents('form')[0];
        if (!form) {
          return;
        }

        var formParents = [];
        if (el.dataset.hasOwnProperty('formParents')) {
          formParents = this.dataset.formParents.split(' ');
        }

        var entityType = this.dataset.entityType;
        var mapping = settings.panopolyMediaImgDataMap[entityType];
        var prop, item;

        for (var key in mapping) {
          if (mapping.hasOwnProperty(key)) {
            item = mapping[key];
            if (!item.hasOwnProperty('formElement')) {
              continue;
            }

            prop = camelize('iptc ' + item.iptc);
            setFormValue(form, formParents, item.formElement, el.dataset[prop]);
          }
        }
      });
    }
  };

  /**
   * Sets value into a form item.
   */
  function setFormValue(form, formParents, parts, value) {
    var name = formElName(formParents, parts);
    console.log(name);
    var $el = $('[name="' + name + '"]', form);

    // Unable to locate form element.
    if (!$el.length) {
      return;
    }

    // Handle CKEDITOR.
    var id = $el.attr('id');
    if (id && typeof CKEDITOR.instances[id] !== 'undefined') {
      if (CKEDITOR.instances[id].getData()) {
        // Existing value.
        return;
      }

      CKEDITOR.instances[id].setData(value);
      return;
    }

    // Set the form element value.
    if ($el.val()) {
      // Existing value.
      return;
    }
    $el.val(value);
  }

  /**
   * Constructs a form element name from parents and parts.
   */
  function formElName(formParents, parts) {
    parts = formParents.concat(parts);
    var name = parts.shift();
    if (parts.length) {
      name += '[' + parts.join('][') + ']';
    }
    return name;
  }

  function camelize(str) {
    return str.replace(/(?:^\w|[A-Z]|\b\w|\s+)/g, function(match, index) {
      if (+match === 0) return ""; // or if (/\s+/.test(match)) for white spaces
      return index == 0 ? match.toLowerCase() : match.toUpperCase();
    });
  }

})(Drupal, jQuery);
