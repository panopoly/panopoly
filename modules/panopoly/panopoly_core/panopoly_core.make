; Panopoly Core Makefile

api = 2
core = 7.x

; Panels and Chaos Tools Magic

projects[ctools][version] = 1.x-dev
projects[ctools][subdir] = contrib
projects[ctools][download][type] = git
projects[ctools][download][revision] = b8671ef
projects[ctools][download][branch] = 7.x-1.x
projects[ctools][patch][1630820] = http://drupal.org/files/1630820-ctools-access-30.patch

projects[panels][version] = 3.x-dev
projects[panels][subdir] = contrib
projects[panels][download][type] = git
projects[panels][download][revision] = 9846b92
projects[panels][download][branch] = 7.x-3.x

projects[panels_breadcrumbs][version] = 2.x-dev
projects[panels_breadcrumbs][subdir] = contrib
projects[panels_breadcrumbs][download][type] = git
projects[panels_breadcrumbs][download][revision] = a8f265a
projects[panels_breadcrumbs][download][branch] = 7.x-2.x

projects[panelizer][version] = 3.x-dev
projects[panelizer][subdir] = contrib
projects[panelizer][download][type] = git
projects[panelizer][download][revision] = fb796a2 
projects[panelizer][download][branch] = 7.x-3.x

projects[fieldable_panels_panes][version] = 1.x-dev
projects[fieldable_panels_panes][subdir] = contrib
projects[fieldable_panels_panes][download][type] = git
projects[fieldable_panels_panes][download][revision] = 7fe67bc
projects[fieldable_panels_panes][download][branch] = 7.x-1.x

projects[pm_existing_pages][version] = 1.4
projects[pm_existing_pages][subdir] = contrib

projects[fape][version] = 1.x-dev
projects[fape][subdir] = contrib
projects[fape][download][type] = git
projects[fape][download][revision] = 1143ee2
projects[fape][download][branch] = 7.x-1.x

; Views Magic

projects[views][version] = 3.5
projects[views][subdir] = contrib

projects[views_autocomplete_filters][version] = 1.0-beta2
projects[views_autocomplete_filters][subdir] = contrib

projects[views_bulk_operations][version] = 3.1
projects[views_bulk_operations][subdir] = contrib

; The Usual Suspects

projects[pathauto][version] = 1.2
projects[pathauto][subdir] = contrib
projects[pathauto][patch][936222] = http://drupal.org/files/936222-pathauto-persist.patch

projects[token][version] = 1.4
projects[token][subdir] = contrib

projects[entity][version] = 1.x-dev
projects[entity][subdir] = contrib
projects[entity][download][type] = git
projects[entity][download][revision] = 681a20d1c8a8cb209b2ae7afa6121276f8fb9a2f
projects[entity][download][branch] = 7.x-1.x

projects[libraries][version] = 2.0
projects[libraries][subdir] = contrib

; Field modules

projects[date][version] = 2.6
projects[date][subdir] = contrib

projects[entityreference][version] = 1.0
projects[entityreference][subdir] = contrib

projects[field_group][version] = 1.1
projects[field_group][subdir] = contrib

projects[link][version] = 1.0
projects[link][subdir] = contrib

; Harness the Power of Features and Apps with Default Content

projects[apps][version] = 1.0-beta7
projects[apps][subdir] = contrib
projects[apps][patch][1790902] = http://drupal.org/files/1790902-check-last-modified-existing.patch

projects[features][version] = 2.0-beta1
projects[features][subdir] = contrib

projects[strongarm][version] = 2.0
projects[strongarm][subdir] = contrib

projects[defaultconfig][version] = 1.0-alpha9
projects[defaultconfig][subdir] = contrib

projects[defaultcontent][version] = 1.x-dev
projects[defaultcontent][subdir] = contrib
projects[defaultcontent][download][type] = git
projects[defaultcontent][download][revision] = d8806d8
projects[defaultcontent][download][branch] = 7.x-1.x
projects[defaultcontent][patch][1754428] = http://drupal.org/files/1754428-allow-node-export-alter.patch
projects[defaultcontent][patch][1757782] = http://drupal.org/files/1757782-cannot-import-menu-hierarchy-8.patch

; Recommended Modules
projects[devel][version] = 1.3
projects[devel][subdir] = contrib

projects[uuid][version] = 1.x-dev
projects[uuid][subdir] = contrib
projects[uuid][download][type] = git
projects[uuid][download][revision] = 4730c67
projects[uuid][download][branch] = 7.x-1.x
projects[uuid][patch][1605284] = http://drupal.org/files/1605284-define-types-for-tokens-6.patch
