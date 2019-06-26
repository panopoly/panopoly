<?php

/**
 * @file
 * Provides an install task for selection a default theme.
 */

use Drupal\panopoly_theme\Form\PanopolyThemeSelectionForm;

/**
 * Adds an install task to allow selection of the theme.
 */
function panopoly_theme_profile_theme_selection_install_task(&$install_task) {
  $tasks[PanopolyThemeSelectionForm::class] = [
    'display_name' => t('Choose a theme'),
    'type' => 'form',
  ];
  return $tasks;
}
