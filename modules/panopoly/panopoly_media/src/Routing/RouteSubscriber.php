<?php

namespace Drupal\panopoly_media\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Implements a route subscriber for Panopoly Media.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Remove the title from our entity browsers.
    $entity_browser_ids = [
      'panopoly_media_field_media_browser',
      'panopoly_media_wysiwyg_media_browser',
    ];
    foreach ($entity_browser_ids as $entity_browser_id) {
      if ($route = $collection->get("entity_browser.{$entity_browser_id}")) {
        $defaults = $route->getDefaults();
        unset($defaults['_title_callback']);
        $route->setDefaults($defaults);
      }
    }
  }

}
