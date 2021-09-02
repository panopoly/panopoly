<?php

namespace Drupal\panopoly_search\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Condition plugin for testing which Panopoly Search backend is being used.
 *
 * @Condition(
 *   id = "panopoly_search",
 *   label = @Translation("Panopoly Search")
 * )
 */
class PanopolySearchCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * PanopolySearchCondition constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'module' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['module'] = [
      '#type' => 'select',
      '#options' => $this->getOptions(),
      '#empty_value' => '',
      '#title' => $this->t('Search backend'),
      '#default_value' => $this->configuration['module'],
      '#description' => $this->t('The Panopoly Search backend that is enabled.'),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['module'] = $form_state->getValue('module');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Gets the backend options.
   *
   * @return array
   *   An associative array of backend options.
   */
  protected function getOptions() {
    return [
      'none' => $this->t('None'),
      'panopoly_search_db' => $this->t('Database'),
      'panopoly_search_solr' => $this->t('SOLR'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $module = $this->configuration['module'];
    if (empty($module)) {
      // Make sure the value is always TRUE, even if it's going to be negated.
      return $this->isNegated() ? FALSE : TRUE;
    }
    return $this->moduleHandler->moduleExists($module);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $module = $this->configuration['module'];
    $options = $this->getOptions();
    if (!empty($module)) {
      if (!$this->isNegated()) {
        return $this->t('The "@backend" Panopoly Search backend is enabled',
          ['@backend' => $options[$module]]);
      }
      else {
        return $this->t('The "@backend" Panopoly Search backend is disabled',
          ['@backend' => $options[$module]]);
      }
    }
    else {
      return $this->t('Always evaluates to true because no backend is selected');
    }
  }

}
