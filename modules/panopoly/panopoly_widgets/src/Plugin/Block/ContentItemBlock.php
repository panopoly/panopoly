<?php

namespace Drupal\panopoly_widgets\Plugin\Block;

use Drupal\node\Entity\NodeType;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
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
    $content_types = $this->getContentTypes();

    $form['#attached'] = ['library' => ['panopoly_widgets/content-item']];
    $form['content_type'] = [
      '#type' => 'select',
      '#options' => array_merge(['all' => 'Any'], $content_types),
      '#title' => $this->t('Content type'),
      '#default_value' => $entity ? $entity->bundle() : 'all',
      '#ajax' => [
        'callback' => [$this, 'autocompleteGetNodes'],
      ],
    ];
    $form['node'] = [
      '#prefix' => '<div id="node-selector-wrapper">',
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Piece of content'),
      '#target_type' => 'node',
      '#default_value' => $entity,
      '#required' => TRUE,
      // @todo Properly update this to any AJAX value for `content_type`.
      //   There are some complications as $form_state->getValues() breaks due
      //   to Layout Builder leveraging subform states. This requires us to
      //   use a #process callback, but that does not seem to effect the
      //   selection settings passed to the autocomplete, since AJAX was
      //   triggered by a regular element and not a button. Without a button
      //   triggering the rebuild, these changes are not respected.
      //
      //   This element needs to be rebuild with a new selection settings key in
      //   its autocomplete-path property.
      '#selection_settings' => [
        'target_bundles' => array_keys($content_types),
      ],
      '#suffix' => '</div>',
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
   * Gets AJAX response for node autocomplete.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response.
   */
  public function autocompleteGetNodes(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'panopolyWidgetsCleanNodeAutoComplete', []));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['nid'] = $form_state->getValue('node');
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
