<?php

$plugin = array(
  'title' => t('Pages'),
  'description' => t('This is a standard list of pages that are available'),
  'content_types' => array('pages'),
  'category' => t('Admin'),
  'all contexts' => TRUE,
  'single' => TRUE,
);

/**
  * Put the output for you pages type in the blocks pages.
  */
function panopoly_admin_pages_content_type_render($subtype, $conf, $panel_args, $context) {
  $pane = new stdClass();

  // Assemble the General Page Data
  $general_pages = array();
  module_load_include('inc', 'page_manager', 'page_manager.admin');
  $tasks = page_manager_get_tasks_by_type('page');
  $pages = array('operations' => array());
  page_manager_get_pages($tasks, $pages);
  foreach($pages['rows'] as $id => $info) {
    if (strpos($id, 'panopoly_admin')) { 
      // Exclude any admin pages
    }
    elseif($info['data']['type']['data'] == 'System') {
      // Exclude any system pages
    }
    elseif($pages['disabled'][$id] == TRUE) {
      // Exclude any disabled pages
    }
    else {
      // Include anything else as a general page
      $general_pages[] = array(
        'data' => array(
          $info['data']['title'],
          $info['data']['path'],
        ),
      );
    }
  }

  // Assemble the Page Template Data for Nodes, Taxonomy Terms, and Users
  $page_templates = array();
  $plugins = panelizer_get_entity_plugins();
  foreach ($plugins as $entity_type => $plugin) {
    if (in_array($entity_type, array('node', 'taxonomy_term', 'user'))) {
      $handler = panelizer_entity_plugin_get_handler($entity_type);
      $entity_info = entity_get_info($entity_type);
      foreach($handler->plugin['bundles'] as $bundle_type => $bundle) {
        if (!empty($bundle['view modes']['page_manager']) && $bundle['view modes']['page_manager']['status'] == 1 && $bundle['view modes']['page_manager']['default'] == 1) {
          $page_templates[]  = array(
            'data' => array(
              $entity_info['bundles'][$bundle_type]['label'],
              '<em>' . $entity_info['label'] . ' template' . '</em>',
              (pathauto_pattern_load_by_entity($entity_type, $bundle_type)) ? l('/' . pathauto_pattern_load_by_entity($entity_type, $bundle_type), 'admin/config/content/panelizer/' .$entity_type . '/' . $bundle_type . '.page_manager/content') : l('/' . $handler->plugin['entity path'], 'admin/config/content/panelizer/' .$entity_type . '/' . $bundle_type . '.page_manager/content'),
            ),                             
          );
        }
      }
    }
  }

  // Assemble the Overriden Template Data
  $overridden_templates = array();
  $entity_types = array('node', 'taxonomy_term', 'user');
  // TODO - handle cases where there are more than 30 pages. This will involve a pager or something fancy like that.
  foreach(db_query("SELECT * FROM {panelizer_entity} WHERE entity_type IN (:entity_types) AND view_mode = '' LIMIT 30", array(':entity_types' => $entity_types)) as $overridden_page) {
    $entity_info = entity_get_info($overridden_page->entity_type);
    $entity = entity_load($overridden_page->entity_type, array($overridden_page->entity_id));
    $entity = $entity[$overridden_page->entity_id];
    $entity_uri = entity_uri($overridden_page->entity_type, $entity);
    $overridden_templates[] = array(
      'data' => array(
        $entity->title,
        '<em>' . $entity_info['bundles'][$entity->type]['label'] . ' template' . '</em>',
        l(url($entity_uri['path']), $entity_uri['path']),
      )
    );
  }

  // Create the Output
  $pane->content .= '<h2 class="page-title">' . t('General Pages') . '</h2>';
  $pane->content .= (count($general_pages)) ? theme('table', array('header' => array(), 'rows' => $general_pages, 'attributes' => array('class' => 'panels-manage'))) : t('No general pages have yet been created!');
  $pane->content .= '<h2 class="page-title">' . t('Page Templates') . '</h2>';
  $pane->content .= (count($page_templates)) ? theme('table', array('header' => array(), 'rows' => $page_templates, 'attributes' => array('class' => 'panelizer-templates'))) : t('No page templates have yet been created!');
  $pane->content .= '<h2 class="page-title">' . t('Overriden Page Templates') . '</h2>';
  $pane->content .= (count($overridden_templates)) ? theme('table', array('header' => array(), 'rows' => $overridden_templates, 'attributes' => array('class' => 'panelizer-overrides'))) : t('No page templates have yet been overridden!');
  return $pane;
}