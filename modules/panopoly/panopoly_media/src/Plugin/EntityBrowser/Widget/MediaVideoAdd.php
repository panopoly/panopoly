<?php

namespace Drupal\panopoly_media\Plugin\EntityBrowser\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\entity_browser\WidgetBase;
use Drupal\media\MediaInterface;

/**
 * Uses a view to provide entity listing in a browser's widget.
 *
 * @EntityBrowserWidget(
 *   id = "panopoly_media_video_add",
 *   label = @Translation("Add video as a media item"),
 *   description = @Translation("Allows creation of video media items."),
 *   auto_select = FALSE
 * )
 */
class MediaVideoAdd extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'media_type' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);

    $form_display = entity_get_form_display('media', $this->configuration['media_type'], 'entity_browser');
    $media = $this->entityTypeManager->getStorage('media')->create([
      'bundle' => $this->configuration['media_type'],
    ]);
    $form_display->buildForm($media, $form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntities(array $form, FormStateInterface $form_state) {
    $form_display = entity_get_form_display('media', $this->configuration['media_type'], 'entity_browser');
    $media = $this->entityTypeManager->getStorage('media')->create([
      'bundle' => $this->configuration['media_type'],
    ]);
    $form_display->extractFormValues($media, $form, $form_state);
    return [$media];
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    if (!empty($form_state->getTriggeringElement()['#eb_widget_main_submit'])) {
      $videos = $this->prepareEntities($form, $form_state);
      array_walk(
        $videos,
        function (MediaInterface $media) {
          $media->save();
        }
      );

      $this->selectEntities($videos, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $media_type_options = [];
    $media_types = $this
      ->entityTypeManager
      ->getStorage('media_type')
      ->loadByProperties(['source' => 'video_embed_field']);

    foreach ($media_types as $media_type) {
      $media_type_options[$media_type->id()] = $media_type->label();
    }

    if (empty($media_type_options)) {
      $url = Url::fromRoute('entity.media_type.add_form')->toString();
      $form['media_type'] = [
        '#markup' => $this->t("You don't have media type of the Image type. You should <a href='!link'>create one</a>", ['!link' => $url]),
      ];
    }
    else {
      $form['media_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Media type'),
        '#default_value' => $this->configuration['media_type'],
        '#options' => $media_type_options,
      ];
    }

    return $form;
  }

}
