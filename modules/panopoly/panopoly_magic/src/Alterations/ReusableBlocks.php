<?php

namespace Drupal\panopoly_magic\Alterations;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;

/**
 * A service for altering some Layout Builder forms to allow reusable blocks.
 */
class ReusableBlocks {

  use StringTranslationTrait;

  /**
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * ReusableBlocks constructor.
   *
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   */
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, EntityTypeManagerInterface $entity_type_manager, BlockManagerInterface $block_manager, AccountProxyInterface $current_user, TranslationInterface $string_translation) {
    $this->layoutTempstoreRepository = $layout_tempstore_repository;
    $this->entityTypeManager = $entity_type_manager;
    $this->blockManager = $block_manager;
    $this->currentUser = $current_user;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Alters the layout builder add and update block forms.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param string $form_id
   *   The form array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id) {
    $form_args = $form_state->getBuildInfo()['args'];
    /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
    $section_storage = $form_args[0];
    $delta = $form_args[1];

    if ($form_id === 'layout_builder_add_block') {
      /** @var \Drupal\layout_builder\SectionComponent $component */
      $component = $form_state->get('layout_builder__component');
      $uuid = $component->getUuid();
    }
    else {
      $uuid = $form_args[3];
      $component = $section_storage->getSection($delta)->getComponent($uuid);
    }

    // Store some properties for us to access later.
    $form_state->set('panopoly_magic__component', $component);
    $form_state->set('panopoly_magic__section_storage', $section_storage);
    $form_state->set('panopoly_magic__delta', $delta);
    $form_state->set('panopoly_magic__uuid', $uuid);

    /** @var \Drupal\Core\Block\BlockPluginInterface $block */
    $block = $component->getPlugin();

    if ($block->getBaseId() === 'block_content') {
      // Show the block content form here.
      /** @var \Drupal\block_content\Plugin\Derivative\BlockContent[] $block_contents */
      $block_contents = $this->entityTypeManager->getStorage('block_content')->loadByProperties([ 'uuid' => $block->getDerivativeId() ]);
      if (count($block_contents) === 1) {
        $form['messages'] = [
          '#theme' => 'status_messages',
          '#message_list' => [
            'warning' => [$this->t("This block is reusable! Any changes made will be applied globally.")],
          ],
        ];
        $form['block_form'] = [
          '#type' => 'container',
          '#process' => [[static::class, 'processBlockContentForm']],
          '#block' => reset($block_contents),
          '#access' => $this->currentUser->hasPermission('create and edit custom blocks'),
        ];

        $form['#validate'][] = [static::class, 'blockContentValidate'];
        $form['#submit'][] = [static::class, 'blockContentSubmit'];

      }
    }
    elseif ($block->getBaseId() === 'inline_block') {
      /** @var \Drupal\block_content\BlockContentInterface $block_content */
      $block_content = $form['settings']['block_form']['#block'];
      $form['reusable'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Reusable'),
        '#description' => $this->t('Would you like to be able to reuse this block? This option can not be changed after saving.'),
        '#default_value' => $block_content->isReusable(),
      ];
      $form['info'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Admin title'),
        '#description' => $this->t('The title used to find and reuse this block later.'),
        '#states' => [
          'visible' => [
            ':input[name="reusable"]' => [ 'checked' => TRUE ],
          ],
        ],
      ];

      $form['#submit'][] = [static::class, 'staticInlineBlockSubmit'];
    }

    $form['actions']['#weight'] = 100;
  }

  /**
   * Process callback to insert a Custom Block form.
   *
   * @param array $element
   *   The containing element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The containing element, with the Custom Block form inserted.
   */
  public static function processBlockContentForm(array $element, FormStateInterface $form_state) {
    /** @var \Drupal\block_content\BlockContentInterface $block */
    $block = $element['#block'];
    EntityFormDisplay::collectRenderDisplay($block, 'edit')->buildForm($block, $element, $form_state);
    $element['revision_log']['#access'] = FALSE;
    $element['info']['#access'] = FALSE;
    return $element;
  }

  /**
   * Validation callback for editing block_content plugins.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function blockContentValidate($form, FormStateInterface $form_state) {
    $block_form = $form['block_form'];
    /** @var \Drupal\block_content\BlockContentInterface $block_content */
    $block_content = $block_form['#block'];
    $form_display = EntityFormDisplay::collectRenderDisplay($block_content, 'edit');
    $complete_form_state = $form_state instanceof SubformStateInterface ? $form_state->getCompleteFormState() : $form_state;
    $form_display->extractFormValues($block_content, $block_form, $complete_form_state);
    $form_display->validateFormValues($block_content, $block_form, $complete_form_state);
    // @todo Remove when https://www.drupal.org/project/drupal/issues/2948549 is closed.
    $form_state->setTemporaryValue('block_form_parents', $block_form['#parents']);
  }

  /**
   * Submission callback for editing block_content plugins.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function blockContentSubmit($form, FormStateInterface $form_state) {
    // @todo Remove when https://www.drupal.org/project/drupal/issues/2948549 is closed.
    $block_form = NestedArray::getValue($form, $form_state->getTemporaryValue('block_form_parents'));
    /** @var \Drupal\block_content\BlockContentInterface $block_content */
    $block_content = $block_form['#block'];
    $form_display = EntityFormDisplay::collectRenderDisplay($block_content, 'edit');
    $complete_form_state = $form_state instanceof SubformStateInterface ? $form_state->getCompleteFormState() : $form_state;
    $form_display->extractFormValues($block_content, $block_form, $complete_form_state);
    $block_content->setInfo($form_state->getValue('label'));
    $block_content->save();
  }

  /**
   * Static wrapper to call back into this service.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return mixed
   */
  public static function staticInlineBlockSubmit($form, FormStateInterface $form_state) {
    return \Drupal::service('panopoly_magic.alterations.reusable_blocks')->inlineBlockSubmit($form, $form_state);
  }

  /**
   * Submission callback for inline_block plugins.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function inlineBlockSubmit($form, FormStateInterface $form_state) {
    if (!$form_state->getValue('reusable')) {
      return;
    }

    /** @var \Drupal\layout_builder\SectionComponent $component */
    $component = $form_state->get('panopoly_magic__component');
    /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
    $section_storage = $form_state->get('panopoly_magic__section_storage');
    $delta = $form_state->get('panopoly_magic__delta');
    $uuid = $form_state->get('panopoly_magic__uuid');

    /** @var \Drupal\Core\Block\BlockPluginInterface $block */
    $block = $component->getPlugin();

    $configuration = $block->getConfiguration();

    /** @var \Drupal\block_content\BlockContentInterface $block_content */
    $block_content = $form['settings']['block_form']['#block'];
    $block_content->setReusable();
    $block_content->setInfo($form_state->getValue('info'));
    $block_content->save();

    $block = $this->blockManager->createInstance('block_content:' . $block_content->uuid(), [
      'view_mode' => $configuration['view_mode'],
      'label' => $configuration['label'],
    ]);
    $configuration = $block->getConfiguration();

    $section = $section_storage->getSection($delta);
    $section->getComponent($uuid)->setConfiguration($configuration);
    $this->layoutTempstoreRepository->set($section_storage);
  }

}