<?php

namespace Drupal\panopoly_admin\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to administer the available layouts.
 */
class PanopolyAdminLayoutsForm extends FormBase {

  /**
   * The layout plugin manager.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManagerInterface
   */
  protected $layoutManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * PanopolyAdminLayoutsForm constructor.
   *
   * @param \Drupal\Core\Layout\LayoutPluginManagerInterface $layout_plugin_manager
   *   The layout plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(LayoutPluginManagerInterface $layout_plugin_manager, ConfigFactoryInterface $config_factory) {
    $this->layoutManager = $layout_plugin_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.core.layout'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'panopoly_admin_layouts_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('panopoly_admin.settings');
    $layouts = $config->get('layouts');

    $form['layouts'] = [
      '#type' => 'vertical_tabs',
    ];

    $definitions = $this->layoutManager->getFilteredDefinitions('layout_builder', NULL, ['panopoly_admin_layouts_form' => TRUE]);
    $definitions = $this->layoutManager->getGroupedDefinitions($definitions);
    foreach ($definitions as $group_name => $group) {
      $key = $group_name;
      if (empty($group_name)) {
        $group_name = $this->t('Miscellaneous');
      }
      $form[$key] = [
        '#type' => 'details',
        '#title' => $group_name,
        '#group' => 'layouts',
      ];
      foreach ($group as $layout_id => $definition) {
        $form[$key][$layout_id] = [
          '#type' => 'checkbox',
          '#title' => $definition->getLabel() ?: $layout_id,
          '#default_value' => isset($layouts[$layout_id]) ? $layouts[$layout_id] : TRUE,
          '#parents' => ['layout_values', $layout_id],
        ];
      }
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('panopoly_admin.settings');
    $config->set('layouts', $form_state->getValue('layout_values'));
    $config->save();
  }

}
