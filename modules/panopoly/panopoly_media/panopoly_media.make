; Panopoly Media Makefile

api = 2
core = 8.x

projects[dropzonejs][version] = 2.x-dev
projects[dropzonejs][subdir] = contrib

projects[entity_browser][version] = 2.1
projects[entity_browser][subdir] = contrib
projects[entity_browser][patch][2916053] = https://www.drupal.org/files/issues/bartik-entity-browser.patch
projects[entity_browser][patch][3160482] = https://www.drupal.org/files/issues/2021-03-02/error-with-untrustedcallbackexception-3160482-5.patch

projects[embed][version] = 1.0
projects[embed][subdir] = contrib

projects[entity_embed][version] = 1.0-beta2
projects[entity_embed][subdir] = contrib

projects[inline_entity_form][version] = 1.0-rc1
projects[inline_entity_form][subdir] = contrib

libraries[dropzone][download][type] = get
libraries[dropzone][download][url] = https://github.com/enyo/dropzone/archive/v5.1.1.tar.gz
