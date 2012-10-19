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
projects[ctools][patch][1197582] = http://drupalcode.org/project/ctools.git/commitdiff_plain/b2c7635abf502e66b108bb64ecbfb4166596b613
projects[ctools][patch][1754770] = http://drupalcode.org/project/ctools.git/commitdiff_plain/d6157d1a4cdc787b6c88c8802ce3026a17de62cf

projects[panels][version] = 3.3
projects[panels][subdir] = contrib
projects[panels][patch][7a8bd4e] = http://drupalcode.org/project/panels.git/commitdiff_plain/7a8bd4e
projects[panels][patch][1572202] = http://drupalcode.org/project/panels.git/commitdiff_plain/d4e2e93
projects[panels][patch][1772834] = http://drupalcode.org/project/panels.git/commitdiff_plain/451f8d4
projects[panels][patch][1788170] = http://drupalcode.org/project/panels.git/commitdiff_plain/465ba82
projects[panels][patch][86c216e] = http://drupalcode.org/project/panels.git/commitdiff_plain/86c216e
projects[panels][patch][1801422] = http://drupalcode.org/project/panels.git/commitdiff_plain/fcca8e3
projects[panels][patch][1735336] = http://drupalcode.org/project/panels.git/commitdiff_plain/f533a26
projects[panels][patch][1797298] = http://drupalcode.org/project/panels.git/commitdiff_plain/42978af
projects[panels][patch][1744824] = http://drupalcode.org/project/panels.git/commitdiff_plain/f6f7049
projects[panels][patch][1772846] = http://drupalcode.org/project/panels.git/commitdiff_plain/4e636d7

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
projects[panelizer][patch][2fb80ae] = http://drupalcode.org/project/panelizer.git/commitdiff_plain/2fb80ae
projects[panelizer][patch][3fdacfd] = http://drupalcode.org/project/panelizer.git/commitdiff_plain/3fdacfd
projects[panelizer][patch][1655296] = http://drupal.org/files/1655296-allow-additional-panelizer-tab-pages_1.patch

projects[fieldable_panels_panes][version] = 1.2
projects[fieldable_panels_panes][subdir] = contrib
projects[fieldable_panels_panes][patch][1536944] = http://drupal.org/files/Fieldable_Panels_Pane-translatable_panes-1536944-11.patch
projects[fieldable_panels_panes][patch][1818132] = http://drupal.org/files/1818132-prevent-uuids.patch

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

projects[views_autocomplete_filters][version] = 1.0-beta1
projects[views_autocomplete_filters][subdir] = contrib

projects[views_bulk_operations][version] = 3.0
projects[views_bulk_operations][subdir] = contrib


; The Usual Suspects

projects[pathauto][version] = 1.2
projects[pathauto][subdir] = contrib
projects[pathauto][patch][936222] = http://drupal.org/files/936222-pathauto-persist.patch

projects[token][version] = 1.4
projects[token][subdir] = contrib

projects[entity][version] = 1.0-rc3
projects[entity][subdir] = contrib

projects[libraries][version] = 2.0
projects[libraries][subdir] = contrib

; Harness the Power of Features and Apps with Default Content

projects[apps][version] = 1.0-beta7
projects[apps][subdir] = contrib
projects[apps][patch][1790902] = http://drupal.org/files/1790902-check-last-modified-existing.patch

projects[features][version] = 1.0
projects[features][subdir] = contrib

projects[strongarm][version] = 2.0
projects[strongarm][subdir] = contrib

projects[defaultconfig][version] = 1.0-alpha8
projects[defaultconfig][subdir] = contrib
projects[defaultconfig][patch][1800560] = http://drupal.org/files/1800560-load-features-map-export.patch
projects[defaultconfig][patch][1813278] = http://drupal.org/files/1813278-2-only-remove-components-exported-by-defaultconfig.patch

projects[defaultcontent][version] = 1.x-dev
projects[defaultcontent][subdir] = contrib
projects[defaultcontent][download][type] = git
projects[defaultcontent][download][revision] = d8806d8
projects[defaultcontent][download][branch] = 7.x-1.x

projects[uuid][version] = 1.x-dev
projects[uuid][subdir] = contrib
projects[uuid][download][type] = git
projects[uuid][download][revision] = 4730c67
projects[uuid][download][branch] = 7.x-1.x
projects[uuid][patch][1605284] = http://drupal.org/files/1605284-define-types-for-tokens-6.patch
