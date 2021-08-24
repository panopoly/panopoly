<?php

namespace Drupal\panopoly_magic_preview_test\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\panopoly_magic\BlockPreviewInterface;

/**
 * A block that uses an image preview.
 *
 * @Block(
 *   id = "panopoly_magic_preview_test_image",
 *   admin_label = @Translation("Block with image preview"),
 *   category = @Translation("Panopoly Magic Preview Test"),
 * )
 */
class BlockWithImagePreview extends BlockBase implements BlockPreviewInterface {

  /**
   * {@inheritDoc}
   */
  public function build() {
    return [
      '#markup' => $this->t("BlockWithImagePreview: normal block content"),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function buildPreview() {
    return [
      '#theme' => 'image',
      '#uri' => drupal_get_path('module', 'panopoly_magic_preview_test') . '/images/block-preview.png',
      '#alt' => $this->t("BlockWithImagePreview: default preview image"),
    ];
  }

}
