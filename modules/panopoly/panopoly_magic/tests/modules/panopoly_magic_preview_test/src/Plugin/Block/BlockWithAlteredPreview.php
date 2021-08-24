<?php

namespace Drupal\panopoly_magic_preview_test\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * A block that takes a setting that'll be displayed.
 *
 * @Block(
 *   id = "panopoly_magic_preview_test_altered_preview",
 *   admin_label = @Translation("Block with (altered) preview"),
 *   category = @Translation("Panopoly Magic Preview Test"),
 * )
 */
class BlockWithAlteredPreview extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function build() {
    return [
      '#markup' => $this->t("BlockWithAlteredPreview: normal block content"),
    ];
  }

}
