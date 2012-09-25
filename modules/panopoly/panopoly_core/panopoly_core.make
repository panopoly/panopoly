; Panopoly Core Makefile

api = 2
core = 7.x

; Panels and Chaos Tools Magic

projects[ctools][version] = 1.2
projects[ctools][subdir] = contrib
projects[ctools][patch][1739718] = http://drupalcode.org/project/ctools.git/commitdiff_plain/03f10455c9ca38c152b33282f66c9bedad577b21
projects[ctools][patch][1494860] = http://drupalcode.org/project/ctools.git/commitdiff_plain/7cd8d95d65edecf82f73b6df2a073c44d05074b8
projects[ctools][patch][1788088] = http://drupalcode.org/project/ctools.git/commitdiff_plain/909290dc9482374a30943db49ca9dac998fc3a5e
projects[ctools][patch][1774434] = http://drupalcode.org/project/ctools.git/commitdiff_plain/e7b85db971cf33ade21252d1f55aa88de3301ce7
projects[ctools][patch][1294478] = http://drupal.org/files/dynamic-modal-1294478-34.patch
projects[ctools][patch][1754770] = http://drupal.org/files/1754770-do-not-query-block-table-if-no-block-module.patch

projects[panels][version] = 3.3
projects[panels][subdir] = contrib
projects[panels][patch][7a8bd4e] = http://drupalcode.org/project/panels.git/commitdiff_plain/7a8bd4e
projects[panels][patch][1572202] = http://drupalcode.org/project/panels.git/commitdiff_plain/d4e2e93
projects[panels][patch][1772834] = http://drupalcode.org/project/panels.git/commitdiff_plain/451f8d4
projects[panels][patch][1788170] = http://drupalcode.org/project/panels.git/commitdiff_plain/465ba82
projects[panels][patch][1735336] = http://drupal.org/files/1735336-fix-draghandle-width-css.patch
projects[panels][patch][1744824] = http://drupal.org/files/1744724-confirmation-on-leave-page-with-changes.patch

projects[panels_breadcrumbs][version] = 2.x-dev
projects[panels_breadcrumbs][subdir] = contrib
projects[panels_breadcrumbs][download][type] = git
projects[panels_breadcrumbs][download][revision] = 531f589
projects[panels_breadcrumbs][download][branch] = 7.x-2.x

projects[panelizer][version] = 3.0-rc1
projects[panelizer][subdir] = contrib
projects[panelizer][patch][1238e8c] = http://drupalcode.org/project/panelizer.git/commitdiff_plain/1238e8c
projects[panelizer][patch][1572202] = http://drupalcode.org/project/panelizer.git/commitdiff_plain/665c089
projects[panelizer][patch][6ba90d3] = http://drupalcode.org/project/panelizer.git/commitdiff_plain/6ba90d3
projects[panelizer][patch][1719372] = http://drupalcode.org/project/panelizer.git/commitdiff_plain/55c4582
projects[panelizer][patch][1655296] = http://drupal.org/files/1655296-allow-additional-panelizer-tab-pages_1.patch

projects[fieldable_panels_panes][version] = 1.2
projects[fieldable_panels_panes][subdir] = contrib
projects[fieldable_panels_panes][patch][1536944] = http://drupal.org/files/Fieldable_Panels_Pane-translatable_panes-1536944-11.patch

projects[pm_existing_pages][version] = 1.4
projects[pm_existing_pages][subdir] = contrib

projects[fape][version] = 1.1
projects[fape][subdir] = contrib
projects[fape][patch][1607652] = http://drupalcode.org/project/fape.git/commitdiff_plain/c8c7c4d
projects[fape][patch][1785056] = http://drupalcode.org/project/fape.git/commitdiff_plain/1143ee2

; Views Magic

projects[views][version] = 3.5
projects[views][subdir] = contrib

projects[views_autocomplete_filters][version] = 1.0-beta1
projects[views_autocomplete_filters][subdir] = contrib

projects[views_bulk_operations][version] = 3.0
projects[views_bulk_operations][subdir] = contrib


; The Usual Suspects

projects[pathauto][version] = 1.2
projects[pathauto][subdir] = contrib
projects[pathauto][patch][936222] = http://drupal.org/files/936222-pathauto-persist.patch

projects[token][version] = 1.3
projects[token][subdir] = contrib

projects[entity][version] = 1.0-rc3
projects[entity][subdir] = contrib

projects[libraries][version] = 2.0
projects[libraries][subdir] = contrib

projects[devel][version] = 1.3
projects[devel][subdir] = contrib

; Harness the Power of Features and Apps with Default Content

projects[apps][version] = 1.0-beta7
projects[apps][subdir] = contrib
projects[apps][patch][1790902] = http://drupal.org/files/1790902-check-last-modified-existing.patch

projects[features][version] = 1.0
projects[features][subdir] = contrib

projects[strongarm][version] = 2.0
projects[strongarm][subdir] = contrib

projects[defaultcontent][version] = 1.0-alpha6
projects[defaultcontent][subdir] = contrib
projects[defaultcontent][patch][1446714] = http://drupalcode.org/project/defaultcontent.git/commitdiff_plain/051bae5
projects[defaultcontent][patch][1446714] = http://drupalcode.org/project/defaultcontent.git/commitdiff_plain/5ca9ecd
projects[defaultcontent][patch][1515024] = http://drupalcode.org/project/defaultcontent.git/commitdiff_plain/d8806d8

projects[uuid][version] = 1.x-dev
projects[uuid][subdir] = contrib
projects[uuid][download][type] = git
projects[uuid][download][revision] = 4730c67
projects[uuid][download][branch] = 7.x-1.x
