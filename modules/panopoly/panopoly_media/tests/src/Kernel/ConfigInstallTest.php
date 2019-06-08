<?php

namespace Drupal\Tests\panopoly_media\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that the module's config does have errors.
 *
 * @group panopoly_media
 */
class ConfigInstallTest extends KernelTestBase {

  protected static $modules = [
    // Core.
    'system',
    'user',
    'field',
    'text',
    'node',
  ];

  /**
   * Installs the module.
   *
   * Any schema and dependency errors will throw an exception.
   *
   * We install this way to replicate normal installation process. Installing
   * the config directly for all dependencies via self::$modules prevents
   * the entity_embed module from generating derivatives and causing failures.
   */
  public function testInstall() {
    $this->installConfig(self::$modules);

    $install = [
      'image',
      'file',
      'views',
      // Contrib.
      'media',
      'embed',
      'entity_embed',
      'entity_browser',
      'media_entity_browser',
      'dropzonejs',
      'dropzonejs_eb_widget',
      'video_embed_field',
      'video_embed_media',
      'panopoly_media',
    ];
    $this->container->get('module_installer')->install($install);
  }

}
