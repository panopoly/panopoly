<?php

namespace Drupal\panopoly_widgets\Plugin\Block;

use Drupal\Core\Url;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Block that displays a node.
 *
 * @Block(
 *   id = "panopoly_widgets_content_item",
 *   admin_label = @Translation("Content item"),
 *   category = @Translation("Content")
 * )
 */
class ContentItemBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a new EntityView.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'nid' => NULL,
      'view_mode' => 'default',
    ];
  }

  /**
   * Load the configured entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity or NULL if it doesn't exist or configuration is empty.
   */
  protected function loadEntity() {
    if (!empty($this->configuration['nid'])) {
      $storage = $this->entityTypeManager->getStorage('node');
      return $storage->load($this->configuration['nid']);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $entity = $this->loadEntity();

    $form['#attached'] = [
      'library' => [
        'panopoly_widgets/content-item',
      ],
      'drupalSettings' => [
        'panopoly_widgets_content_item' => [
          'autocomplete_base_url' => Url::fromRoute('panopoly_widgets.content_item.autocomplete', [
            'type' => '@TYPE@',
          ])->toString(),
        ],
      ],
    ];

    $form['content_type'] = [
      '#type' => 'select',
      '#options' => array_merge(['any' => t('Any')], $this->getContentTypes()),
      '#title' => $this->t('Content type'),
      '#default_value' => 'any',
      '#attributes' => [
        'class' => ['js-panopoly-widgets-content-item-type'],
      ],
    ];

    $form['node'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Piece of content'),
      '#default_value' => $entity ? "{$entity->label()} ({$entity->id()})" : '',
      '#required' => TRUE,
      '#autocomplete_route_name' => 'panopoly_widgets.content_item.autocomplete',
      '#autocomplete_route_parameters' => ['type' => 'any'],
      '#attributes' => [
        'class' => ['js-panopoly-widgets-content-item-autocomplete'],
      ],
    ];

    $form['view_mode'] = [
      '#type' => 'radios',
      '#options' => $this->entityDisplayRepository->getViewModeOptions('node'),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->configuration['view_mode'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['label']['#attributes'] = [
      'class' => ['js-panopoly-widgets-content-item-label'],
    ];
    return $form;
  }

  /**
   * Gets content type options.
   *
   * @return array
   *   An associative array of content types, with the machine names as the keys
   *   and human-readable names as the values.
   */
  private function getContentTypes() {
    $node_types = NodeType::loadMultiple();

    $options = [];
    foreach ($node_types as $node_type) {
      $options[$node_type->id()] = $node_type->label();
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $label = trim($form_state->getValue('node'));
    $type = $form_state->getValue('content_type');
    $nid = NULL;

    if (preg_match("/.+\s\(([^\)]+)\)/", $label, $matches)) {
      $nid = $matches[1];
    }
    else {
      $node_storage = $this->entityTypeManager->getStorage('node');
      $query = $node_storage->getQuery()
        ->condition('title', $label, 'CONTAINS')
        ->sort('title', 'ASC')
        ->range(0, 1)
        ->addTag('node_access');

      if ($type !== 'any') {
        $query->condition('type', $type);
      }

      $results = $query->execute();
      if (!empty($results)) {
        $nid = reset($results);
      }
    }

    $this->configuration['nid'] = $nid;
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $view_builder = $this->entityTypeManager->getViewBuilder('node');

    $build = [];

    if ($entity = $this->loadEntity()) {
      // Hide the node title because we're putting it in the block title.
      $entity->title = '';

      $build = $view_builder->view($entity, $this->configuration['view_mode']);

      CacheableMetadata::createFromObject($entity)
        ->applyTo($build);

      $build['#title'] = Link::fromTextAndUrl($this->configuration['label'], $entity->toUrl());
    }

    return $build;
  }

}
