#!/bin/bash

set -x
set -e

#
# Prints a message about the section of the script.
#
function panopoly_header() {
	echo
	echo "** $@"
	echo
}

# Switch to the composer.json/lock files that will work with old PHP.
cp composer.json-travis composer.json
cp composer.lock-travis composer.lock

# Create new Lando home directory within $CI_SHARED_DIR so that it can be
# mounted by the docker:dind service.
CI_SHARED_DIR=$(dirname $CI_PROJECT_DIR)
cp -r /home/lando $CI_SHARED_DIR/
cp .gitlab-ci/global-lando-config.yml $CI_SHARED_DIR/lando/.lando/config.yml
export HOME=$CI_SHARED_DIR/lando
cd $CI_SHARED_DIR

# Setup hostnames for Panopoly site.
echo $(getent hosts docker | awk '{ print $1 }') panopoly-1.docker | sudo tee -a /etc/hosts

# If doing an upgrade build, install the site we are upgrading from.
if [[ "$UPGRADE" != "no-upgrade" ]]; then
	panopoly_header "Creating and installing Panopoly $UPGRADE site to upgrade from..."
	curl https://ftp.drupal.org/files/projects/panopoly-$UPGRADE-core.tar.gz --output panopoly-$UPGRADE-core.tar.gz
	tar -xzf panopoly-$UPGRADE-core.tar.gz
	cd panopoly-$UPGRADE
	cat $CI_PROJECT_DIR/.gitlab-ci/lando.yml | sed -e "s,PHP_VERSION,$PHP_VERSION,g" > .lando.yml
	lando start
	lando drush si panopoly --db-url=mysql://drupal7:drupal7@database/drupal7 -y
	lando stop
	cd $CI_SHARED_DIR
fi

# Create the Drupal site.
panopoly_header "Creating site to test..."
mkdir drupal
cd drupal
cat $CI_PROJECT_DIR/.gitlab-ci/lando.yml | sed -e "s,PHP_VERSION,$PHP_VERSION,g" > .lando.yml
# NOTE: the .lando.local.yml specially mounts the $CI_PROJECT_DIR as /app/profiles/panopoly
# in the appserver.
cat $CI_PROJECT_DIR/.gitlab-ci/lando.local.yml | sed -e "s,CI_PROJECT_DIR,$CI_PROJECT_DIR,g" > .lando.local.yml
[[ "$UPGRADE" != "no-upgrade" ]] && lando rebuild -y
lando start
lando drush make -y profiles/panopoly/drupal-org-core.make --prepare-install temp
cp -rT temp/ ./ && rm -rf temp
lando ssh -c 'cd profiles/panopoly && composer install'
lando ssh -c 'cd profiles/panopoly && ./vendor/bin/robo build:dependencies'
mkdir -p sites/default/private/files
mkdir -p sites/default/private/temp

if [[ "$UPGRADE" = "no-upgrade" ]]; then
	# Check to see if the .make file will work on Drupal.org.
	lando ssh -c 'drush dl -y drupalorg_drush-7.x-1.x-dev --destination=$HOME/.drush && drush cc drush'
	lando ssh -c 'cd profiles/panopoly && ./vendor/bin/robo check:makefile'
fi

# Install (or upgrade) Drupal.
if [[ "$UPGRADE" = "no-upgrade" ]]; then
	panopoly_header "Installing Panopoly..."
	lando drush si panopoly --db-url=mysql://drupal7:drupal7@database/drupal7 -y
else
	# Since we're not doing the install (which downloads panopoly_demo via apps),
	# we need to download it manually here.
	lando drush dl panopoly_demo-1.x-dev

	panopoly_header "Upgrading Panopoly..."
	cp "$CI_SHARED_DIR/panopoly-$UPGRADE/sites/default/settings.php" sites/default/
	lando drush cc all
	lando drush updb -y
fi

# Post installation setup.
lando drush vset -y file_private_path "sites/default/private/files"
lando drush vset -y file_temporary_path "sites/default/private/temp"
lando drush en -y panopoly_test

# Check if any modules are overridden.
panopoly_header "Checking for overridden features..."
lando drush en -y features diff
lando ssh -c 'cd profiles/panopoly && ./vendor/bin/robo check:overridden'

# Make symlink to the Drupal site so we can get our artifacts out.
ln -s $CI_SHARED_DIR/drupal $CI_PROJECT_DIR/drupal

# Run the Behat tests
panopoly_header "Starting Behat tests..."
BEHAT_CMD='cd profiles/panopoly/modules/panopoly/panopoly_test/behat && /app/profiles/panopoly/vendor/bin/behat --rerun --config behat.lando.yml'
if ! lando ssh -c "$BEHAT_CMD" ; then
	echo "Failures detected. Re-running failed scenarios."
	lando ssh -c "$BEHAT_CMD"
fi

