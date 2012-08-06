<?php
/**
 * @file
 * panopoly_core.features.taxonomy.inc
 */

/**
 * Implements hook_taxonomy_default_vocabularies().
 */
function panopoly_core_taxonomy_default_vocabularies() {
  return array(
    'panopoly_topics' => array(
      'name' => 'Topics',
      'machine_name' => 'panopoly_topics',
      'description' => 'This is a sitewide taxonomy of topics to organize the content on your site!',
      'hierarchy' => '0',
      'module' => 'taxonomy',
      'weight' => '0',
    ),
  );
}
