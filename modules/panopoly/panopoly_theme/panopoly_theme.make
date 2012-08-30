; Panopoly Theme Makefile

api = 2
core = 7.x

; Include the Ability to Detect Browsers 

projects[browscap][version] = 1.3
projects[browscap][subdir] = contrib

projects[browscap_ctools][version] = 1.0
projects[browscap_ctools][subdir] = contrib

; Sparkle up the Responsive Layout Builder

projects[layout][version] = 1.0-alpha3
projects[layout][subdir] = contrib

projects[gridbuilder][version] = 1.0-alpha2
projects[gridbuilder][subdir] = contrib

projects[json2][version] = 1.0
projects[json2][subdir] = contrib

libraries[json2][download][type] = get
libraries[json2][download][url] = https://github.com/douglascrockford/JSON-js/blob/master/json2.js
libraries[json2][revision] = fc535e9cc8fa78bbf45a85835c830e7f799a5084

; Summon the Power of Respond.js

projects[respondjs][version] = 1.1
projects[respondjs][subdir] = contrib

libraries[respondjs][download][type] = get
libraries[respondjs][download][url] = https://github.com/scottjehl/Respond/tarball/master

; Bundle a Few Panopoly Approved Themes

projects[responsive_bartik][version] = 1.x-dev
projects[responsive_bartik][type] = theme
projects[responsive_bartik][download][type] = git
projects[responsive_bartik][download][revision] = 7853fee
projects[responsive_bartik][download][branch] = 7.x-1.x


