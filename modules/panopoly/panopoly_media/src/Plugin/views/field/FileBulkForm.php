<?php

namespace Drupal\panopoly_media\Plugin\views\field;

use Drupal\system\Plugin\views\field\BulkForm;

/**
 * Defines a node operations bulk form element.
 *
 * @ViewsField("panopoly_media_file_managed_bulk_form")
 */
class FileBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No files selected.');
  }

}
