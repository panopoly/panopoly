<?php

namespace Drupal\panopoly\Installer\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Installer form to optionally enable Panopoly Demo.
 */
class PanopolyDemoInstallerForm extends FormBase {

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * Constructs a PanopolyDemoInstallerForm.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
   */
  public function __construct(ModuleInstallerInterface $module_installer) {
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'panopoly_demo_installer_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = t('Demo content');

    $form['enable'] = [
      '#type' => 'checkbox',
      '#title' => 'Enable demo content',
      '#description' => t("Creates some demo content to help you test out Panopoly quickly. If you want to remove it later, simply disable the <em>Panopoly Demo</em> module."),
      '#default_value' => TRUE,
    ];

    $form['warning'] = [
      '#markup' => "<p><strong>Warning:</strong> Don't install the demo content if you're upgrading from Drupal 7 - you need to start from a blank site.</p>",
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#weight' => 15,
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $enable = $form_state->getValue('enable');

    // We have to use ->getUserInput() to supercede ->getValue() because that
    // isn't correctly set when passing the form value to drush si like:
    // "drush si panopoly panopoly_demo_installer_form.enable=0"
    $input = $form_state->getUserInput();
    if (isset($input['enable'])) {
      $enable = !empty($input['enable']);
    }

    if ($enable) {
      $this->moduleInstaller->install(['panopoly_demo']);
    }
  }

}
