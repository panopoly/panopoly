<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Event\StepEvent,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalContext;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends DrupalContext
{
  /**
   * Initializes context.
   * Every scenario gets it's own context object.
   *
   * @param array $parameters context parameters (set them up through behat.yml)
   */
  public function __construct(array $parameters) {
    // Initialize your context here
    $this->useContext('panels', new PanelsSubContext());
  }

//
// Place your definition and hook methods here:
//
//    /**
//     * @Given /^I have done something with "([^"]*)"$/
//     */
//    public function iHaveDoneSomethingWith($argument)
//    {
//        doSomethingWith($argument);
//    }
//

  /**
   * @AfterStep @javascript
   *
   * After every step in a @javascript scenario, we want to wait for AJAX
   * loading to finish.
   */
  public function afterStep(StepEvent $event) {
    if ($event->getResult() === 0) {
      $this->iWaitForAJAX();
    }
  }

  /**
   * @Given /^Panopoly magic live previews are disabled$/
   *
   * Disable live previews via Panopoly Magic.
   */
  public function disablePanopolyMagicLivePreview() {
    $this->getDriver('drush')->vset('panopoly_magic_live_preview 0 --yes');
  }

  /**
   * @Given /^(?:|I )wait(?:| for) (\d+) seconds?$/
   *
   * Wait for the given number of seconds. ONLY USE FOR DEBUGGING!
   */
  public function iWaitForSeconds($arg1) {
    sleep($arg1);
  }

  /**
   * @Given /^(?:|I )wait for AJAX loading to finish$/
   *
   * Wait for the jQuery AJAX loading to finish. ONLY USE FOR DEBUGGING!
   */
  public function iWaitForAJAX() {
    $this->getSession()->wait(5000, 'jQuery.active === 0');
  }
}
