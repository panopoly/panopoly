<?php

namespace Drupal\panopoly_widgets\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
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
    $form['node'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Piece of content'),
      '#target_type' => 'node',
      '#default_value' => $this->loadEntity(),
      '#required' => TRUE,
    ];
    $form['view_mode'] = [
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions('node'),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->configuration['view_mode'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['nid'] = $form_state->getValue('node')['target_id'];
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $view_builder = $this->entityTypeManager->getViewBuilder('node');

    $build = [];

    if ($entity = $this->loadEntity()) {
      $build = $view_builder->view($entity, $this->configuration['view_mode']);

      CacheableMetadata::createFromObject($entity)
        ->applyTo($build);
    }

    return $build;
  }

}