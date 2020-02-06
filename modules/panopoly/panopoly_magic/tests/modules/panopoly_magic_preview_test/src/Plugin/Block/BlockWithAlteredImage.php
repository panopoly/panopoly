<?php

namespace Drupal\panopoly_magic_preview_test\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A block that takes a setting that'll be displayed.
 *
 * @Block(
 *   id = "panopoly_magic_preview_test_altered_image",
 *   admin_label = @Translation("Block with (altered) preview image"),
 *   category = @Translation("Panopoly Magic Preview Test"),
 * )
 */
class BlockWithAlteredImage extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function build() {
    return [
      '#markup' => $this->t("BlockWithAlteredImage: normal block content"),
    ];
  }

}
