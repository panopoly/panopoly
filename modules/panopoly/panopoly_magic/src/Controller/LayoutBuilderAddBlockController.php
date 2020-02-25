<?php

namespace Drupal\panopoly_magic\Controller;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
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
   * LayoutBuilderAddBlockController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The uuid generator service.
   */
  public function __construct(FormBuilderInterface $form_builder, UuidInterface $uuid) {
    $this->formBuilder = $form_builder;
    $this->uuid = $uuid;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('uuid')
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
    $component = new SectionComponent($this->uuid->generate(), $region, ['id' => $plugin_id]);
    $section_storage->getSection($delta)->appendComponent($component);

    // Rebuild the layout.
    $response = $this->rebuildLayout($section_storage);

    // Build the panopoly magic update block form and open it in the off canvas.
    $form = $this->formBuilder->getForm(LayoutBuilderUpdateBlockForm::class, $section_storage, $delta, $region, $component->getUuid());
    $response->addCommand(new HtmlCommand('#drupal-off-canvas', $form));
    return $response;
  }

}
