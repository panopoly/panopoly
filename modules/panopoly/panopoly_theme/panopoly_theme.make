; Panopoly Theme Makefile

api = 2
core = 8.x

; Radix Layouts
projects[radix_layouts][version] = 3.0-rc1
projects[radix_layouts][subdir] = contrib
projects[radix_layouts][patch][2875034] = https://www.drupal.org/files/issues/2875034-radix-layouts-drupal-8-3-1-panels-4-compatibility-3.0.patch

; Bundle a Few Panopoly Approved Themes

; projects[radix][version] = 1.x-dev
; projects[radix][type] = theme
; projects[radix][download][type] = git
; projects[radix][download][revision] = b873330
; projects[radix][download][branch] = 7.x-1.x
