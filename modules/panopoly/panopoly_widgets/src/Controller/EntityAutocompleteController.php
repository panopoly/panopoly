<?php

namespace Drupal\panopoly_widgets\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\system\Controller\EntityAutocompleteController as CoreEntityAutocompleteController;

/**
 * Controller for entity autocomplete.
 */
class EntityAutocompleteController extends CoreEntityAutocompleteController {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('panopoly_widgets.autocomplete_matcher'),
      $container->get('keyvalue')->get('entity_autocomplete')
    );
  }

}
