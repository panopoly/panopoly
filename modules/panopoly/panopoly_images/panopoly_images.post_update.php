<?php

/**
 * @file
 * Post update functions for the panopoly_images module.
 */

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;

/**
 * Create new image and responsive image styles.
 */
function panopoly_images_post_update_create_new_image_styles() {
  $module_handler = \Drupal::moduleHandler();
  $config_install_path = $module_handler->getModule('panopoly_images')->getPath() . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY;
  /** @var \Drupal\Core\Config\StorageInterface $config_storage */
  $config_storage = \Drupal::service('config.storage');

  // Update all image styles config.
  $source = new FileStorage($config_install_path);
  foreach (['image.style', 'responsive_image.styles'] as $prefix) {
    foreach ($source->listAll($prefix) as $config_name) {
      $config_storage->write($config_name, $source->read($config_name));
    }
  }
}
