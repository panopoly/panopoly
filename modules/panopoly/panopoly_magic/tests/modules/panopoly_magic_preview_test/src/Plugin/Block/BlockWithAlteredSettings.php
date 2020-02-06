<?php

namespace Drupal\panopoly_magic_preview_test\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A block that takes a setting that'll be displayed.
 *
 * @Block(
 *   id = "panopoly_magic_preview_test_altered_settings",
 *   admin_label = @Translation("Block with (altered) preview settings"),
 *   category = @Translation("Panopoly Magic Preview Test"),
 * )
 */
class BlockWithAlteredSettings extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [
      'message' => 'The default message',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#default_value' => $config['message'],
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $config['message'] = $form_state->getValue('message');
    $this->setConfiguration($config);
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    return [
      '#markup' => 'BlockWithAlteredSettings: ' . $config['message'],
    ];
  }

}
