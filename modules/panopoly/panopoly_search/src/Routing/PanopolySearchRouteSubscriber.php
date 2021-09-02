<?php

namespace Drupal\panopoly_search\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Removes module dependency from /admin/reports/search.
 */
class PanopolySearchRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // We need to run before ModuleRouteSubscriber.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', 100];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Since panopoly_search is enabled, we want dblog.search to work, so
    // remove the existing module dependency.
    if ($route = $collection->get('dblog.search')) {
      $requirements = $route->getRequirements();
      unset($requirements['_module_dependencies']);
      $route->setRequirements($requirements);
    }
  }

}
