<?php

namespace Drupal\panopoly_magic\Controller;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\layout_builder\Context\LayoutBuilderContextTrait;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\LayoutBuilderHighlightTrait;
use Drupal\layout_builder\SectionComponent;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\panopoly_magic\Form\LayoutBuilderUpdateBlockForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LayoutBuilderAddBlockController extends ControllerBase {

  use AjaxHelperTrait;
  use LayoutRebuildTrait;
  use LayoutBuilderHighlightTrait;
  use LayoutBuilderContextTrait;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The Uuid generator service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * The context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The block plugin manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockPluginManager;

  /**
   * LayoutBuilderAddBlockController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The uuid generator service.
   */
  public function __construct(FormBuilderInterface $form_builder, UuidInterface $uuid, ContextHandlerInterface $context_handler, BlockManagerInterface $block_plugin_manager) {
    $this->formBuilder = $form_builder;
    $this->uuid = $uuid;
    $this->contextHandler = $context_handler;
    $this->blockPluginManager = $block_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('uuid'),
      $container->get('context.handler'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Adds the new block to layout builder and opens the configuration form.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to add the block to.
   * @param string $region
   *   The region for the section.
   * @param string $plugin_id
   *   The plugin ID of the layout to add.
   *
   * @return \Symfony\Component\HttpFoundation\Response|array
   *   The controller response.
   */
  public function addBlock(SectionStorageInterface $section_storage, $delta, $region, $plugin_id) {
    // Create a new component and add it to the section storage.
    $plugin_configuration = ['id' => $plugin_id];

    /** @var \Drupal\Core\Block\BlockPluginInterface $plugin */
    $plugin = $this->blockPluginManager->createInstance($plugin_id, ['id' => $plugin_id]);
    $contexts = $this->getAvailableContexts($section_storage);

    // Map contexts for plugins that need them.
    if ($contexts && $plugin instanceof ContextAwarePluginInterface) {
      $context_mapping = [];
      foreach ($plugin->getContextDefinitions() as $context_slot => $definition) {
        // If the context is required, we have to give it something to start with.
        if ($definition->isRequired()) {
          $valid_contexts = $this->contextHandler->getMatchingContexts($contexts, $definition);

          // Get the first context id, and assign that to the slot. The user can
          // change it later.
          reset($valid_contexts);
          $context_id = key($valid_contexts);

          $context_mapping[$context_slot] = $context_id;
        }
      }

      $plugin->setContextMapping($context_mapping);
      $plugin_configuration = $plugin->getConfiguration();
    }

    $component = new SectionComponent($this->uuid->generate(), $region, $plugin_configuration);
    $section_storage->getSection($delta)->appendComponent($component);

    // Rebuild the layout.
    $response = $this->rebuildLayout($section_storage);

    // Build the panopoly magic update block form and open it in the off canvas.
    $form = $this->formBuilder->getForm(LayoutBuilderUpdateBlockForm::class, $section_storage, $delta, $region, $component->getUuid());
    $response->addCommand(new HtmlCommand('#drupal-off-canvas', $form));
    return $response;
  }

}
