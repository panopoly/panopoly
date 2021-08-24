<?php

namespace Drupal\panopoly_magic;

/**
 * Interface for block plugins that want to render their preview special.
 */
interface BlockPreviewInterface {

  /**
   * Builds render array for block preview.
   *
   * Like BlockPluginInterface::build() but for preview.
   *
   * @return array
   *   Render array of block preview content.
   */
  public function buildPreview();

}
