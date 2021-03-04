<?php

namespace Drupal\panopoly_magic\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\layout_builder\Form\UpdateBlockForm;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Enhances the update block form with live preview.
 */
class LayoutBuilderUpdateBlockForm extends UpdateBlockForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $region = NULL, $uuid = NULL) {
    $form = parent::buildForm($form, $form_state, $section_storage, $delta, $region, $uuid);

    // Add a preview and cancel buttons.
    if ($this->isAjax()) {
      $plugin_id = $section_storage->getSection($delta)->getComponent($uuid)->getPluginId();
      list($plugin_base_id, ) = explode(':', $plugin_id);
      if ($plugin_base_id !== 'block_content') {
        $form['actions']['preview'] = [
          '#type' => 'submit',
          '#value' => $this->t('Preview'),
          '#attributes' => [
            'class' => [
              'panopoly-magic-live-preview',
            ],
          ],
          '#ajax' => [
            'callback' => '::ajaxSubmit',
            'disable-refocus' => TRUE,
          ],
        ];
        $form['actions']['cancel'] = [
          '#type' => 'submit',
          '#value' => $this->t('Cancel'),
          '#ajax' => [
            'callback' => '::ajaxSubmit'
          ],
        ];

        // Attach preview library.
        $form['#attached']['library'][] = 'panopoly_magic/preview';
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Suppress form validation errors when Preview is clicked, allowing partial
    // previews and removing error messages when a block has multiple required
    // fields.
    // Supporess form validation errors when Cancel is clicked, allowing the
    // dialog to be dismissed discarding changes if there are validation errors.
    $submit_button_name = end($form_state->getTriggeringElement()['#parents']);
    if ($submit_button_name == 'preview' || $submit_button_name == 'cancel') {
      // Suppress all future validation errors from parent::validateForm().
      $form_state->setLimitValidationErrors([]);
      // Clear any existing validation errors from the Field API.
      $form_state->clearErrors();
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Handle preview mode.
    if ($form_state->getValue('op') == $form['actions']['preview']['#value']) {
      // Call the plugin submit handler.
      $subform_state = SubformState::createForSubform($form['settings'], $form, $form_state);
      $this->getPluginForm($this->block)->submitConfigurationForm($form, $subform_state);

      // Update the component configuration.
      $configuration = array_merge($this->block->getConfiguration(), $form_state->getValue('settings'));
      $section = $this->sectionStorage->getSection($this->delta);
      $section->getComponent($this->uuid)->setConfiguration($configuration);

      // We want to preview so rebuild the layout only.
      // Do not update the temp layout storage.
      // Preview config can hence be discarded and are not saved.
      return $this->rebuildLayout($this->sectionStorage);
    }
    elseif ($form_state->getValue('op') == $form['actions']['cancel']['#value']) {
      // Pull the last configuration from the temp layout storage and rebuild
      // the layout.
      $this->sectionStorage = $this->layoutTempstoreRepository->get($this->sectionStorage);
      return $this->rebuildLayout($this->sectionStorage);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    // When in preview mode return the rebuilt layout.
    if ($form_state->getValue('op') == $form['actions']['preview']['#value']) {
      return $this->rebuildLayout($this->sectionStorage);
    }

    return parent::successfulAjaxSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function submitLabel() {
    return $this->t('Save');
  }

}
