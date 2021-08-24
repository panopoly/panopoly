<?php

namespace Drupal\panopoly_magic\Controller;

use Drupal\Core\Url;
use Drupal\layout_builder\Controller\ChooseBlockController;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Controller for choosing blocks in layout builder.
 */
class LayoutBuilderChooseBlockController extends ChooseBlockController {

  /**
   * {@inheritDoc}
   *
   * Overrides the parent to show a preview of the block, along with the link.
   */
  protected function getBlockLinks(SectionStorageInterface $section_storage, $delta, $region, array $blocks) {
    $contexts = $this->getAvailableContexts($section_storage);

    $build = [
      '#links' => [],
    ];

    foreach ($blocks as $block_id => $block) {
      $block_preview = [
        '#theme' => 'panopoly_magic_preview',
        '#title' => $block['admin_label'],
        '#attached' => [
          'library' => ['panopoly_magic/preview'],
        ],
      ];

      // @todo Inject this
      $block_preview['preview'] = \Drupal::service('panopoly_magic.block_preview_renderer')->buildBlockPreview($block_id, $contexts);

      $attributes = $this->getAjaxAttributes();
      $attributes['class'][] = 'js-layout-builder-block-link';
      $block_preview['add_link'] = [
        '#type' => 'link',
        '#title' => $this->t('Add<span class="visually-hidden"> @name</span>', ['@name' => $block['admin_label']]),
        '#url' => Url::fromRoute('layout_builder.add_block',
          [
            'section_storage_type' => $section_storage->getStorageType(),
            'section_storage' => $section_storage->getStorageId(),
            'delta' => $delta,
            'region' => $region,
            'plugin_id' => $block_id,
          ]
        ),
        '#attributes' => $attributes,
      ];

      $build[$block_id] = $block_preview;
    }

    return $build;
  }

}
