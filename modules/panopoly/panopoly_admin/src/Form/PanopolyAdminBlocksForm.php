<?php


namespace Drupal\panopoly_admin\Form;


use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PanopolyAdminBlocksForm extends FormBase {

  /**
   * @var \Drupal\Core\Block\BlockManagerInterface
   *
   * The block plugin manager.
   */
  protected $blockManager;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   *
   * The config factory.
   */
  protected $configFactory;

  /**
   * PanopolyAdminLayoutsForm constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(BlockManagerInterface $block_manager, ConfigFactoryInterface $config_factory) {
    $this->blockManager = $block_manager;
    $this->configFactory = $config_factory;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'panopoly_admin_blocks_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('panopoly_admin.settings');
    $blocks = $config->get('blocks');

    $form['blocks'] = [
      '#type' => 'vertical_tabs',
    ];

    $definitions = $this->blockManager->getFilteredDefinitions('layout_builder', NULL, ['panopoly_admin_blocks_form' => TRUE]);
    $definitions = $this->blockManager->getGroupedDefinitions($definitions);
    foreach ($definitions as $group_name => $group) {
      $key = $group_name;
      if (empty($group_name)) {
        $group_name = $this->t('Miscellaneous');
      }
      $form[$key] = [
        '#type' => 'details',
        '#title' => $group_name,
        '#group' => 'blocks',
      ];
      foreach ($group as $block_id => $definition) {
        $form[$key][$block_id] = [
          '#type' => 'checkbox',
          '#title' => isset($definition['admin_label']) ? $definition['admin_label'] : $block_id,
          '#default_value' => isset($blocks[$block_id]) ? $blocks[$block_id] : TRUE,
          '#parents' => ['block_values', $block_id],
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
    $config->set('blocks', $form_state->getValue('block_values'));
    $config->save();
  }

}