<?php

namespace Drupal\panopoly_media\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\EntityRouteProviderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides routes for files.
 */
class FileRouteProvider implements EntityRouteProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $route_collection = new RouteCollection();

    // Individual file delete form.
    $route = (new Route('/file/{file}/delete'))
      ->addDefaults([
        '_entity_form' => 'file.delete',
        '_title' => 'Delete',
      ])
      ->setRequirement('file', '\d+')
      ->setRequirement('_entity_access', 'file.delete');

    $route_collection->add('entity.file.delete_form', $route);

    // Bulk operation confirmation form.
    $route = (new Route('/admin/content/files/delete'))
      ->addDefaults([
        '_form' => '\Drupal\panopoly_media\Form\FileDeleteMultiple',
      ])
      ->setRequirement('_permission', 'access files overview');
    $route_collection->add('file.multiple_delete_confirm', $route);

    return $route_collection;
  }

}
