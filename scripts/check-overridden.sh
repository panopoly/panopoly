#!/bin/bash

: ${DRUSH:=drush}
: ${DRUSH_ARGS:=}

PANOPOLY_FEATURES="panopoly_core panopoly_demo panopoly_images panopoly_pages panopoly_theme panopoly_users panopoly_widgets panopoly_wysiwyg"

# TODO: We should make sure that 'diff' is downloaded first!
$DRUSH $DRUSH_ARGS en -y diff features

OVERRIDDEN=0
FIRST=1
for panopoly_feature in $PANOPOLY_FEATURES; do
  if [ x$FIRST = x1 ]; then
    # Run the first features-diff twice.
    $DRUSH $DRUSH_ARGS features-diff $panopoly_feature >/dev/null 2>&1
    FIRST=0
  fi

  echo "Checking $panopoly_feature..."
  if $DRUSH $DRUSH_ARGS features-diff $panopoly_feature 2>&1 | grep -v 'Active config matches stored config'; then
    OVERRIDDEN=1
  fi
done

exit $OVERRIDDEN
