; Panopoly Core Makefile

api = 2
core = 8.x

; Panels and Chaos Tools Magic

projects[ctools][version] = 3.0
projects[ctools][subdir] = contrib
projects[ctools][patch][2657060] = https://www.drupal.org/files/issues/ctools-exposed-filter-block-config-2657060-56.patch

projects[panels][version] = 4.3
projects[panels][subdir] = contrib
projects[panels][patch][2849219] = https://www.drupal.org/files/issues/panels-hook-layouts-alter-2849219-17.patch
projects[panels][patch][2824508] = https://www.drupal.org/files/issues/panels-ipe-search-2824508-8.patch
projects[panels][patch][2822390] = https://www.drupal.org/files/issues/panels-toolbar-vertical-2822390-7.patch

projects[page_manager][version] = 4.0-beta3
projects[page_manager][subdir] = contrib

;projects[panels_breadcrumbs][version] = 2.2
;projects[panels_breadcrumbs][subdir] = contrib

projects[panelizer][version] = 4.1
projects[panelizer][subdir] = contrib

;projects[fape][version] = 1.2
;projects[fape][subdir] = contrib

; Views Magic

;projects[views_autocomplete_filters][version] = 1.2
;projects[views_autocomplete_filters][subdir] = contrib
;projects[views_autocomplete_filters][patch][2374709] = http://www.drupal.org/files/issues/views_autocomplete_filters-cache-2374709-2.patch
;projects[views_autocomplete_filters][patch][2317351] = http://www.drupal.org/files/issues/views_autocomplete_filters-content-pane-2317351-4.patch

;projects[views_bulk_operations][version] = 3.3
;projects[views_bulk_operations][subdir] = contrib

; The Usual Suspects

projects[pathauto][version] = 1.2
projects[pathauto][subdir] = contrib

projects[token][version] = 1.3
projects[token][subdir] = contrib

;projects[libraries][version] = 2.2
;projects[libraries][subdir] = contrib

; Field modules

;projects[field_group][version] = 1.4
;projects[field_group][subdir] = contrib

; Harness the Power of Features and Apps with Default Content

projects[features][version] = 3.7
projects[features][subdir] = contrib

projects[config_actions][version] = 1.0-beta4
projects[config_actions][subdir] = contrib

projects[config_update][version] = 1.5
projects[config_update][subdir] = contrib

;projects[apps][version] = 1.0
;projects[apps][subdir] = contrib

;projects[defaultconfig][version] = 1.0-alpha11
;projects[defaultconfig][subdir] = contrib
;projects[defaultconfig][patch][1900574] = http://drupal.org/files/1900574.defaultconfig.undefinedindex_11.patch

; Recommended Modules
;projects[devel][version] = 1.5
;projects[devel][subdir] = contrib

;projects[distro_update][version] = 1.0-beta4
;projects[distro_update][subdir] = contrib

;projects[features_override][version] = 2.0-rc3
;projects[features_override][subdir] = contrib

;projects[uuid][version] = 1.0-alpha6
;projects[uuid][subdir] = contrib
