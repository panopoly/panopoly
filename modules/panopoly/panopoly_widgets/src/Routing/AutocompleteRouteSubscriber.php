<?php

namespace Drupal\panopoly_widgets\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Rout subscriber to setup our entity autocomplete controller.
 */
class AutocompleteRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('system.entity_autocomplete')) {
      $route->setDefault('_controller', '\Drupal\panopoly_widgets\Controller\EntityAutocompleteController::handleAutocomplete');
    }
  }

}
