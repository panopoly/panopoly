#!/usr/bin/env php
<?php
/**
 * @file
 * Merges the composer.json requirements from the panopoly_* modules.
 */

$PROFILE_REQUIREMENTS = [
  "cweagans/composer-patches" => "^1.6.5",
  "drupal/core" => "^8.6.16",
  "drupal/features" => "~3.7",
];

$PANOPOLY_MODULES = [
  "panopoly_core",
  "panopoly_images",
  "panopoly_pages",
  "panopoly_theme",
  "panopoly_widgets",
  "panopoly_demo",
  "panopoly_media",
  "panopoly_test",
  "panopoly_users",
  "panopoly_wysiwyg",
];

function load_composer_json_file($filename) {
  return json_decode(file_get_contents($filename), TRUE);
}

function save_composer_json_file($filename, $data) {
  file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function main() {
  global $PROFILE_REQUIREMENTS, $PANOPOLY_MODULES;

  $package_index = [];

  foreach ($PROFILE_REQUIREMENTS as $package => $version) {
    $package_index[$package]['profile'] = $version;
  }
  foreach ($PANOPOLY_MODULES as $module) {
    $module_composer_json = load_composer_json_file("modules/panopoly/{$module}/composer.json");
    foreach ($module_composer_json['require'] as $package => $version) {
      $package_index[$package][$module] = $version;
    }
  }

  $main_composer_json = load_composer_json_file("composer.json");
  $main_composer_json['require'] = $PROFILE_REQUIREMENTS;
  foreach ($package_index as $package => $versions) {
    // Skip any of the Panopoly modules.
    list ($vendor, $short_package_name) = explode('/', $package);
    if ($vendor == 'drupal' && in_array($short_package_name, $PANOPOLY_MODULES)) {
      continue;
    }

    $unique_versions = array_unique($versions);
    if (count($unique_versions) > 1) {
      throw new \Exception("Panopoly sub-modules have dependencies with non-matching requirements for {$package} package: " . print_r($versions, TRUE));
    }

    $main_composer_json['require'][$package] = reset($unique_versions);
  }

  ksort($main_composer_json['require']);

  save_composer_json_file('composer.json', $main_composer_json);
}

if (php_sapi_name() === 'cli') {
  main();
}
