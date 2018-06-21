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

