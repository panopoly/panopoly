<?php

namespace Drupal\panopoly_magic\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Implements a route subscriber for Panopoly Magic.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Replace the layout builder choose block route with our own.
    if ($route = $collection->get('layout_builder.choose_block')) {
      $route->setDefaults([
        '_controller' => '\Drupal\panopoly_magic\Controller\LayoutBuilderChooseBlockController::build',
        '_title' => $route->getDefault('title'),
      ]);
    }
    if ($route = $collection->get('layout_builder.choose_inline_block')) {
      $route->setDefaults([
        '_controller' => '\Drupal\panopoly_magic\Controller\LayoutBuilderChooseBlockController::inlineBlockList',
        '_title' => $route->getDefault('title'),
      ]);
    }
  }

}
