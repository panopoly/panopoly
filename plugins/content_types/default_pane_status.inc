<?php

/**
 * @file
 * A panel plugin to provide administrative information about the site's pane default status
 */

$plugin = array(
  'title' => t('Default Pane Status'),
  'description' => t('This is list of the pane types and their default status.'),
  'content_types' => array('default_pane_status'),
  'category' => t('Admin'),
  'required context' => new ctools_context_required(t('Panopoly Admin'), 'string'),
  'single' => TRUE,
);

/**
  * Put the output for you pages type in the blocks pages.
  */
function panopoly_admin_default_pane_status_content_type_render($subtype, $conf, $panel_args, $context) {

  // Get the default pane status
  $panels_page_default = variable_get('panels_page_default');
  $header = array(
    //array('data' => '', 'style' => 'width: 16px'), 
    array('data' => t('Title')), 
    array('data' => t('Status'), 'style' => 'width: 20%'),
  );
  $rows = array();

  foreach($panels_page_default as $category => $category_status) {
    $rows[] = array(
      $category,
      $category_status,
    );
  }

  $pane->title = t('Default Pane Status');
  $pane->content = theme('table', array('header' => $header, 'rows' => $rows));
  return $pane;
  return $pane;
}
