<?php
/**
 * @file
 * panopoly_admin.features.defaultconfig.inc
 */

/**
 * Implements hook_defaultconfig_features().
 */
function panopoly_admin_defaultconfig_features() {
  return array(
    'panopoly_admin' => array(
      'strongarm' => 'strongarm',
    ),
  );
}

/**
 * Implements hook_defaultconfig_strongarm().
 */
function panopoly_admin_defaultconfig_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'admin_toolbar';
  $strongarm->value = array(
    'layout' => 'vertical',
    'position' => 'nw',
    'behavior' => 'ah',
    'blocks' => array(
      'admin-create' => -1,
      'admin-menu' => 1,
    ),
  );
  $export['admin_toolbar'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'user_register';
  $strongarm->value = '0';
  $export['user_register'] = $strongarm;

  return $export;
}
