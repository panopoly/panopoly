<?php

namespace Drupal\panopoly_widgets\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for Panopoly Widgets.
 */
class ContentItemController extends ControllerBase {


  /**
   * Node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * ContentItemController constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_storage
   *   Node storage.
   */
  public function __construct(EntityStorageInterface $node_storage) {
    $this->nodeStorage = $node_storage;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('node')
    );
  }

  /**
   * Handles autocomplete for the content item widget.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $type
   *   The content type to search for.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function handleContentItemAutocomplete(Request $request, $type = 'any') {
    $results = [];
    $input = $request->query->get('q');

    $query = $this->nodeStorage->getQuery()
      ->condition('title', $input, 'CONTAINS')
      ->sort('title', 'ASC')
      ->range(0, 10)
      ->addTag('node_access');

    if ($type !== 'any') {
      $query->condition('type', $type);
    }

    $ids = $query->execute();
    $nodes = $ids ? $this->nodeStorage->loadMultiple($ids) : [];

    /** @var \Drupal\node\Entity\Node $node */
    foreach ($nodes as $node) {
      if ($node->isPublished() && $node->access('view')) {
        $label = $node->label();
        $entity_id = $node->id();

        // Code taken from EntityAutocompleteMatcher.
        $key = "$label ($entity_id)";
        // Strip things like starting/trailing white spaces, line breaks and
        // tags.
        $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
        // Names containing commas or quotes must be wrapped in quotes.
        $key = Tags::encode($key);
        $results[] = ['value' => $key, 'label' => $label];
      }
    }

    return new JsonResponse($results);
  }

}
