; Panopoly Core Makefile

api = 2
core = 7.x

; The Panopoly Foundation

projects[panopoly_images][version] = 1.x-dev
projects[panopoly_images][subdir] = panopoly

projects[panopoly_theme][version] = 1.x-dev
projects[panopoly_theme][subdir] = panopoly

projects[panopoly_magic][version] = 1.x-dev
projects[panopoly_magic][subdir] = panopoly

projects[panopoly_widgets][version] = 1.x-dev
projects[panopoly_widgets][subdir] = panopoly

projects[panopoly_admin][version] = 1.x-dev
projects[panopoly_admin][subdir] = panopoly

projects[panopoly_pages][version] = 1.x-dev
projects[panopoly_pages][subdir] = panopoly

projects[panopoly_users][version] = 1.x-dev
projects[panopoly_users][subdir] = panopoly

; The Panopoly Toolset

projects[panopoly_wysiwyg][version] = 1.x-dev
projects[panopoly_wysiwyg][subdir] = panopoly

projects[panopoly_search][version] = 1.x-dev
projects[panopoly_search][subdir] = panopoly

; Panels and Chaos Tools Magic

projects[ctools][version] = 1.2
projects[ctools][subdir] = contrib
projects[ctools][patch][1294478] = http://drupal.org/files/dynamic-modal-1294478-34.patch

projects[panels][version] = 3.3
projects[panels][subdir] = contrib
projects[panels][patch][1735336] = http://drupal.org/files/1735336-repaint-draghandle-ipe-initial.patch

projects[panels_breadcrumbs][version] = 2.x-dev
projects[panels_breadcrumbs][subdir] = contrib
projects[panels_breadcrumbs][download][type] = git
projects[panels_breadcrumbs][download][revision] = 531f589
projects[panels_breadcrumbs][download][branch] = 7.x-2.x

projects[panelizer][version] = 3.0-rc1
projects[panelizer][subdir] = contrib

projects[fieldable_panels_panes][version] = 1.2
projects[fieldable_panels_panes][subdir] = contrib

projects[pm_existing_pages][version] = 1.4
projects[pm_existing_pages][subdir] = contrib

projects[fape][version] = 1.1
projects[fape][subdir] = contrib

; Views Magic

projects[views][version] = 3.5
projects[views][subdir] = contrib

projects[views_autocomplete_filters][version] = 1.0-beta1
projects[views_autocomplete_filters][subdir] = contrib

projects[views_bulk_operations][version] = 3.0-rc1
projects[views_bulk_operations][subdir] = contrib


; The Usual Suspects

projects[jquery_update][version] = 2.2
projects[jquery_update][subdir] = contrib

projects[pathauto][version] = 1.2
projects[pathauto][subdir] = contrib
projects[pathauto][patch][936222] = http://drupal.org/files/936222-pathauto-persist.patch

projects[token][version] = 1.2
projects[token][subdir] = contrib

projects[entity][version] = 1.0-rc3
projects[entity][subdir] = contrib

projects[libraries][version] = 2.0
projects[libraries][subdir] = contrib

projects[devel][version] = 1.3
projects[devel][subdir] = contrib

; Harness the Power of Features and Apps

projects[apps][version] = 1.0-beta7
projects[apps][subdir] = contrib

projects[features][version] = 1.0
projects[features][subdir] = contrib

projects[strongarm][version] = 2.0
projects[strongarm][subdir] = contrib

; Allow for Default Content

projects[defaultcontent][version] = 1.x-dev
projects[defaultcontent][subdir] = contrib
projects[defaultcontent][download][type] = git
projects[defaultcontent][download][revision] = d8806d8
projects[defaultcontent][download][branch] = 7.x-1.x

projects[uuid][version] = 1.x-dev
projects[uuid][subdir] = contrib
projects[uuid][download][type] = git
projects[uuid][download][revision] = 806c301
projects[uuid][download][branch] = 7.x-1.x
