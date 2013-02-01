; Panopoly Widgets Makefile

api = 2
core = 7.x

; Panopoly - Contrib - Fields

projects[tablefield][version] = 2.1
projects[tablefield][subdir] = contrib

projects[simple_gmap][version] = 1.0
projects[simple_gmap][subdir] = contrib

; Panopoly - Contrib - Widgets

projects[menu_block][version] = 2.3
projects[menu_block][subdir] = contrib

; Panopoly - Contrib - Files & Media

projects[file_entity][version] = 2.x-dev
projects[file_entity][subdir] = contrib
projects[file_entity][download][type] = git
projects[file_entity][download][revision] = 6d46953
projects[file_entity][download][branch] = 7.x-2.x

projects[media][version] = 2.x-dev
projects[media][subdir] = contrib
projects[media][download][type] = git
projects[media][download][revision] = 4a18a67 
projects[media][download][branch] = 7.x-2.x
projects[media][patch][1307054] = http://drupal.org/files/1307054-d7-2-alt-text-89.patch

projects[media_youtube][version] = 1.0-beta3
projects[media_youtube][subdir] = contrib
projects[media_youtube][patch][1812976] = http://drupal.org/files/1812976-1x-fix-against-b3.patch

projects[media_vimeo][version] = 1.0-beta5
projects[media_vimeo][subdir] = contrib
projects[media_vimeo][patch][1823078] = http://drupal.org/files/1823078-1x-fix.patch
