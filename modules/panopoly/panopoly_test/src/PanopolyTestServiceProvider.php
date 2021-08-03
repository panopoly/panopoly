<?php declare(strict_types=1);

namespace Drupal\panopoly_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StreamWrapper\PrivateStream;

/**
 * Registers private file system when path set through Behat Drupal Driver.
 *
 * In Drupal 7, the private file path was a variable that could be set and
 * manipulated at run time. In Drupal 8 it became a settings to be set in the
 * site's settings.php file.
 *
 * This service modifier allows us to alter the container and register the
 * private file system like the CoreServiceProvider does.
 *
 * @see \Drupal\Core\CoreServiceProvider::register
 */
final class PanopolyTestServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $state = $container->get('state');
    if ($state instanceof StateInterface) {
      $panopoly_test_private_file_path = $state->get('panopoly_test_private_file_path');
      if ($panopoly_test_private_file_path) {
        // Rebuild the settings singleton.
        $settings = Settings::getAll();
        $settings['file_private_path'] = $panopoly_test_private_file_path;
        new Settings($settings);
        $container->register('stream_wrapper.private', PrivateStream::class)
          ->addTag('stream_wrapper', ['scheme' => 'private']);
      }
    }
  }

}
