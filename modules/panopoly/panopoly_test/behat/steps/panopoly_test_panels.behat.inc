<?php

/**
 * @file
 * Provide Behat step-definitions for Panels.
 *
 * @todo This should become panels.behat.inc in the Panels module!
 */

use Drupal\DrupalExtension\Context\DrupalSubContextInterface;
use Drupal\DrupalDriverManager;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Behat sub-context for Panels.
 */
class PanelsSubContext extends RawDrupalContext implements DrupalSubContextInterface {

  /**
   * Contains the DrupalDriverManager.
   *
   * @var \Drupal\DrupalDriverManager
   */
  private $drupal;

  /**
   * Initializes context.
   */
  public function __construct(DrupalDriverManager $drupal) {
    $this->drupal = $drupal;
  }

  /**
   * Wait until the Panels IPE is activated.
   *
   * @When I wait for the Panels IPE to activate
   */
  public function waitForIpeToActivate() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Wait until the Panels IPE is deactivated.
   *
   * @When I wait for the Panels IPE to deactivate
   */
  public function waitForIpeToDeactivate() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Enable the Panels IPE if it's available on the current page.
   *
   * @When I customize this page with the Panels IPE
   */
  public function customizeThisPageIpe() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Open 'Change layout' dialog if it's available on the current page.
   *
   * @When I change layout with the Panels IPE
   */
  public function changeLayoutIpe() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Ensure the element identified by CSS selector is visible.
   *
   * @param string $selector
   *   The CSS selector.
   */
  protected function checkVisible($selector) {
    $this->getSession()->wait(5000, "jQuery('$selector').length > 0 && jQuery('$selector').get(0).offsetParent !== null");
  }

  /**
   * Creates a new widget in the Panels IPE.
   *
   * @When I create new :widget content in the Panels IPE
   */
  public function createNewContent($widget) {
    $page = $this->getSession()->getPage();
    $page->clickLink('Manage Content');
    $this->checkVisible(".ipe-block-picker-list.active");
    $page->clickLink('Create Content');
    $this->checkVisible(".ipe-category-picker-top.active");
    if ($create_link = $page->find('xpath', "//a[@data-block-type and div/h5/text() = '$widget']")) {
      $create_link->click();
    }
    else {
      throw new \Exception("Unable to find widget called '$widget'");
    }
    $this->checkVisible("form.block-content-form");
  }

  /**
   * Waits for IPE to load.
   *
   * @When I wait for the block form to load in the Panels IPE
   */
  public function waitForBlockFormInIpe() {
    $this->checkVisible("form.panels-ipe-block-plugin-form");
  }

}
