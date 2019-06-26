;
; GENERATED FILE - DO NOT EDIT!
;

; Panopoly Core Makefile

api = 2
core = 8.x

; Panels and Chaos Tools Magic

projects[ctools][version] = 3.0
projects[ctools][subdir] = contrib
projects[ctools][patch][2657060] = https://www.drupal.org/files/issues/ctools-exposed-filter-block-config-2657060-56.patch

projects[page_manager][version] = 4.x-dev
projects[page_manager][subdir] = contrib
projects[page_manager][patch][2960739] = https://www.drupal.org/files/issues/2019-06-20/page_manager-layout-builder-variant-2960739-41.patch

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

; Panopoly Media Makefile

api = 2
core = 8.x

projects[dropzonejs][version] = 2.x-dev
projects[dropzonejs][subdir] = contrib

projects[entity_browser][version] = 2.0-alpha3
projects[entity_browser][subdir] = contrib

projects[embed][version] = 1.0
projects[embed][subdir] = contrib

projects[entity_embed][version] = 1.0-beta2
projects[entity_embed][subdir] = contrib

projects[media_entity_browser][version] = 2.0-alpha1
projects[media_entity_browser][subdir] = contrib

projects[inline_entity_form][version] = 1.0-rc1
projects[inline_entity_form][subdir] = contrib

projects[video_embed_field][version] = 2.x-dev
projects[video_embed_field][subdir] = contrib
projects[video_embed_field][patch][2973246] = https://www.drupal.org/files/issues/2018-05-30/video_embed_field-youtube_nocookie-2973246-21.patch

libraries[dropzone][download][type] = get
libraries[dropzone][download][url] = https://github.com/enyo/dropzone/archive/v5.1.1.tar.gz


; Panopoly Theme Makefile

api = 2
core = 8.x

; Radix Layouts
projects[radix_layouts][version] = 4.0
projects[radix_layouts][subdir] = contrib

; Bundle a Few Panopoly Approved Themes

; projects[radix][version] = 1.x-dev
; projects[radix][type] = theme
; projects[radix][download][type] = git
; projects[radix][download][revision] = b873330
; projects[radix][download][branch] = 7.x-1.x

; Panopoly Core Makefile

api = 2
core = 8.x

; Panels and Chaos Tools Magic

projects[ckeditor_drupalbreaks][version] = 1.x-dev
projects[ckeditor_drupalbreaks][subdir] = contrib

projects[colorbutton][version] = 1.1
projects[colorbutton][subdir] = contrib

projects[editor_advanced_link][version] = 1.4
projects[editor_advanced_link][subdir] = contrib

projects[fakeobjects][version] = 1.0
projects[fakeobjects][subdir] = contrib

projects[linkit][version] = 5.0-beta8
projects[linkit][subdir] = contrib

projects[panelbutton][version] = 1.2
projects[panelbutton][subdir] = contrib

libraries[colorbutton][download][type] = get
libraries[colorbutton][download][url] = https://download.ckeditor.com/colorbutton/releases/colorbutton_4.11.3.zip

libraries[fakeobjects][download][type] = get
libraries[fakeobjects][download][url] = https://download.ckeditor.com/fakeobjects/releases/fakeobjects_4.11.3.zip

libraries[panelbutton][download][type] = get
libraries[panelbutton][download][url] = https://download.ckeditor.com/panelbutton/releases/panelbutton_4.11.3.zip

libraries[tabletoolstoolbar][download][type] = get
libraries[tabletoolstoolbar][download][url] = https://download.ckeditor.com/tabletoolstoolbar/releases/tabletoolstoolbar_0.0.1.zip


