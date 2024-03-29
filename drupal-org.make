;
; GENERATED FILE - DO NOT EDIT!
;

; Panopoly Admin Makefile

api = 2
core = 7.x

; UX/UI Improvements

projects[backports][version] = 1.0-alpha1
projects[backports][subdir] = contrib

projects[module_filter][version] = 2.2
projects[module_filter][subdir] = contrib

projects[simplified_menu_admin][version] = 1.0
projects[simplified_menu_admin][subdir] = contrib

projects[date_popup_authored][version] = 1.2
projects[date_popup_authored][subdir] = contrib

projects[admin_views][version] = 1.7
projects[admin_views][subdir] = contrib

projects[save_draft][version] = 1.4
projects[save_draft][subdir] = contrib

; Admin Toolbar Modules

projects[admin][version] = 2.0-beta3
projects[admin][subdir] = contrib
projects[admin][patch][1334804] = http://drupal.org/files/1334804-admin-jquery-updated-6.patch

projects[navbar][version] = 1.x-dev
projects[navbar][subdir] = contrib
projects[navbar][download][type] = git
projects[navbar][download][revision] = 455f81d
projects[navbar][download][branch] = 7.x-1.x
projects[navbar][patch][1757466] = http://drupal.org/files/navbar-conflict-1757466-14.patch
projects[navbar][patch][2050559] = http://drupal.org/files/z-index-heart-cools-2050559-1.patch

projects[breakpoints][version] = 1.6
projects[breakpoints][subdir] = contrib

projects[admin_menu][version] = 3.0-rc6
projects[admin_menu][subdir] = contrib

; jQuery Update was moved to Panopoly Core, but is left in Panopoly Admin's
; .make file to retain a stable 1.x branch of Panopoly. See the following URL
; for more information: http://drupal.org/node/2492811
projects[jquery_update][version] = 2.7
projects[jquery_update][subdir] = contrib

; Libraries
libraries[backbone][download][type] = get
libraries[backbone][download][url] = https://github.com/jashkenas/backbone/archive/1.0.0.tar.gz

libraries[underscore][download][type] = get
libraries[underscore][download][url] = https://github.com/jashkenas/underscore/archive/1.5.2.zip

; Panopoly Core Makefile

api = 2
core = 7.x

; Panels and Chaos Tools Magic

projects[ctools][version] = 1.19
projects[ctools][subdir] = contrib
projects[ctools][patch][1910608] = https://www.drupal.org/files/issues/2020-12-01/views_content-keyword-substitution-1910608-83.patch
projects[ctools][patch][1000146] = https://www.drupal.org/files/issues/2019-01-07/views_panes-more_link_text-1000146-32.patch

projects[panels][version] = 3.10
projects[panels][subdir] = contrib

projects[panels_breadcrumbs][version] = 2.4
projects[panels_breadcrumbs][subdir] = contrib

projects[panelizer][version] = 3.4
projects[panelizer][subdir] = contrib
projects[panelizer][patch][1549608] = https://www.drupal.org/files/issues/panelizer-n1549608-26.patch
projects[panelizer][patch][2788851] = https://www.drupal.org/files/issues/panelizer-administer-panelizer-2788851-2.patch

projects[fieldable_panels_panes][version] = 1.13
projects[fieldable_panels_panes][subdir] = contrib

projects[pm_existing_pages][version] = 1.4
projects[pm_existing_pages][subdir] = contrib

projects[fape][version] = 1.2
projects[fape][subdir] = contrib

; Views Magic

projects[views][version] = 3.24
projects[views][subdir] = contrib
projects[views][patch][2037469] = https://www.drupal.org/files/issues/views-exposed-sorts-2037469-26.patch
projects[views][patch][2977851] = https://www.drupal.org/files/issues/2019-09-23/2977851-views-php72-count-14_0.patch
projects[views][patch][2284423] = https://www.drupal.org/files/issues/2019-04-29/views-same_sort_twice-2284423-3.patch
projects[views][patch][3076826] = https://www.drupal.org/files/issues/2019-08-23/views-php7-3076826-2.patch

projects[views_autocomplete_filters][version] = 1.2
projects[views_autocomplete_filters][subdir] = contrib
projects[views_autocomplete_filters][patch][2374709] = http://www.drupal.org/files/issues/views_autocomplete_filters-cache-2374709-2.patch
projects[views_autocomplete_filters][patch][2317351] = http://www.drupal.org/files/issues/views_autocomplete_filters-content-pane-2317351-4.patch
projects[views_autocomplete_filters][patch][2404893] = https://www.drupal.org/files/issues/2404893-grammar_correction-11.patch

projects[views_bulk_operations][version] = 3.6
projects[views_bulk_operations][subdir] = contrib

; The Usual Suspects

projects[pathauto][version] = 1.3
projects[pathauto][subdir] = contrib

projects[token][version] = 1.8
projects[token][subdir] = contrib

projects[entity][version] = 1.9
projects[entity][subdir] = contrib

projects[libraries][version] = 2.5
projects[libraries][subdir] = contrib

projects[transliteration][version] = 3.2
projects[transliteration][subdir] = contrib

; Field modules

projects[date][version] = 2.10
projects[date][subdir] = contrib
projects[date][patch][2449261] = https://www.drupal.org/files/issues/2018-08-30/date-cannot_create_references_to_from_string_offsets-2449261-14.patch
projects[date][patch][2889759] = https://www.drupal.org/files/issues/date-php7Offset-2889759-1.patch
projects[date][patch][2995679] = https://www.drupal.org/files/issues/2018-08-28/date-array_conversion-2995679-2-D7.patch
projects[date][patch][2843367-1] = https://www.drupal.org/files/issues/2019-01-16/2843367-php71-string-offset-47.patch
projects[date][patch][2843367-2] = https://www.drupal.org/files/issues/2843367-php71-string-offset-26.patch
projects[date][patch][3145639] = https://www.drupal.org/files/issues/2020-06-04/date-php74-3145639-2.patch

projects[entityreference][version] = 1.5
projects[entityreference][subdir] = contrib

projects[field_group][version] = 1.6
projects[field_group][subdir] = contrib
projects[field_group][patch][3085340] = https://www.drupal.org/files/issues/2019-10-03/3085340-2.patch
projects[field_group][patch][3083542] = https://www.drupal.org/files/issues/2020-06-09/field_group-php-7.2-compatible-3083542-5.patch

projects[link][version] = 1.9
projects[link][subdir] = contrib

; Harness the Power of Features and Apps with Default Content

projects[apps][version] = 1.1
projects[apps][subdir] = contrib
projects[apps][patch][2945929] = https://www.drupal.org/files/issues/apps-php7-compat-2945929.patch

projects[features][version] = 2.13
projects[features][subdir] = contrib

projects[strongarm][version] = 2.0
projects[strongarm][subdir] = contrib

projects[defaultconfig][version] = 1.0-alpha11
projects[defaultconfig][subdir] = contrib
projects[defaultconfig][patch][1900574] = http://drupal.org/files/1900574.defaultconfig.undefinedindex_11.patch

projects[defaultcontent][version] = 1.0-alpha9
projects[defaultcontent][subdir] = contrib
projects[defaultcontent][patch][1754428] = http://drupal.org/files/1754428-allow-node-export-alter.patch
projects[defaultcontent][patch][1757782] = http://drupal.org/files/1757782-cannot-import-menu-hierarchy-8.patch
projects[defaultcontent][patch][2946138] = https://www.drupal.org/files/issues/defaultcontent-php7-compat-2946138.patch
projects[defaultcontent][patch][3172478] = https://www.drupal.org/files/issues/2020-09-22/defaultcontent-php74-implode-3172478-2.patch

projects[migrate][version] = "2.11"
projects[migrate][type] = "module"
projects[migrate][subdir] = "contrib"

projects[migrate_extras][version] = "2.5"
projects[migrate_extras][type] = "module"
projects[migrate_extras][subdir] = "contrib"

; jQuery Update was moved to Panopoly Core, but is left in Panopoly Admin's
; .make file to retain a stable 1.x branch of Panopoly. See the following URL
; for more information: http://drupal.org/node/2492811
projects[jquery_update][version] = 2.7
projects[jquery_update][subdir] = contrib

; Recommended Modules
projects[devel][version] = 1.7
projects[devel][subdir] = contrib

projects[distro_update][version] = 1.0-beta4
projects[distro_update][subdir] = contrib

projects[features_override][version] = 2.0-rc3
projects[features_override][subdir] = contrib

projects[uuid][version] = 1.3
projects[uuid][subdir] = contrib

; Panopoly Images Makefile

api = 2
core = 7.x

; Cropping images

projects[manualcrop][version] = 1.7
projects[manualcrop][subdir] = contrib
projects[manualcrop][patch][3177209] = https://www.drupal.org/files/issues/2020-10-23/manualcrop-csp-3177209-5.patch

; jquery.imagesLoaded library for manualcrop
libraries[jquery.imagesloaded][download][type] = file
libraries[jquery.imagesloaded][download][url] = https://github.com/desandro/imagesloaded/archive/v2.1.2.tar.gz
libraries[jquery.imagesloaded][download][subtree] = imagesloaded-2.1.2

; jquery.imgAreaSelect library for manualcrop
libraries[jquery.imgareaselect][download][type] = file
libraries[jquery.imgareaselect][download][url] = https://github.com/odyniec/imgareaselect/archive/v0.9.11-rc.1.tar.gz
libraries[jquery.imgareaselect][download][subtree] = imgareaselect-0.9.11-rc.1

; Panopoly Search Makefile

api = 2
core = 7.x

; Search API and Facet API Modules

projects[facetapi][version] = 1.6
projects[facetapi][subdir] = contrib

projects[search_api][version] = 1.27
projects[search_api][subdir] = contrib

projects[search_api_solr][version] = 1.15
projects[search_api_solr][subdir] = contrib

projects[search_api_db][version] = 1.8
projects[search_api_db][subdir] = contrib

; Solr PHP Client Library

libraries[SolrPhpClient][download][type] = get
libraries[SolrPhpClient][download][url] = https://github.com/PTCInc/solr-php-client/archive/master.zip

; Panopoly Theme Makefile

api = 2
core = 7.x

; Radix Layouts
projects[radix_layouts][version] = 3.4
projects[radix_layouts][subdir] = contrib

; Summon the Power of Respond.js

projects[respondjs][version] = 1.5
projects[respondjs][subdir] = contrib

; The RespondJS library is licenesed under the MIT license, and can't be
; packaged on Drupal.org. Copy and paste the below into your distributions
; .make file if you still need it, or clone manually from Git.

;libraries[respondjs][download][type] = git
;libraries[respondjs][download][url] = https://github.com/scottjehl/Respond.git
;libraries[respondjs][download][revision] = 86dea4ab1e93a275e2044965ab7452c3c1e2c6da
;libraries[respondjs][download][branch] = master

; Bundle a Few Panopoly Approved Themes

projects[responsive_bartik][version] = 1.0
projects[responsive_bartik][type] = theme

; projects[radix][version] = 1.x-dev
; projects[radix][type] = theme
; projects[radix][download][type] = git
; projects[radix][download][revision] = b873330
; projects[radix][download][branch] = 7.x-1.x

; Panopoly Users Makefile

api = 2
core = 7.x

; To sync field_user_picture with the Drupal user picture
projects[user_picture_field][version] = 1.0-rc1
projects[user_picture_field][subdir] = contrib

; Panopoly Widgets Makefile

api = 2
core = 7.x

; Panopoly - Contrib - Fields

projects[tablefield][version] = 3.6
projects[tablefield][subdir] = contrib
projects[tablefield][patch][3128030] = https://www.drupal.org/files/issues/2020-04-22/tablefield-header-orientation-3128030-5.patch
projects[tablefield][patch][3137640] = https://www.drupal.org/files/issues/2020-05-18/tablefield-7008-fix-3137640-2.patch

projects[simple_gmap][version] = 1.5
projects[simple_gmap][subdir] = contrib
projects[simple_gmap][patch][2902178] = https://www.drupal.org/files/issues/2021-06-09/simple_gmap-iframe-title-2902178-19.patch

; Panopoly - Contrib - Widgets

projects[menu_block][version] = 2.8
projects[menu_block][subdir] = contrib

; Panopoly - Contrib - Files & Media

projects[file_entity][version] = 2.30
projects[file_entity][subdir] = contrib

projects[media][version] = 2.27
projects[media][subdir] = contrib

projects[media_youtube][version] = 3.10
projects[media_youtube][subdir] = contrib

projects[media_vimeo][version] = 2.1
projects[media_vimeo][subdir] = contrib
projects[media_vimeo][patch][2446199] = https://www.drupal.org/files/issues/no_exception_handling-2446199-1.patch
projects[media_vimeo][patch][2913855] = https://www.drupal.org/files/issues/media_vimeo_https-2913855-3.patch

; Panopoly WYSIWYG Makefile

api = 2
core = 7.x

; The WYSIWYG Module Family

projects[wysiwyg][subdir] = contrib
projects[wysiwyg][version] = 2.9
projects[wysiwyg][patch][1489096] = https://www.drupal.org/files/issues/2019-11-16/wysiwyg-table-format-1489096-10.patch
projects[wysiwyg][patch][1786732] = https://www.drupal.org/files/issues/2019-11-16/wysiwyg-arbitrary_image_paths_markitup-1786732-6.patch

projects[wysiwyg_filter][version] = 1.6-rc9
projects[wysiwyg_filter][subdir] = contrib

; The WYSIWYG Helpers

projects[linkit][version] = 3.6
projects[linkit][subdir] = contrib

projects[image_resize_filter][version] = 1.16
projects[image_resize_filter][subdir] = contrib

projects[caption_filter][version] = 1.3
projects[caption_filter][subdir] = contrib

; Include our Editors

libraries[tinymce][download][type] = get
libraries[tinymce][download][url] = http://download.moxiecode.com/tinymce/tinymce_3.5.11.zip
libraries[tinymce][patch][1561882] = http://drupal.org/files/1561882-cirkuit-theme-tinymce-3.5.8.patch
libraries[tinymce][patch][2876031] = https://www.drupal.org/files/issues/tinymce-chrome58-fix-2876031-5.patch

libraries[markitup][download][type] = git
libraries[markitup][download][url] = https://github.com/markitup/1.x.git
libraries[markitup][download][revision] = 2c88c42
libraries[markitup][download][branch] = master
libraries[markitup][patch][1715642] = http://drupal.org/files/1715642-adding-html-set-markitup-editor.patch
