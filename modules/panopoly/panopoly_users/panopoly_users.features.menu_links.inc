<?php
/**
 * @file
 * panopoly_users.features.menu_links.inc
 */

/**
 * Implements hook_menu_default_menu_links().
 */
function panopoly_users_menu_default_menu_links() {
  $menu_links = array();

  // Exported menu link: user-menu:user/login
  $menu_links['user-menu:user/login'] = array(
    'menu_name' => 'user-menu',
    'link_path' => 'user/login',
    'router_path' => 'user/login',
    'link_title' => 'Log in',
    'options' => array(),
    'module' => 'system',
    'hidden' => '-1',
    'external' => '0',
    'has_children' => '0',
    'expanded' => '0',
    'weight' => '0',
    'parent_path' => 'user',
  );
  // Translatables
  // Included for use with string extractors like potx.
  t('Log in');


  return $menu_links;
}
