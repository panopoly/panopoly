<?php

namespace Drupal\panopoly_magic;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * A service for rendering block previews.
 */
class BlockPreviewRenderer {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * BlockPreviewRenderer constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The context handler.
   */
  public function __construct(BlockManagerInterface $block_manager, ContextHandlerInterface $context_handler) {
    $this->blockManager = $block_manager;
    $this->contextHandler = $context_handler;
  }

  /**
   * Builds a render array representing a preview of the block.
   *
   * @param string $block_id
   *   The block plugin id.
   * @param \Drupal\Core\Plugin\Context\ContextInterface[] $contexts
   *   The available contexts.
   *
   * @return array
   *   A render array of the block preview.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function buildBlockPreview($block_id, array $contexts = []) {
    $block_definition = $this->blockManager->getDefinition($block_id);

    /** @var \Drupal\Core\Block\BlockPluginInterface $block_plugin */
    $block_plugin = $this->blockManager->createInstance($block_id);

    // Map contexts automatically in preparation for rendering.
    if ($block_plugin instanceof ContextAwarePluginInterface) {
      $mapping = $block_plugin->getContextMapping();

      foreach ($block_plugin->getContextDefinitions() as $context_slot => $definition) {
        // If it's already mapped, skip.
        if (isset($mapping[$context_slot])) {
          continue;
        }

        // If the context is required, assign the first valid context.
        if ($definition->isRequired()) {
          $valid_contexts = $this->contextHandler->getMatchingContexts($contexts, $definition);
          if (count($valid_contexts) > 0) {
            $valid_context_ids = array_keys($valid_contexts);
            $mapping[$context_slot] = $valid_context_ids[0];
          }
        }
      }

      try {
        $this->contextHandler->applyContextMapping($block_plugin, $contexts, $mapping);
      }
      catch (ContextException $e) {
        return ['#markup' => $this->t("Missing required context")];
      }
    }

    if (!empty($block_definition['preview_settings']) && is_array($block_definition['preview_settings'])) {
      if ($block_plugin->getBaseId() === 'inline_block') {
        $block_definition['preview_settings']['type'] = $block_plugin->getDerivativeId();
        $block_entity = BlockContent::create($block_definition['preview_settings']);
        $block_plugin->setConfiguration(['block_serialized' => serialize($block_entity)]);
      }
      else {
        $block_plugin->setConfiguration(array_merge($block_plugin->getConfiguration(), $block_definition['preview_settings']));
      }
    }

    try {
      // First, favor callbacks added to the definition, because this is how a
      // third-party module would add a custom preview to a block, possibly
      // overriding its original preview.
      if (!empty($block_definition['preview_callback']) && is_callable($block_definition['preview_callback'])) {
        return call_user_func($block_definition['preview_callback'], $block_plugin);
      }

      // Second, favor images, because these are also intended for use by
      // third-party modules (the block itself would use BlockPreviewInterface
      // and return the image render array itself).
      if (!empty($block_definition['preview_image'])) {
        $preview_image = [
          '#theme' => 'image',
          '#uri' => $block_definition['preview_image'],
        ];
        if (!empty($block_definition['preview_alt'])) {
          $preview_image['#alt'] = $block_definition['preview_alt'];
        }
        return $preview_image;
      }

      // Then, allow the block plugin to preview its own preview.
      if ($block_plugin instanceof BlockPreviewInterface) {
        return $block_plugin->buildPreview();
      }

      // Finally, we'll render the block the normal way as the last resort.
      return $block_plugin->build();
    }
    catch (ContextException $e) {
      return ['#markup' => $this->t("Missing required context")];
    }
  }

}