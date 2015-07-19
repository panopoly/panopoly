#!/bin/bash
# @file
# Behat integration - Script step.

set -e $DRUPAL_TI_DEBUG

# Ensure we are in the right directory.
cd "$DRUPAL_TI_DRUPAL_DIR"

# Now go to the local behat tests, being within the module installation is
# needed for example for the drush runner.
cd "$DRUPAL_TI_BEHAT_DIR"

panopoly_header Running tests

panopoly_header Hacking panopoly_test_panels.behat.inc ...
rm -f steps/panopoly_test_panels.behat.inc
mv -f $TRAVIS_BUILD_DIR/hacks/panopoly_test_panels.behat.inc steps/
mv -f $TRAVIS_BUILD_DIR/hacks/behat.travis.yml.dist .

# If this isn't an upgrade, we test if any features are overridden.
if [[ "$UPGRADE" == none ]]
then
	echo @todo remove commented out part.
#	"$TRAVIS_BUILD_DIR"/scripts/check-overridden.sh
fi

# This replaces environment vars from $DRUPAL_TI_BEHAT_YML into 'behat.yml'.
drupal_ti_replace_behat_vars

ARGS=( $DRUPAL_TI_BEHAT_ARGS )

# First, run all the tests in Firefox.
./bin/behat "${ARGS[@]}"

# Then run some Chrome-only tests.
./bin/behat -p chrome "${ARGS[@]}"
