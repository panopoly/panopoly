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
cp -a $TRAVIS_BUILD_DIR/hacks/panopoly_test_panels.behat.inc steps/

# Make the Travis tests repos agnostic by injecting drupal_root with BEHAT_PARAMS
# @todo Consider using drupal_ti_replace_behat_vars instead to use $ in
#       behat.yml.travis directly.
BEHAT_PARAMS='{"extensions":{"Drupal\\DrupalExtension":{"drupal":{"drupal_root":"DRUPAL_TI_DRUPAL_DIR"}}}}'
BEHAT_PARAMS=`echo $BEHAT_PARAMS | sed -e s#DRUPAL_TI_DRUPAL_DIR#$DRUPAL_TI_DRUPAL_DIR#`
export BEHAT_PARAMS

# If this isn't an upgrade, we test if any features are overridden.
if [[ "$UPGRADE" == none ]]
then
	echo @todo remove commented out part.
#	"$TRAVIS_BUILD_DIR"/scripts/check-overridden.sh
fi

ARGS=( $DRUPAL_TI_BEHAT_ARGS )
# First, run all the tests in Firefox.
./bin/behat --config behat.travis.yml "${ARGS[@]}"

# Then run some Chrome-only tests.
./bin/behat --config behat.travis.yml -p chrome "${ARGS[@]}"
