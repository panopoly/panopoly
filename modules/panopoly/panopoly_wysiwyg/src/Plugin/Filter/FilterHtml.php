<?php

namespace Drupal\panopoly_wysiwyg\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\Filter\FilterHtml as CoreFilterHtml;

/**
 * Replacement for core FilterHtml that respects <!--break-->.
 *
 * @see https://www.drupal.org/node/2903733
 */
class FilterHtml extends CoreFilterHtml {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    // Split the text on <!--break-->, then do the normal processing and
    // re-join.
    $parts = explode('<!--break-->', $text, 2);
    foreach ($parts as &$part) {
      $part = parent::process($part, $langcode)->getProcessedText();
    }
    $text = count($parts) > 1 ? implode('<!--break-->', $parts) : $parts[0];
    return new FilterProcessResult($text);
  }

}
