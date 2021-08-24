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

# GitLab CI checks out the commit unattached from any branch. We need to
# put it in a branch for the Composer repository to work.
git checkout -b $CI_COMMIT_REF_NAME

# Create new Lando home directory within $CI_SHARED_DIR so that it can be
# mounted by the docker:dind service.
CI_SHARED_DIR=$(dirname $CI_PROJECT_DIR)
cp -r /home/lando $CI_SHARED_DIR/
cp .gitlab-ci/global-lando-config.yml $CI_SHARED_DIR/lando/.lando/config.yml
export HOME=$CI_SHARED_DIR/lando
cd $CI_SHARED_DIR

# Setup hostnames for Panopoly site.
echo $(getent hosts docker | awk '{ print $1 }') panopoly-2.docker | sudo tee -a /etc/hosts

# This is roughly equivalent to:
#
#   composer create-project panopoly/panopoly-composer-template:9.x-dev drupal --no-interaction --no-install
#
# We don't want to run composer here, because we want Lando to run it,
# so that, the PHP version used by Lando is used.
panopoly_header "Creating site to test..."
mkdir drupal
curl https://gitlab.com/panopoly/panopoly-composer-template/-/archive/9.x/panopoly-composer-template-9.x.tar.bz2 | tar -xj -C drupal --strip-components=1
cd drupal
# Use Lando to run composer.
# NOTE: the .lando.yml specially mounts the $CI_PROJECT_DIR as /src/panopoly
# in the appserver, so we can point the repository there.
cat $CI_PROJECT_DIR/.gitlab-ci/lando.yml \
	| sed -e "s,PHP_VERSION,$PHP_VERSION,g" \
	| sed -e "s,CI_PROJECT_DIR,$CI_PROJECT_DIR,g" > .lando.yml
[[ "$UPGRADE" != "no-upgrade" ]] && lando rebuild -y
lando start

# If doing an upgrade build, install the site we are upgrading from.
if [[ "$UPGRADE" != "no-upgrade" ]]; then
	panopoly_header "Installing Panopoly $UPGRADE to upgrade from..."
	lando composer require "panopoly/panopoly:$UPGRADE" --no-update
	lando composer update
	(cd web && lando drush si panopoly --db-url=mysql://drupal9:drupal9@database/drupal9 -y)
fi

lando composer config repositories.panopoly path /src/panopoly
lando composer require "panopoly/panopoly:dev-$CI_COMMIT_REF_NAME as 2.x-dev" --no-update
lando composer require drush/drush drupal/diff --no-update
lando composer require drupal/panopoly_widgets_table:2.x-dev --no-update
lando composer update
cd web

# Install (or upgrade) Drupal.
if [[ "$UPGRADE" = "no-upgrade" ]]; then
	panopoly_header "Installing Panopoly..."
	lando drush si panopoly --db-url=mysql://drupal9:drupal9@database/drupal9 -y
else
	panopoly_header "Upgrading Panopoly..."
	lando drush updb -y
	lando drush cr
fi

# Post installation setup.
lando drush en -y panopoly_test


# Install Panopoly Widgets modules
lando drush en -y panopoly_widgets_table

# Check if any modules are overridden.
panopoly_header "Checking for overridden features..."
lando drush en -y features diff
lando ssh -c 'cd profiles/contrib/panopoly && /app/vendor/bin/robo check:overridden'

# Check for code style issues.
panopoly_header "Checking for code style issues..."
lando ssh -c 'cd profiles/contrib/panopoly && /app/vendor/bin/robo phpcs'

# Perform static code analysis.
panopoly_header "Performing static code analysis..."
lando ssh -c 'cd profiles/contrib/panopoly && /app/vendor/bin/robo phpstan'

# Make symlink to the Drupal site so we can get our artifacts out.
ln -s $CI_SHARED_DIR/drupal $CI_PROJECT_DIR/drupal

# Run the Behat tests
panopoly_header "Starting Behat tests..."
BEHAT_CMD='cd profiles/contrib/panopoly/modules/panopoly/panopoly_test/behat && /app/vendor/bin/behat --rerun --config behat.lando.yml'
if ! lando ssh -c "$BEHAT_CMD" ; then
	echo "Failures detected. Re-running failed scenarios."
	lando ssh -c "$BEHAT_CMD"
fi

