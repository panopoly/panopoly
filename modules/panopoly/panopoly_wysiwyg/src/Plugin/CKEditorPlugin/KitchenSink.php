<?php

namespace Drupal\panopoly_wysiwyg\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "panopoly_wysiwyg_kitchensink" plugin.
 *
 * NOTE: The plugin ID ('id' key) corresponds to the CKEditor plugin name.
 * It is the first argument of the CKEDITOR.plugins.add() function in the
 * plugin.js file.
 *
 * @CKEditorPlugin(
 *   id = "panopoly_wysiwyg_kitchensink",
 *   label = @Translation("Kitchen Sink")
 * )
 */
class KitchenSink extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   *
   * NOTE: The keys of the returned array corresponds to the CKEditor button
   * names. They are the first argument of the editor.ui.addButton() or
   * editor.ui.addRichCombo() functions in the plugin.js file.
   */
  public function getButtons() {
    return [
      'panopoly_wysiwyg_kitchensink' => [
        'label' => t('Kitchen Sink'),
        'image' => drupal_get_path('module', 'panopoly_wysiwyg') . '/ckeditor/kitchensink/icons/kitchensink.png',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'panopoly_wysiwyg') . '/ckeditor/kitchensink/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function isInternal() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

}
