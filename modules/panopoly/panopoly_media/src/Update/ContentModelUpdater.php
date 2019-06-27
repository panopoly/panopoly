<?php

namespace Drupal\panopoly_media\Update;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\embed\Entity\EmbedButton;
use Drupal\entity_browser\Entity\EntityBrowser;
use Drupal\field\Entity\FieldConfig;
use Drupal\media\Entity\Media;

/**
 * Applies changes to media content model from schema versions 8204 to 8205.
 */
class ContentModelUpdater {

  /**
   * Config file storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configFileStorage;

  /**
   * Active config storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Performs changes necessary before mass update of media entities.
   */
  public function init() {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = \Drupal::service('module_installer');
    $module_installer->install(['entity_browser_entity_form']);
    $this->installConfig();
  }

  /**
   * Final cleanup.
   */
  public function cleanup() {
    // Set the YouTube nocookie config per the video_embed_field config.
    $vefConfig = $this->getConfigStorage()->read('video_embed_field.settings');
    if (isset($vefConfig['privacy_mode'])) {
      $config = $this->getConfigStorage()->read('panopoly_media.settings');
      $config['youtube_nocookie'] = $vefConfig['privacy_mode'];
      $this->getConfigStorage()->write('panopoly_media.settings', $config);
    }
  }

  /**
   * Install config.
   */
  public function installConfig() {
    $this->installBaseConfig();
    $this->installBundleConfig();
    $this->updateEntityBrowsers();
    $this->updateEmbedButton();
  }

  /**
   * Batch execution callback for converting media entities to new types.
   *
   * @param array|\ArrayAccess $context
   *   The batch operation context.
   *
   * @return float
   *   Indicates the progress of the batch.
   */
  public function convertMedia(&$context) {
    // First time through, populate the media IDs.
    if (!isset($context['sandbox']['media_ids'])) {
      $context['sandbox']['media_ids'] = \Drupal::database()
        ->select('media', 'm')
        ->fields('m', ['mid'])
        ->execute()
        ->fetchCol();
      $context['sandbox']['media_count'] = count($context['sandbox']['media_ids']);
    }

    if (!$context['sandbox']['media_count']) {
      return 1;
    }

    $count = 0;
    while (($count < 25) && ($id = array_shift($context['sandbox']['media_ids']))) {
      $this->convertMediaEntity($id);
    }

    return 1 - (count($context['sandbox']['media_ids']) / $context['sandbox']['media_count']);
  }

  /**
   * Batch execution callback for converting entities reference fields.
   */
  public function convertFields() {
    // Find entity reference fields.
    /** @var \Drupal\field\Entity\FieldConfig[] $fields */
    $fields = FieldConfig::loadMultiple();
    $fields = array_filter($fields, function ($fieldConfig) {
      /** @var \Drupal\field\Entity\FieldConfig $fieldConfig */
      return $fieldConfig->getType() == 'entity_reference';
    });

    foreach ($fields as $fieldConfig) {
      $settings = $fieldConfig->getSettings();
      if ($settings['handler'] == 'default:media') {
        foreach ($settings['handler_settings']['target_bundles'] as $bundle) {
          switch ($bundle) {
            case 'file':
              $settings['handler_settings']['target_bundles']['panopoly_media_file'] = 'panopoly_media_file';
              unset($settings['handler_settings']['target_bundles'][$bundle]);
              break;

            case 'image':
              $settings['handler_settings']['target_bundles']['panopoly_media_image'] = 'panopoly_media_image';
              unset($settings['handler_settings']['target_bundles'][$bundle]);
              break;

            case 'video':
              $settings['handler_settings']['target_bundles']['panopoly_media_remote_video'] = 'panopoly_media_remote_video';
              unset($settings['handler_settings']['target_bundles'][$bundle]);
              break;
          }
        }

        if (isset($settings['handler_settings']['auto_create_bundle'])) {
          switch ($settings['handler_settings']['auto_create_bundle']) {
            case 'file':
              $settings['handler_settings']['auto_create_bundle'] = 'panopoly_media_file';
              break;

            case 'image':
              $settings['handler_settings']['auto_create_bundle'] = 'panopoly_media_image';
              break;

            case 'video':
              $settings['handler_settings']['auto_create_bundle'] = 'panopoly_media_remote_video';
              break;
          }
        }
        ksort($settings);
        $fieldConfig->setSettings($settings);
        $fieldConfig->save();
      }
    }
  }

  /**
   * Installs base config needed by other entity config.
   */
  protected function installBaseConfig() {
    $this->getConfigStorage()->write('panopoly_media.settings', $this->getConfigFileStorage()->read('panopoly_media.settings'));
    $this->installAllOfType('field.storage.media');
    $this->installAllOfType('image.style');
  }

  /**
   * Installs config pertinent to media entity bundles.
   */
  protected function installBundleConfig() {
    $this->installMediaTypes();
    $this->installFields();
    $this->installFormDisplays();
    $this->installViewDisplays();
  }

  /**
   * Installs media type config.
   */
  protected function installMediaTypes() {
    $this->installAllOfType('media.type');
  }

  /**
   * Installs fields on media types.
   */
  protected function installFields() {
    // File, copy existing fields.
    $existingFields = \Drupal::configFactory()->listAll('field.field.media.file.');
    foreach ($existingFields as $configId) {
      if (!$this->getConfigStorage()->exists($configId)) {
        $config = $this->getConfigStorage()->read($configId);
        unset($config['uuid']);
        $config['id'] = 'media.panopoly_media_file.' . $config['field_name'];
        $config['bundle'] = 'panopoly_media_file';
        $config['translatable'] = TRUE;
        $configId = 'field.field.' . $config['id'];
        $this->createConfigEntity($configId, $config)->save();
      }
    }

    // Image, copy existing fields.
    $existingFields = \Drupal::configFactory()->listAll('field.field.media.image.');
    foreach ($existingFields as $configId) {
      if (!$this->getConfigStorage()->exists($configId)) {
        $config = $this->getConfigStorage()->read($configId);
        unset($config['uuid']);
        $config['id'] = 'media.panopoly_media_image.' . $config['field_name'];
        $config['bundle'] = 'panopoly_media_image';
        $config['translatable'] = TRUE;
        $configId = 'field.field.' . $config['id'];
        $this->createConfigEntity($configId, $config)->save();
      }
    }

    // Remote video, copy existing fields.
    $existingFields = \Drupal::configFactory()->listAll('field.field.media.video.');
    foreach ($existingFields as $configId) {
      if (!$this->getConfigStorage()->exists($configId)) {
        $config = $this->getConfigStorage()->read($configId);
        unset($config['uuid']);

        // Skip video_embed, will create oembed field instead.
        if ($configId == 'field.field.media.video.field_media_video_embed_field') {
          continue;
        }

        $config['id'] = 'media.panopoly_media_remote_video.' . $config['field_name'];
        $config['bundle'] = 'panopoly_media_remote_video';
        $config['translatable'] = TRUE;
        $configId = 'field.field.' . $config['id'];
        $this->createConfigEntity($configId, $config)->save();
      }
    }

    $this->installAllOfType('field.field.media');
  }

  /**
   * Install form displays.
   */
  protected function installFormDisplays() {
    // File, copy form display mode.
    $existingModes = \Drupal::configFactory()->listAll('core.entity_form_display.media.file.');
    foreach ($existingModes as $configId) {
      $config = $this->getConfigStorage()->read($configId);
      unset($config['uuid']);
      $config['id'] = 'media.panopoly_media_file.' . $config['mode'];
      $config['bundle'] = 'panopoly_media_file';
      $configId = 'core.entity_form_display.' . $config['id'];
      $this->createConfigEntity($configId, $config)->save();
      $this->updateFormDisplay($config['id']);
    }

    // Image, copy form display mode.
    $existingModes = \Drupal::configFactory()->listAll('core.entity_form_display.media.image.');
    foreach ($existingModes as $configId) {
      $config = $this->getConfigStorage()->read($configId);
      unset($config['uuid']);
      $config['id'] = 'media.panopoly_media_image.' . $config['mode'];
      $config['bundle'] = 'panopoly_media_image';
      $configId = 'core.entity_form_display.' . $config['id'];
      $this->createConfigEntity($configId, $config)->save();
      $this->updateFormDisplay($config['id']);
    }

    // Remote video, copy form display mode.
    $existingModes = \Drupal::configFactory()->listAll('core.entity_form_display.media.video.');
    foreach ($existingModes as $configId) {
      $config = $this->getConfigStorage()->read($configId);
      unset($config['uuid']);
      $config = $this->swapEmbedFieldDisplay($config, 'form');
      $config['id'] = 'media.panopoly_media_remote_video.' . $config['mode'];
      $config['bundle'] = 'panopoly_media_remote_video';
      $configId = 'core.entity_form_display.' . $config['id'];
      $this->createConfigEntity($configId, $config)->save();
      $this->updateFormDisplay($config['id']);
    }

    $this->installAllOfType('core.entity_form_display.media');

    // Set paths for new and old config.
    $this->hideEntityFormPaths('panopoly_media_file');
    $this->hideEntityFormPaths('panopoly_media_image');
    $this->hideEntityFormPaths('panopoly_media_remote_video');
  }

  /**
   * Update configuration for entity form displays.
   *
   * @param string $id
   *   The form display ID.
   */
  protected function updateFormDisplay($id) {
    $formDisplay = EntityFormDisplay::load($id);
    switch ($id) {
      case 'media.panopoly_media_file.default':
        $content = $formDisplay->get('content');
        $hidden = $formDisplay->get('hidden');
        $this->changeNestedValue($content, 'field_media_file::weight', 26, 0);
        $this->changeNestedValue($content, 'field_panopoly_media_description::weight', 27, 1);
        $this->changeNestedValue($content, 'field_panopoly_media_tags::weight', 28, 2);
        $this->unsetNestedValue($content, 'name');
        $this->setNestedValue($hidden, 'name', TRUE);
        $formDisplay->set('content', $content);
        $formDisplay->set('hidden', $hidden);
        $formDisplay->save();
        break;

      case 'media.panopoly_media_file.entity_browser':
        $content = $formDisplay->get('content');
        $hidden = $formDisplay->get('hidden');
        $formDisplay->set('content', $content);
        $formDisplay->set('hidden', $hidden);
        $formDisplay->save();
        break;

      case 'media.panopoly_media_image.default':
        $content = $formDisplay->get('content');
        $hidden = $formDisplay->get('hidden');
        $this->changeNestedValue($content, 'field_media_image::weight', 26, 0);
        $this->changeNestedValue($content, 'field_panopoly_media_description::weight', 27, 1);
        $this->changeNestedValue($content, 'field_panopoly_media_tags::weight', 28, 2);
        $this->unsetNestedValue($content, 'name');
        $this->setNestedValue($hidden, 'name', TRUE);
        $formDisplay->set('content', $content);
        $formDisplay->set('hidden', $hidden);
        $formDisplay->save();
        break;

      case 'media.panopoly_media_image.entity_browser':
        $content = $formDisplay->get('content');
        $hidden = $formDisplay->get('hidden');
        $this->changeNestedValue($content, 'field_media_image::weight', 1, 0);
        $this->changeNestedValue($content, 'field_panopoly_media_description::weight', 2, 1);
        $this->changeNestedValue($content, 'field_panopoly_media_tags::weight', 3, 2);
        $this->unsetNestedValue($content, 'name');
        $this->setNestedValue($hidden, 'name', TRUE);
        $formDisplay->set('content', $content);
        $formDisplay->set('hidden', $hidden);
        $formDisplay->save();
        break;

      case 'media.panopoly_media_remote_video.default':
        $content = $formDisplay->get('content');
        $hidden = $formDisplay->get('hidden');
        $this->changeNestedValue($content, 'field_media_oembed_video::weight', 26, 0);
        $this->changeNestedValue($content, 'field_panopoly_media_description::weight', 27, 1);
        $this->changeNestedValue($content, 'field_panopoly_media_tags::weight', 28, 2);
        $this->unsetNestedValue($content, 'name');
        $this->setNestedValue($hidden, 'name', TRUE);
        $formDisplay->set('content', $content);
        $formDisplay->set('hidden', $hidden);
        $formDisplay->save();
        break;

      case 'media.panopoly_media_remote_video.entity_browser':
        $content = $formDisplay->get('content');
        $hidden = $formDisplay->get('hidden');
        $this->changeNestedValue($content, 'field_media_oembed_video::weight', 1, 0);
        $this->changeNestedValue($content, 'field_panopoly_media_description', NULL, [
          'region' => 'content',
          'settings' => [
            'placeholder' => '',
            'rows' => 9,
            'summary_rows' => 3,
          ],
          'third_party_settings' => [],
          'type' => 'text_textarea_with_summary',
          'weight' => 1,
        ]);
        $this->unsetNestedValue($hidden, 'field_panopoly_media_description');
        $content['field_panopoly_media_tags'] = [
          'region' => 'content',
          'settings' => [
            'match_operator' => 'CONTAINS',
            'placeholder' => '',
            'size' => 60,
          ],
          'third_party_settings' => [],
          'type' => 'entity_reference_autocomplete_tags',
          'weight' => 2,
        ];
        $this->unsetNestedValue($hidden, 'field_panopoly_media_tags');
        $this->unsetNestedValue($content, 'name');
        $this->setNestedValue($hidden, 'name', TRUE);
        $formDisplay->set('content', $content);
        $formDisplay->set('hidden', $hidden);
        $formDisplay->save();
        break;
    }
  }

  /**
   * Hides the path element for the entity forms of a given type.
   *
   * @param string $type
   *   The media type (bundle).
   */
  protected function hideEntityFormPaths($type) {
    $ids = \Drupal::configFactory()->listAll('core.entity_form_display.media.' . $type . '.');
    foreach ($ids as $id) {
      $id = explode('.', $id, 3);
      $this->hideEntityFormPath($id[2]);
    }
  }

  /**
   * Hides the path element for the entity forms.
   *
   * @param string $id
   *   The form display ID.
   */
  protected function hideEntityFormPath($id) {
    if (!$formDisplay = EntityFormDisplay::load($id)) {
      return;
    }

    $content = $formDisplay->get('content');
    $hidden = $formDisplay->get('hidden');

    if (isset($content['path'])) {
      unset($content['path']);
    }

    if ($this->getModuleHandler()->moduleExists('path')) {
      $hidden['path'] = TRUE;
    }
    else {
      if (isset($hidden['path'])) {
        unset($hidden['path']);
      }
    }

    $formDisplay->set('content', $content);
    $formDisplay->set('hidden', $hidden);
    $formDisplay->save();
  }

  /**
   * Install view displays.
   */
  protected function installViewDisplays() {
    // File, copy view mode displays.
    $existingModes = \Drupal::configFactory()->listAll('core.entity_view_display.media.file.');
    foreach ($existingModes as $configId) {
      $config = $this->getConfigStorage()->read($configId);
      unset($config['uuid']);
      $config['id'] = 'media.panopoly_media_file.' . $config['mode'];
      $config['bundle'] = 'panopoly_media_file';
      $configId = 'core.entity_view_display.' . $config['id'];
      $this->createConfigEntity($configId, $config)->save();
      $this->updateViewDisplay($config['id']);
    }

    // Image, copy view mode displays.
    $existingModes = \Drupal::configFactory()->listAll('core.entity_view_display.media.image.');
    foreach ($existingModes as $configId) {
      $config = $this->getConfigStorage()->read($configId);
      unset($config['uuid']);
      $config['id'] = 'media.panopoly_media_image.' . $config['mode'];
      $config['bundle'] = 'panopoly_media_image';
      $configId = 'core.entity_view_display.' . $config['id'];
      $this->createConfigEntity($configId, $config)->save();
      $this->updateViewDisplay($config['id']);
    }

    // Video, copy view mode displays.
    $existingModes = \Drupal::configFactory()->listAll('core.entity_view_display.media.video.');
    foreach ($existingModes as $configId) {
      $config = $this->getConfigStorage()->read($configId);
      unset($config['uuid']);
      $config = $this->swapEmbedFieldDisplay($config);
      $config['id'] = 'media.panopoly_media_remote_video.' . $config['mode'];
      $config['bundle'] = 'panopoly_media_remote_video';
      $configId = 'core.entity_view_display.' . $config['id'];
      $this->createConfigEntity($configId, $config)->save();
      $this->updateViewDisplay($config['id']);
    }

    $this->installAllOfType('core.entity_view_display.media');
  }

  /**
   * Update configuration for entity view displays.
   *
   * @param string $id
   *   The view display ID.
   */
  protected function updateViewDisplay($id) {
    $viewDisplay = EntityViewDisplay::load($id);
    switch ($id) {
      case 'media.panopoly_media_file.default':
        $content = $viewDisplay->get('content');
        $hidden = $viewDisplay->get('hidden');
        $this->changeNestedValue($content, 'field_media_file::label', 'hidden', 'visually_hidden');
        $this->changeNestedValue($content, 'field_media_file::weight', 1, 0);
        $this->changeNestedValue($content, 'field_panopoly_media_description', NULL, [
          'label' => 'hidden',
          'region' => 'content',
          'settings' => [],
          'third_party_settings' => [],
          'type' => 'text_default',
          'weight' => 1,
        ]);
        $this->unsetNestedValue($hidden, 'field_panopoly_media_description');
        $this->changeNestedValue($content, 'field_panopoly_media_tags', NULL, [
          'label' => 'inline',
          'region' => 'content',
          'settings' => [
            'link' => TRUE,
          ],
          'third_party_settings' => [],
          'type' => 'entity_reference_label',
          'weight' => 2,
        ]);
        $this->unsetNestedValue($hidden, 'field_panopoly_media_tags');
        $this->unsetNestedValue($content, 'name');
        $this->setNestedValue($hidden, 'name', TRUE);
        $viewDisplay->set('content', $content);
        $viewDisplay->set('hidden', $hidden);
        $viewDisplay->save();
        break;

      case 'media.panopoly_media_file.teaser':
        $content = $viewDisplay->get('content');
        $hidden = $viewDisplay->get('hidden');
        $this->unsetNestedValue($content, 'field_media_file');
        $this->setNestedValue($hidden, 'field_media_file', TRUE);
        $this->unsetNestedValue($content, 'field_panopoly_media_description');
        $this->setNestedValue($hidden, 'field_panopoly_media_description', TRUE);
        $this->unsetNestedValue($content, 'field_panopoly_media_tags');
        $this->setNestedValue($hidden, 'field_panopoly_media_tags', TRUE);
        $this->changeNestedValue($content, 'name::weight', 0, 1);
        $this->changeNestedValue($content, 'name::label', 'above', 'hidden');
        $this->changeNestedValue($content, 'name::settings::link_to_entity', FALSE, TRUE);
        $this->changeNestedValue($content, 'thumbnail', NULL, [
          'label' => 'hidden',
          'region' => 'content',
          'settings' => [
            'image_link' => 'file',
            'image_style' => 'medium',
          ],
          'third_party_settings' => [],
          'type' => 'image',
          'weight' => 0,
        ]);
        $this->unsetNestedValue($hidden, 'thumbnail');
        $viewDisplay->set('content', $content);
        $viewDisplay->set('hidden', $hidden);
        $viewDisplay->save();
        break;

      case 'media.panopoly_media_image.default':
        $content = $viewDisplay->get('content');
        $hidden = $viewDisplay->get('hidden');
        $this->setNestedValue($hidden, 'name', TRUE);
        $this->unsetNestedValue($content, 'name');
        $this->changeNestedValue($content, 'field_media_image::settings::image_style', 'medium', '');
        $this->changeNestedValue($content, 'field_media_image::weight', 1, 0);
        $this->changeNestedValue($content, 'field_panopoly_media_description', NULL, [
          'label' => 'hidden',
          'region' => 'content',
          'settings' => [],
          'third_party_settings' => [],
          'type' => 'text_default',
          'weight' => 1,
        ]);
        $this->unsetNestedValue($hidden, 'field_panopoly_media_description');
        $this->changeNestedValue($content, 'field_panopoly_media_tags', NULL, [
          'label' => 'inline',
          'region' => 'content',
          'settings' => [
            'link' => TRUE,
          ],
          'third_party_settings' => [],
          'type' => 'entity_reference_label',
          'weight' => 2,
        ]);
        $this->unsetNestedValue($hidden, 'field_panopoly_media_tags');
        $viewDisplay->set('content', $content);
        $viewDisplay->set('hidden', $hidden);
        $viewDisplay->save();
        break;

      case 'media.panopoly_media_image.embed_large':
        $content = $viewDisplay->get('content');
        $hidden = $viewDisplay->get('hidden');
        $this->changeNestedValue($content, 'field_media_image::weight', 1, 0);
        $viewDisplay->set('content', $content);
        $viewDisplay->set('hidden', $hidden);
        $viewDisplay->save();
        break;

      case 'media.panopoly_media_image.teaser':
        $content = $viewDisplay->get('content');
        $hidden = $viewDisplay->get('hidden');
        $this->unsetNestedValue($content, 'field_media_image');
        $this->setNestedValue($hidden, 'field_media_image', TRUE);
        $this->unsetNestedValue($content, 'field_panopoly_media_description');
        $this->setNestedValue($hidden, 'field_panopoly_media_description', TRUE);
        $this->unsetNestedValue($content, 'field_panopoly_media_tags');
        $this->setNestedValue($hidden, 'field_panopoly_media_tags', TRUE);
        $this->changeNestedValue($content, 'name::settings::link_to_entity', FALSE, TRUE);
        $this->changeNestedValue($content, 'name::weight', 0, 1);
        $this->changeNestedValue($content, 'thumbnail', NULL, [
          'label' => 'hidden',
          'region' => 'content',
          'settings' => [
            'image_link' => '',
            'image_style' => 'panopoly_media_entity_browser_thumbnail',
          ],
          'third_party_settings' => [],
          'type' => 'image',
          'weight' => 0,
        ]);
        $this->unsetNestedValue($hidden, 'thumbnail');
        $viewDisplay->set('content', $content);
        $viewDisplay->set('hidden', $hidden);
        $viewDisplay->save();
        break;

      case 'media.panopoly_media_remote_video.default':
        $content = $viewDisplay->get('content');
        $hidden = $viewDisplay->get('hidden');
        $this->changeNestedValue($content, 'field_panopoly_media_description', NULL, [
          'label' => 'hidden',
          'region' => 'content',
          'settings' => [],
          'third_party_settings' => [],
          'type' => 'text_default',
          'weight' => 1,
        ]);
        $this->unsetNestedValue($hidden, 'field_panopoly_media_description');
        $this->changeNestedValue($content, 'field_panopoly_media_tags', NULL, [
          'label' => 'inline',
          'region' => 'content',
          'settings' => [
            'link' => TRUE,
          ],
          'third_party_settings' => [],
          'type' => 'entity_reference_label',
          'weight' => 2,
        ]);
        $this->unsetNestedValue($hidden, 'field_panopoly_media_tags');
        $viewDisplay->set('content', $content);
        $viewDisplay->set('hidden', $hidden);
        $viewDisplay->save();
        break;

      case 'media.panopoly_media_remote_video.full':
        $content = $viewDisplay->get('content');
        $hidden = $viewDisplay->get('hidden');
        $this->changeNestedValue($content, 'name::weight', -5, 0);
        $viewDisplay->set('content', $content);
        $viewDisplay->set('hidden', $hidden);
        $viewDisplay->save();
        break;

      case 'media.panopoly_media_remote_video.teaser':
        $content = $viewDisplay->get('content');
        $hidden = $viewDisplay->get('hidden');
        $this->changeNestedValue($content, 'name::settings::link_to_entity', FALSE, TRUE);
        $this->changeNestedValue($content, 'name::weight', -5, 1);
        $this->changeNestedValue($content, 'thumbnail', NULL, [
          'label' => 'hidden',
          'region' => 'content',
          'settings' => [
            'image_link' => '',
            'image_style' => 'panopoly_media_entity_browser_thumbnail',
          ],
          'third_party_settings' => [],
          'type' => 'image',
          'weight' => 0,
        ]);
        $this->unsetNestedValue($hidden, 'thumbnail');
        $this->unsetNestedValue($content, 'field_media_oembed_video');
        $this->setNestedValue($hidden, 'field_media_oembed_video', TRUE);
        $this->unsetNestedValue($content, 'field_panopoly_media_description');
        $this->setNestedValue($hidden, 'field_panopoly_media_description', TRUE);
        $this->unsetNestedValue($content, 'field_panopoly_media_tags');
        $this->setNestedValue($hidden, 'field_panopoly_media_tags', TRUE);
        $viewDisplay->set('content', $content);
        $viewDisplay->set('hidden', $hidden);
        $viewDisplay->save();
        break;
    }
  }

  /**
   * Swap embed field in form/view mode display config.
   *
   * @param array $config
   *   Configuration array.
   * @param string $type
   *   The type of display config, 'form' or 'view'. Defaults to 'view'.
   *
   * @return array
   *   The display config with video embed field swapped-out for oembed field.
   */
  protected function swapEmbedFieldDisplay(array $config, $type = 'view') {
    // Field displayed in content.
    if (array_key_exists('field_media_video_embed_field', $config['content'])) {
      switch ($type) {
        case 'form':
          $config['content']['field_media_oembed_video'] = $this->adaptVideoFormDisplayConfig($config['content']['field_media_video_embed_field']);
          break;

        default:
          $config['content']['field_media_oembed_video'] = $this->adaptVideoViewDisplayConfig($config['content']['field_media_video_embed_field']);
      }
      unset($config['content']['field_media_video_embed_field']);
      ksort($config['content']);
    }

    // Hidden field.
    if (array_key_exists('field_media_video_embed_field', $config['hidden'])) {
      $config['hidden']['field_media_oembed_video'] = $config['hidden']['field_media_video_embed_field'];
      unset($config['hidden']['field_media_video_embed_field']);
      ksort($config['hidden']);
    }

    return $config;
  }

  /**
   * Adapts a form display config for video embed fields.
   *
   * @param array $config
   *   The form display config.
   *
   * @return array
   *   The adapted form display config.
   */
  protected function adaptVideoFormDisplayConfig(array $config) {
    $field = [
      'type' => 'oembed_textfield',
      'weight' => 0,
      'settings' => [
        'size' => 60,
        'placeholder' => '',
      ],
      'third_party_settings' => [],
      'region' => 'content',
    ];

    if (isset($config['weight'])) {
      $field['weight'] = $config['weight'];
    }

    if (isset($config['region'])) {
      $field['region'] = $config['region'];
    }

    return $field;
  }

  /**
   * Adapts a view display config for video embed fields.
   *
   * @param array $config
   *   The view display config.
   *
   * @return array
   *   The adapted view display config.
   */
  protected function adaptVideoViewDisplayConfig(array $config) {
    $field = [
      'type' => 'oembed',
      'weight' => 0,
      'label' => 'hidden',
      'settings' => [
        'max_width' => 0,
        'max_height' => 0,
      ],
      'third_party_settings' => [],
      'region' => 'content',
    ];

    if (isset($config['type']) && ($config['type'] == 'video_embed_field_video')) {
      $field['settings']['max_height'] = $config['settings']['height'];
      $field['settings']['max_width'] = $config['settings']['width'];
    }

    if (isset($config['weight'])) {
      $field['weight'] = $config['weight'];
    }

    if (isset($config['label'])) {
      $field['label'] = $config['label'];
    }

    if (isset($config['region'])) {
      $field['region'] = $config['region'];
    }

    return $field;
  }

  /**
   * Switch entity browsers to utilize new types.
   */
  protected function updateEntityBrowsers() {
    if ($b = EntityBrowser::load('panopoly_media_field_media_browser')) {
      $displayConfiguration = $b->get('display_configuration');
      $displayConfiguration['height'] = '';
      $displayConfiguration['width'] = '';
      $displayConfiguration['link_text'] = 'Browse media';
      $b->set('display_configuration', $displayConfiguration);

      /** @var \Drupal\entity_browser\WidgetsLazyPluginCollection $widgets */
      $widgets = $b->getWidgets();
      if ($widgets->has('a36a243c-e298-4b28-9eb2-4a9976879176')) {
        /** @var \Drupal\entity_browser\WidgetInterface $widget */
        $widget = $widgets->get('a36a243c-e298-4b28-9eb2-4a9976879176');
        $widget->setWeight(-7);
        $config = $widget->getConfiguration();
        $config['settings']['media_type'] = 'panopoly_media_image';
        $widget->setConfiguration($config);
      }

      if ($widgets->has('ee8606e5-1b38-4c5d-9cc3-e71ae053cb4e')) {
        /** @var \Drupal\entity_browser\WidgetInterface $widget */
        $widget = $widgets->get('ee8606e5-1b38-4c5d-9cc3-e71ae053cb4e');
        $widget->setWeight(-8);
        $config = $widget->getConfiguration();
        $config['settings']['media_type'] = 'panopoly_media_file';
        $widget->setConfiguration($config);
      }

      $config = [
        'id' => 'entity_form',
        'label' => 'Add remote video',
        'settings' => [
          'bundle' => 'panopoly_media_remote_video',
          'entity_type' => 'media',
          'form_mode' => 'entity_browser',
          'submit_text' => 'Select',
        ],
        'uuid' => '15474089-1e4d-48bb-8917-4af94990132a',
        'weight' => 5,
      ];
      $widgets->addInstanceId('15474089-1e4d-48bb-8917-4af94990132a', $config);

      $b->save();
    }

    if ($b = EntityBrowser::load('panopoly_media_wysiwyg_media_browser')) {
      /** @var \Drupal\entity_browser\WidgetsLazyPluginCollection $widgets */
      $widgets = $b->getWidgets();
      if ($widgets->has('5864d273-3a0b-4019-b5e2-257bb6faa387')) {
        /** @var \Drupal\entity_browser\WidgetInterface $widget */
        $widget = $widgets->get('5864d273-3a0b-4019-b5e2-257bb6faa387');
        $widget->setWeight(-7);
        $config = $widget->getConfiguration();
        $config['settings']['media_type'] = 'panopoly_media_image';
        $widget->setConfiguration($config);
      }

      if ($widgets->has('a9609bc6-0d7d-47ca-84f1-62e76c37372a')) {
        /** @var \Drupal\entity_browser\WidgetInterface $widget */
        $widget = $widgets->get('a9609bc6-0d7d-47ca-84f1-62e76c37372a');
        $widget->setWeight(-9);
        $config = $widget->getConfiguration();
        $config['settings']['media_type'] = 'panopoly_media_file';
        $widget->setConfiguration($config);
      }

      $config = [
        'id' => 'entity_form',
        'label' => 'Add remote video',
        'settings' => [
          'bundle' => 'panopoly_media_remote_video',
          'entity_type' => 'media',
          'form_mode' => 'entity_browser',
          'submit_text' => 'Select',
        ],
        'uuid' => 'aed962bf-6834-4b6f-b9d7-e41e530b470c',
        'weight' => -5,
      ];
      $widgets->addInstanceId('aed962bf-6834-4b6f-b9d7-e41e530b470c', $config);

      if ($widgets->has('0da62598-d7a1-4f21-97a4-0f9c1111eb91')) {
        $widgets->removeInstanceId('0da62598-d7a1-4f21-97a4-0f9c1111eb91');
      }

      $b->save();
    }

    $this->installAllOfType('entity_browser.browser');
  }

  /**
   * Switch embed button to utilize new types.
   */
  protected function updateEmbedButton() {
    if ($b = EmbedButton::load('panopoly_media_wysiwyg_media_embed')) {
      $settings = $b->get('type_settings');
      $settings['bundles'][] = 'panopoly_media_file';
      $settings['bundles'][] = 'panopoly_media_image';
      $settings['bundles'][] = 'panopoly_media_remote_video';
      $settings['bundles'] = array_diff($settings['bundles'], [
        'file',
        'image',
        'video',
      ]);
      $settings['bundles'] = array_unique($settings['bundles']);
      sort($settings['bundles']);
      $b->set('type_settings', $settings);
      $b->save();
    }

    $this->installAllOfType('embed.button');
  }

  /**
   * Install all config of the specified type.
   *
   * @param string $type
   *   Config type.
   */
  protected function installAllOfType($type) {
    $configIds = $this->getConfigFileStorage()->listAll($type . '.');
    foreach ($configIds as $configId) {
      if (!$this->getConfigStorage()->exists($configId)) {
        $config = $this->createConfigEntity($configId, $this->getConfigFileStorage()->read($configId));
        $config->save();
      }
    }
  }

  /**
   * Creates a config entity.
   *
   * @param string $id
   *   The full config entity ID, with provider and type/config prefix.
   * @param array $data
   *   Configuration data.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface
   *   The configuration entity.
   */
  protected function createConfigEntity($id, array $data) {
    list($provider, $config_prefix, $id) = explode('.', $id, 3);
    $class = $this->getConfigEntityClass($provider, $config_prefix);
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $config */
    return $class::create($data);
  }

  /**
   * Gets a configuration entity.
   *
   * @param string $id
   *   The full config entity ID, with provider and type/config prefix.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface
   *   The configuration entity.
   */
  protected function getConfigEntity($id) {
    list($provider, $config_prefix, $id) = explode('.', $id, 3);
    $class = $this->getConfigEntityClass($provider, $config_prefix);
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $config */
    return $class::load($id);
  }

  /**
   * Get the class for a config entity type by provider and config prefix.
   *
   * @param string $provider
   *   The provider (module).
   * @param string $type
   *   The config type.
   *
   * @return string
   *   The class name of the configuration entity class.
   */
  protected function getConfigEntityClass($provider, $type) {
    foreach ($this->getEntityTypeManager()->getDefinitions() as $definition) {
      if ($definition->getProvider() == $provider) {
        if ($type == $definition->id() || $definition->get('config_prefix') == $type) {
          return $definition->getClass();
        }
      }
    }

    throw new \Exception(sprintf('Config entity class not found for "%s.%s".', $provider, $type));
  }

  /**
   * Converts media entities to new panopoly_* types.
   *
   * @param int $id
   *   The media ID.
   */
  protected function convertMediaEntity($id) {
    if (!$media = Media::load($id)) {
      return;
    }

    // Adjust the 'type' property.
    switch ($media->bundle()) {
      case 'file':
        $this->setEntityBundle($media, 'panopoly_media_file');
        break;

      case 'image':
        $this->setEntityBundle($media, 'panopoly_media_image');
        break;

      case 'video':
        $this->setEntityBundle($media, 'panopoly_media_remote_video');
        break;
    }
  }

  /**
   * Sets an entity's bundle.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $bundle
   *   The bundle.
   */
  protected function setEntityBundle(ContentEntityInterface $entity, $bundle) {
    if (!$entity instanceof ContentEntityBase) {
      return;
    }

    // Update the bundle in the database, and clear entity from cache.
    \Drupal::database()->update('media')
      ->condition('mid', $entity->id())
      ->fields(['bundle' => $bundle])
      ->execute();
    \Drupal::entityTypeManager()
      ->getStorage('media')
      ->resetCache([$entity->id()]);

    // Grab the original fields.
    $fields = $entity->getFields(FALSE);

    // Load a fresh copy of the entity with updated field definitions.
    $new = Media::load($entity->id());

    // Copy the values of each of the original fields into the new fields.
    // Convert field_media_video_embed_field to field_media_oembed_video and
    // skip setting the bundle.
    foreach ($fields as $field => $value) {
      $field = $field == 'field_media_video_embed_field' ? 'field_media_oembed_video' : $field;
      /** @var \Drupal\Core\Field\FieldItemListInterface $new_field */
      $new_field = $new->get($field);
      if ($field != 'bundle') {
        $new_field->setValue($value->getValue());
      }
    }

    // Set the original property in order to prevent the media source from being
    // compared.
    $new->original = clone $new;

    // Save with new revision.
    if ($new instanceof RevisionableInterface) {
      $new->setNewRevision();
      if ($new instanceof RevisionLogInterface) {
        $new->setRevisionLogMessage('Converted to ' . $bundle);
      }
    }
    $new->save();
  }

  /**
   * Gets the entity field manager service.
   *
   * @return \Drupal\Core\Entity\EntityFieldManagerInterface
   *   The entity field manager service.
   */
  protected function getEntityFieldManager() {
    return \Drupal::service('entity_field.manager');
  }

  /**
   * Utility method to get nested value from config data.
   *
   * @param array $data
   *   The data array.
   * @param string $path
   *   The path of the desired data.
   * @param string $delimiter
   *   The path delimiter.
   *
   * @return mixed
   *   The value found at the specified path.
   */
  protected function getNestedValue(array $data, $path, $delimiter = '::') {
    $path = explode($delimiter, $path);
    return NestedArray::getValue($data, $path);
  }

  /**
   * Utility method to set nested value in config data.
   *
   * @param array $data
   *   The data array.
   * @param string $path
   *   The path of the desired data.
   * @param mixed $value
   *   The value to set.
   * @param string $delimiter
   *   The path delimiter.
   */
  protected function setNestedValue(array &$data, $path, $value, $delimiter = '::') {
    $path = explode($delimiter, $path);
    NestedArray::setValue($data, $path, $value);
  }

  /**
   * Utility method to change nested value in config data.
   *
   * Only changes the value if the original value matches an expected value.
   *
   * @param array $data
   *   The data array.
   * @param string $path
   *   The path of the desired data.
   * @param mixed $original_value
   *   The expected original value.
   * @param mixed $value
   *   The value to set.
   * @param string $delimiter
   *   The path delimiter.
   *
   * @return bool
   *   Indicates if a change was performed.
   */
  protected function changeNestedValue(array &$data, $path, $original_value, $value, $delimiter = '::') {
    // Strict comparison for bool/null.
    if ($original_value === TRUE || $original_value === FALSE || $original_value === NULL) {
      if ($this->getNestedValue($data, $path) !== $original_value) {
        return FALSE;
      }
    }
    else {
      if ($this->getNestedValue($data, $path) != $original_value) {
        return FALSE;
      }
    }
    $this->setNestedValue($data, $path, $value);
    return TRUE;
  }

  /**
   * Utility method to unset nested value in config data.
   *
   * @param array $data
   *   The data array.
   * @param string $path
   *   The path of the desired data.
   * @param string $delimiter
   *   The path delimiter.
   */
  protected function unsetNestedValue(array &$data, $path, $delimiter = '::') {
    $path = explode($delimiter, $path);
    NestedArray::unsetValue($data, $path);
  }

  /**
   * Gets the active config storage.
   *
   * @return \Drupal\Core\Config\StorageInterface
   *   The active config storage.
   */
  protected function getConfigStorage() {
    if ($this->configStorage === NULL) {
      $this->configStorage = \Drupal::service('config.storage');
    }
    return $this->configStorage;
  }

  /**
   * Gets the config file storage.
   *
   * @return \Drupal\Core\Config\StorageInterface
   *   The config file storage.
   */
  protected function getConfigFileStorage() {
    // @TODO: Update this storage directory.
    // As config updates over time, in order to run this update it will become
    // necessary to move this config in its current state to a different
    // location and utilize it from there.
    if ($this->configFileStorage === NULL) {
      $configPath = drupal_get_path('module', 'panopoly_media') . '/config/update_8205';
      $this->configFileStorage = new FileStorage($configPath);
    }
    return $this->configFileStorage;
  }

  /**
   * Gets the entity type manager service.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager service.
   */
  protected function getEntityTypeManager() {
    if (!$this->entityTypeManager) {
      $this->entityTypeManager = \Drupal::entityTypeManager();
    }
    return $this->entityTypeManager;
  }

  /**
   * Gets the module handler service.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler service.
   */
  protected function getModuleHandler() {
    if (!$this->moduleHandler) {
      $this->moduleHandler = \Drupal::moduleHandler();
    }
    return $this->moduleHandler;
  }

}
