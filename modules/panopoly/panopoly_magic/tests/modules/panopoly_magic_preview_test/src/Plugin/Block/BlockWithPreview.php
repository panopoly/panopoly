<?php

namespace Drupal\panopoly_magic_preview_test\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\panopoly_magic\BlockPreviewInterface;

/**
 * A block that uses an image preview.
 *
 * @Block(
 *   id = "panopoly_magic_preview_test",
 *   admin_label = @Translation("Block with custom preview"),
 *   category = @Translation("Panopoly Magic Preview Test"),
 * )
 */
class BlockWithPreview extends BlockBase implements BlockPreviewInterface {

  /**
   * {@inheritDoc}
   */
  public function build() {
    return [
      '#markup' => $this->t("BlockWithPreview: normal block content"),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function buildPreview() {
    return [
      '#markup' => $this->t("BlockWithPreview: preview block content"),
    ];
  }

}
