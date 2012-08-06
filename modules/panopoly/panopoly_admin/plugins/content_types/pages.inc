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
  $pane->title = t('Pages');
  $pane->content = '';

  // Assemble the Pages
  // $pages = array();
  // $result = db_query('SELECT * FROM {panelizer_entity} WHERE name IN (:names)', array(':names' => $names));

  /* foreach (menu_get_pages() as $menu_name => $menu) {
    $menu_links = array();
    if (user_access('administer menu')) {
      $menu_links[] = array(
        'title' => t('Configure', array('!menu' => ucwords($menu))),
        'href' => 'admin/structure/menu/manage/' . $menu_name . '/edit',
        'query' => array(),    
      );
      $menu_links[] = array(
        'title' => t('Add new', array('!menu' => ucwords($menu))),
        'href' => 'admin/structure/menu/manage/' . $menu_name . '/add',
        'query' => array(),           
      );
    }
    $menu_count = db_query("SELECT count(*) FROM {menu_links} WHERE menu_name = :menu_name", array(':menu_name' => $menu_name))->fetchField();
    
    // Filter out the options we don't want
    if (!in_array($menu_name, array('devel', 'management', 'navigation', 'user-menu'))) {
      $menu_links = (count($menu_links)) ? theme('links__ctools_dropbutton', array('links' => $menu_links, 'attributes' => array('class' => array('links', 'inline')))) : ''; 
      $pages[] = '<div class="menu-links-wrapper clearfix"><div class="menu-links">' . $menu_links . '</div><strong>' . ucwords(str_replace(' Menu', '', $menu)) . '</strong><br /> <em>' . format_plural($menu_count, '1 link' , '@count links') . '</em></div>';
    }
  } */

  // Assemble the Data
  $general_pages = array();
  $page_templates = array();
  $overridden_templates = array();

  // Create the Output
  $pane->content .= theme('item_list', array('items' => $general_pages, 'title' => 'General Pages'));
  $pane->content .= theme('item_list', array('items' => $page_templates, 'title' => 'Sitewide Page Templates'));
  $pane->content .= theme('item_list', array('items' => $overridden_templates, 'title' => 'Overriden Page Templates'));
  return $pane;
}
