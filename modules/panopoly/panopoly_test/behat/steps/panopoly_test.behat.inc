<?php
/**
 * @file
 * Provide Behat step-definitions for generic Panopoly tests.
 */

use Drupal\DrupalExtension\Context\DrupalSubContextInterface;
use Drupal\DrupalDriverManager;
use Drupal\Component\Utility\Random;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Drupal\DrupalExtension\Hook\Scope\AfterUserCreateScope;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use WebDriver\Exception\NoAlertOpenError;

class TestSubContext extends RawDrupalContext implements DrupalSubContextInterface {

  /**
   * Contains the DrupalDriverManager.
   *
   * @var \Drupal\DrupalDriverManager
   */
  private $drupal = NULL;

  /**
   * Tracks if we're in a Javascript scenario or not.
   *
   * @var bool
   */
  private $javascript = FALSE;

  /**
   * Contains the name of the currently selected iframe.
   *
   * @var string
   */
  private $iframe = NULL;

  /**
   * An array of Drupal users created by other contexts.
   *
   * @var array
   */
  protected $external_users = array();

  /**
   * Keep track of files added by tests so they can be cleaned up.
   *
   * @var array
   */
  protected $files = array();

  /**
   * Set to TRUE after the window has been resized.
   */
  protected $window_resized = FALSE;

  /**
   * Store variables so they can be reset to previous values.
   *
   * @var array
   */
  protected $configVariables = array();

  const PANOPOLY_BEHAT_FLAG_PHP_NOTICES_OFF = 0;
  const PANOPOLY_BEHAT_FLAG_PHP_NOTICES_PRINT = 1;
  const PANOPOLY_BEHAT_FLAG_PHP_NOTICES_FAIL = 2;

  /**
   * Initializes context.
   */
  public function __construct(DrupalDriverManager $drupal) {
    $this->drupal = $drupal;
  }

  /**
   * Get a region by name.
   *
   * @param string $region
   *   The name of the region from the behat.yml file.
   *
   * @return Behat\Mink\Element\Element
   *   An element representing the region.
   */
  public function getRegion($region) {
    $session = $this->getSession();
    $regionObj = $session->getPage()->find('region', $region);
    if (!$regionObj) {
      throw new \Exception(sprintf('No region "%s" found on the page %s.', $region, $session->getCurrentUrl()));
    }
    return $regionObj;
  }

  /**
   * Save existing preview configurations.
   *
   * @BeforeScenario @api
   */
  public function saveConfig() {
    $this->configVariables['panopoly_magic_live_preview'] = variable_get('panopoly_magic_live_preview', 'not set');
    $this->configVariables['panopoly_magic_pane_add_preview'] = variable_get('panopoly_magic_pane_add_preview', 'not set');
    variable_set('panopoly_magic_live_preview', 0);
    variable_set('panopoly_magic_pane_add_preview', PANOPOLY_ADD_PREVIEW_SINGLE); // 3.
  }

  /**
   * Restore saved preview configurations or other variables.
   *
   * @AfterScenario @api
   */
  public function restoreConfig() {
    if (count($this->configVariables) > 0) {
      foreach ($this->configVariables as $key => $value) {
        if ($value === 'not set') {
          variable_del($key);
        }
        else {
          variable_set($key, $value);
        }
        unset($this->configVariables[$key]);
      }
    }
  }

  /**
   * Set a variable to mark the current scenario as using javascript.
   *
   * @BeforeScenario @javascript
   */
  public function setJavascript() {
    $this->javascript = true;
  }

  /**
   * @BeforeSuite
   *
   * If we will flag PHP errors, clear all of them before we run a suite.
   */
  public static function clearPhpBehatNoticeLogs() {
    if (!empty(getenv('PANOPOLY_BEHAT_FLAG_PHP_NOTICES'))) {
      db_delete('watchdog')
        ->condition('type', array('php', 'behat'), 'IN')
        ->execute();
      $local_file_path = getenv('PANOPOLY_BEHAT_SCREENSHOT_PATH');
      if (empty($local_file_path)) {
        print "Environment variable PANOPOLY_BEHAT_SCREENSHOT_PATH is not set, unable to save errors\n";
      }
      elseif (!is_dir($local_file_path)) {
        print "Directory $local_file_path does not exist, unable to save errors\n";
      }
      else {
        $file_location = "$local_file_path/behat-php-errors.txt";
        if (@file_put_contents($file_location, "Scenario|Time|Message\r\n") !== FALSE) {
          print "PHP errors will be saved to $file_location\n";
        }
        else {
          print "Unable to save errors\n";
        }
      }
    }
  }

  /**
   * Output any PHP notices that were logged in the scenario.
   *
   * @AfterScenario
   */
  public function flagPhpScenarioErrors(AfterScenarioScope $scope) {
    $flagPhp = getenv('PANOPOLY_BEHAT_FLAG_PHP_NOTICES');
    if (!empty($flagPhp)) {
      $scenarioName = $scope->getFeature()->getTitle();
      $result = db_select('watchdog', 'w')
        ->fields('w', [])
        ->condition('w.type', 'php', '=')
        ->execute();

      $errors = [];
      foreach ($result as $entry) {
        $variables = unserialize($entry->variables);
        $time = date('Ymd-Hi', $entry->timestamp);
        $errors[] = "$scenarioName|$time|" . strip_tags(t($entry->message, $variables));
      }

      if (!empty($errors)) {
        $message = implode("\r\n", $errors);
        print "$message\n";
        // Write the error message(s) to a file.
        $local_file_path = getenv('PANOPOLY_BEHAT_SCREENSHOT_PATH');
        if (empty($local_file_path)) {
          print "Environment variable PANOPOLY_BEHAT_SCREENSHOT_PATH is not set, unable to save errors\n";
        }
        else if (!is_dir($local_file_path)) {
          print "Directory $local_file_path does not exist, unable to save errors\n";
        }
        else {
          $file_location = "$local_file_path/behat-php-errors.txt";
          if (@file_put_contents($file_location, $message . "\r\n", FILE_APPEND) !== FALSE) {
            print "PHP errors saved to $file_location\n";
          }
          else {
            print "Unable to save errors\n";
          }
        }
        // Clear the log for the next scenario.
        db_update('watchdog')
          ->fields(array('type' => 'behat'))
          ->condition('type', 'php')
          ->execute();
        if ($flagPhp == self::PANOPOLY_BEHAT_FLAG_PHP_NOTICES_FAIL) {
          throw new \Exception("PHP errors were logged. See scenario output for details.");
        }
      }
    }
  }

  /**
   * Fail the suite if any PHP notices are logged.
   *
   * @AfterSuite
   */
  public static function flagPhpSuiteErrors() {
    if (getenv('PANOPOLY_BEHAT_FLAG_PHP_NOTICES') != self::PANOPOLY_BEHAT_FLAG_PHP_NOTICES_OFF) {
      $number_of_rows = db_select('watchdog', 'w')
        ->fields('w', [])
        ->condition('w.type', 'behat', '=')
        ->countQuery()
        ->execute()
        ->fetchField();

      if ($number_of_rows > 0) {
        print "PHP errors were logged. See scenario output for details.\n";
      }
    }
  }

  /**
   * Resize the window before first Javascript scenarios.
   *
   * @BeforeScenario @javascript
   */
  public function resizeWindow($event) {
    if (!$this->window_resized) {
      $session = $this->getSession();
      if (!$session->isStarted()) {
        $session->start();
      }

      $dimensions = getenv('PANOPOLY_BEHAT_WINDOW_SIZE');
      if (!empty($dimensions)) {
        list ($width, $height) = explode('x', $dimensions);
        $session->resizeWindow((int) $width, (int) $height, 'current');
      }
      else {
        $session->getDriver()->maximizeWindow();
      }

      $this->window_resized = TRUE;
    }
  }

  /**
   * Unsets the variable marking the current scenario as using javascript.
   *
   * @AfterScenario @javascript
   */
  public function unsetJavascript() {
    $this->javascript = false;
  }

  /**
   * Configure a private files path if one isn't already configured.
   *
   * @BeforeScenario @api&&@drupal_private_files
   */
  public function configurePrivateFiles($event) {
    $this->configVariables['file_private_path'] = variable_get('file_private_path', 'not set');
    if ($this->configVariables['file_private_path'] !== 'not set') {
      $file_public_path = variable_get('file_public_path', conf_path() . '/files');
      if (empty($file_public_path)) {
        throw new \Exception('Files must be configured for @drupal_private_files tests to work!');
      }
      // Create and setup the private path.
      $file_private_path = $file_public_path . '/' . 'private';
      variable_set('file_private_path', $file_private_path);
    }
  }

  /**
   * After every step in a @javascript scenario, we want to wait for AJAX
   * loading to finish.
   *
   * @AfterStep
   */
  public function afterStepWaitForJavascript($event) {
    if (isset($this->javascript) && $this->javascript) {
      $text = $event->getStep()->getText();
      if (preg_match('/(follow|press|click|submit|viewing|visit|reload|attach)/i', $text)) {
        if (empty($this->iframe)) {
          $this->iWaitForAjax();
        } else {
          // For whatever reason, the above isn't very accurate inside iframes,
          // and can sometimes cause "Cannot find context with specified id" error.
          sleep(3);
        }
      }
    }
  }

  /**
   * Explicitly take a screenshot.
   *
   * @Given I take a screenshot
   * @Given I take a screenshot with the title :title
   */
  public function takeScreenshot($title = 'screenshot') {
    static $screenshot_count = 0;
    $driver = $this->getSession()->getDriver();

    // Get the screenshot if the driver supports it.
    try {
      $image = $driver->getScreenshot();
    }
    catch (UnsupportedDriverActionException $e) {
      return;
    }

    // Set default title.
    $title = sprintf(
      '%s_%s_%s',
      date("Ymd-Hi"),
      preg_replace('/[^a-zA-Z0-9\._-]/', '_', $title),
      (++$screenshot_count)
    );

    // Save the file locally, if a path is available. Variable can be set in
    // .travis.yml or in local working environment.
    $local_screenshot_path =  getenv('PANOPOLY_BEHAT_SCREENSHOT_PATH');
    if (empty($local_screenshot_path)) {
      print "Environment variable PANOPOLY_BEHAT_SCREENSHOT_PATH is not set, unable to save screenshot\n";
    }
    else if (!is_dir($local_screenshot_path)) {
      print "Directory $local_screenshot_path does not exist, unable to save screenshot\n";
    }
    else {
      $file_location =  "$local_screenshot_path/$title.png";
      if (@file_put_contents($file_location, $image) !== FALSE) {
        print "Screenshot saved to $file_location\n";
      }
      else {
        print "Unable to save screenshot\n";
      }
    }

    // Upload the image to Imgur if a client ID is available.
    $imgur_client_id = getenv('IMGUR_CLIENT_ID');
    if ($imgur_client_id) {
      $url = $this->uploadScreenshot($image, $title, $imgur_client_id);
      print "Screenshot uploaded to $url\n";
    }
    else {
      print "Environment variable IMGUR_CLIENT_ID not set, unable to upload screenshot\n";
    }
  }

  /**
   * After a failed step, upload a screenshot.
   *
   * @AfterStep
   */
  public function afterStepTakeScreenshot($event) {
    if ($event->getTestResult()->getResultCode() === TestResult::FAILED)  {
      $this->takeScreenshot($event->getStep()->getText());
    }
  }

  /**
   * Uploads a screenshot to imgur.
   *
   * @param string $image
   *   The image data.
   * @param string $title
   *   The image title.
   * @param string $imgur_client_id
   *   The Client ID for your application registered with imgur
   *
   * @see https://api.imgur.com/oauth2
   */
  protected function uploadScreenshot($image, $title, $imgur_client_id) {
    // @todo This should use Guzzle rather than curl directly
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.imgur.com/3/image",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "image=".urlencode(base64_encode($image))."&title=$title",
      CURLOPT_HTTPHEADER => array(
        "authorization: Client-ID $imgur_client_id",
        "cache-control: no-cache",
        "content-type: application/x-www-form-urlencoded"
      ),
    ));

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    $payload = json_decode($response);
    if ($error || property_exists($payload, 'error')) {
      return;
    }

    return $payload->data->link;
  }

  /**
   * Convert escaped characters in arguments.
   *
   * @Transform :value
   * @Transform :text
   */
  public function escapeTextArguments($argument) {
    $argument = str_replace('\\"', '"', $argument);
    $argument = str_replace('\n', "\n", $argument);
    return $argument;
  }

  /**
   * Copies the provided file into the site's files directory.
   *
   * @Given the managed file :filename
   *
   * Creates a file object with the URI, and passes that object to a file
   * creation function to create the entity.
   * The function has to be here for now, as it needs some Mink functions.
   *
   * @todo See if it can be done without Mink functions?
   * @todo Allow creating private files.
   * @todo Add before and after event dispatchers.
   * @todo Add ability to create multiple files at once using Table.
   */
  public function createManagedFile($filename, $public = TRUE) {
    // Get location of source file.
    if ($this->getMinkParameter('files_path')) {
      $source_path = rtrim(realpath($this->getMinkParameter('files_path'))) . DIRECTORY_SEPARATOR . $filename;
      if (!is_file($source_path)) {
        throw new \Exception(sprintf("Cannot find the file at '%s'", $source_path));
      }
    } else {
      throw new \Exception("files_path not set");
    }

    $prefix = $public ? 'public://' : 'private://';
    $uri = $prefix . $filename;

    $this->fileCreate($source_path, $uri);
  }

  /**
   * Create a managed Drupal file.
   *
   * @param $source_path
   *   A file object passed in with the URI already set.
   * @param $destination
   *   (Optional) The desired URI where the file will be uploaded.
   *
   * @return
   *   A single Drupal file object.
   */
  public function fileCreate($source_path, $destination = NULL) {
    $data = file_get_contents($source_path);

    // Before working with files, we need to change our current directory to
    // DRUPAL_ROOT so that the relative paths that define the stream wrappers
    // (like public:// or temporary://) actually work.
    $cwd = getcwd();
    chdir(DRUPAL_ROOT);

    if ($file = file_save_data($data, $destination)) {
      $this->files[] = $file;
    }

    // Then change back.
    chdir($cwd);

    return $file;
  }

  /**
   * Record all the users created during this scenario.
   *
   * We need to use this hook so we can get users created in steps on other
   * contexts (most probably the DrupalContext).
   *
   * @AfterUserCreate
   */
  public function afterUserCreate(AfterUserCreateScope $scope) {
    $user = $scope->getEntity();
    $this->external_users[$user->name] = $user;
  }

  /**
   * Get a list of UIDs.
   *
   * @return
   *   An array of numeric UIDs of users created by Given... steps during this scenario.
   */
  public function getUIDs() {
    $uids = array();
    foreach ($this->external_users as $user) {
      $uids[] = $user->uid;
    }
    return $uids;
  }

  /**
   * Cleans up files after every scenario.
   *
   * @AfterScenario @api
   */
  public function cleanFiles($event) {
    // Get UIDs of users created during this scenario.
    $uids = $this->getUIDs();
    if (!empty($uids)) {

      // Add any files created by test users to the $files variable.
      $file_ids = db_query('SELECT fid FROM {file_managed} WHERE uid IN (:uids)', array(':uids' => $uids))->fetchAll();
      if (!empty($file_ids)) {
        // The file_delete() function expects an object.
        foreach ($file_ids as $fid) {
          $file = file_load($fid->fid);
          $this->files[] = $file;
        }
      }
    }

    // Delete any files that were created by test users or our Given step.
    if (!empty($this->files)) {
      foreach ($this->files as $file) {
        $this->fileDelete($file);
      }
    }

    // Reset the arrays to empty after deletion.
    $this->files = array();
  }

  /**
   * Delete a managed Drupal file.
   *
   * @param $file
   *   A file object to delete.
   */
  public function fileDelete($file) {
    // Figure out if there's usage in any nodes.
    $fid = $file->fid;
    $node_usage = db_query('SELECT id AS nid FROM {file_usage} WHERE fid = (:fid) AND module = (:module) and type = (:node)', array(':fid' => $fid, ':module' => 'media', ':node' => 'node'))->fetchAll();
    // If there is, it should be safe to unregister it, because we already know the file is owned by a current test user.
    if (!empty($node_usage)) {
      foreach ($node_usage as $nid) {
        file_usage_delete($file, 'media', 'node', $nid->nid);
      }
    }
    // See PanopolyContext::fileCreate() for information on why we do this.
    $cwd = getcwd();
    chdir(DRUPAL_ROOT);
    file_delete($file);
    chdir($cwd);
  }

  /**
   * Disable live previews via Panopoly Magic.
   *
   * @Given Panopoly magic live previews are disabled
   */
  public function disablePanopolyMagicLivePreview() {
    variable_set('panopoly_magic_live_preview', 0);
  }

  /**
   * Enable live previews via Panopoly Magic.
   *
   * @Given Panopoly magic live previews are automatic
   */
  public function enableAutomaticPanopolyMagicLivePreview() {
    variable_set('panopoly_magic_live_preview', 1);
  }

  /**
   * Enable live previews via Panopoly Magic.
   *
   * @Given Panopoly magic live previews are manual
   */
  public function enableManualPanopolyMagicLivePreview() {
    variable_set('panopoly_magic_live_preview', 2);
  }

  /**
   * @Given Panopoly magic add content previews are disabled
   *
   * Disable add content previews via Panopoly Magic.
   */
  public function disablePanopolyMagicAddContentPreview() {
    variable_set('panopoly_magic_pane_add_preview', PANOPOLY_ADD_PREVIEW_DISABLED);
  }

  /**
   * @Given Panopoly magic add content previews are automatic
   *
   * Enable automatic add content previews via Panopoly Magic.
   */
  public function enableAutomaticPanopolyMagicAddContentPreview() {
    variable_set('panopoly_magic_pane_add_preview', PANOPOLY_ADD_PREVIEW_AUTOMATIC);
  }

  /**
   * @Given Panopoly magic add content previews are manual
   *
   * Enable manual add content previews via Panopoly Magic.
   */
  public function enableManualPanopolyMagicAddContentPreview() {
    variable_set('panopoly_magic_pane_add_preview', PANOPOLY_ADD_PREVIEW_MANUAL);
  }

  /**
   * @Given Panopoly magic add content previews are single
   *
   * Enable single add content previews via Panopoly Magic.
   */
  public function enableSinglePanopolyMagicAddContentPreview() {
    variable_set('panopoly_magic_pane_add_preview', PANOPOLY_ADD_PREVIEW_SINGLE);
  }

  /**
   * Disable the "Use Advanced Panel Panes" option.
   *
   * @Given Panopoly admin "Use Advanced Panel Plugins" is disabled
   */
  public function disablePanopolyAdminAdvanacedPanelPlugins() {
    if (empty($this->configVariables['panopoly_admin_advanced_plugins'])) {
      $this->configVariables['panopoly_admin_advanced_plugins'] = variable_get('panopoly_admin_advanced_plugins', 'not set');
    }
    variable_set('panopoly_admin_advanced_plugins', FALSE);
  }

  /**
   * Enable the "Use Advanced Panel Panes" option.
   *
   * @Given Panopoly admin "Use Advanced Panel Plugins" is enabled
   */
  public function enablePanopolyAdminAdvanacedPanelPlugins() {
    if (empty($this->configVariables['panopoly_admin_advanced_plugins'])) {
      $this->configVariables['panopoly_admin_advanced_plugins'] = variable_get('panopoly_admin_advanced_plugins', 'not set');
    }
    variable_set('panopoly_admin_advanced_plugins', TRUE);
  }

  /**
   * Wait for the given number of seconds. ONLY USE FOR DEBUGGING!
   *
   * @When (I )wait( for) :seconds second(s)
   */
  public function iWaitForSeconds($seconds) {
    sleep($seconds);
  }

  /**
   * Wait for AJAX to finish.
   *
   * @Given I wait for AJAX
   */
  public function iWaitForAjax() {
    $this->getSession()->wait(5000, 'typeof jQuery !== "undefined" && jQuery.active === 0');
  }

  /**
   * Wait until the live preview to finish.
   *
   * @When I wait for live preview to finish
   */
  public function waitForLivePreview() {
    // Make sure the live preview has triggered by bluring the current focus.
    $this->getSession()->executeScript("document.activeElement.blur()");
    $this->getSession()->wait(5000, 'typeof jQuery !== "undefined" && jQuery.active === 0 && jQuery("#panopoly-form-widget-preview").length > 0 && !jQuery("#panopoly-form-widget-preview").hasClass("panopoly-magic-loading")');
  }

  /**
   * Print the HTML contents of a region for debugging purposes.
   *
   * @Given print the contents of the :region region
   */
  public function printRegionContents($region) {
    print $this->getRegion($region)->getOuterHtml();
  }

  /**
   * @Given I log in with the One Time Login Url
   */
  public function iLogInWithTheOneTimeLoginUrl() {
    if ($this->loggedIn()) {
      $this->logOut();
    }

    $random = new Random;

    // Create user (and project)
    $user = (object) array(
      'name' => $random->name(8),
      'pass' => $random->name(16),
      'role' => 'authenticated user',
    );
    $user->mail = "{$user->name}@example.com";

    // Create a new user.
    $this->getDriver()->userCreate($user);

    if (method_exists($this, 'getUserManager')) {
      $user_manager = $this->getUserManager();
      $user_manager->addUser($user);
      $user_manager->setCurrentUser($user);
    }
    else {
      $this->users[$user->name] = $this->user = $user;
    }

    $base_url = rtrim($this->locatePath('/'), '/');
    $login_link = $this->getDriver('drush')->drush('uli', array(
      "'$user->name'",
      '--browser=0',
      "--uri=${base_url}",
    ));
    // Trim EOL characters. Required or else visiting the link won't work.
    $login_link = trim($login_link);
    $login_link = str_replace("/login", '', $login_link);
    $this->getSession()->visit($this->locatePath($login_link));
    return TRUE;
  }

  /**
   * @Given I am viewing a landing page
   */
  public function iAmViewingALandingPage() {
    $node = (object)array(
      'type' => 'panopoly_test_landing_page',
      'title' => $this->getRandom()->name(8),
    );
    $saved = $this->nodeCreate($node);

    // Set internal page on the new node.
    $this->getSession()->visit($this->locatePath('/node/' . $saved->nid));
  }

  /**
   * @When I switch to the frame :frame
   */
  public function iSwitchToTheFrame($frame) {
    $this->getSession()->switchToIFrame($frame);
    $this->iframe = $frame;
    sleep(3);
  }

  /**
   * @When I switch out of all frames
   */
  public function iSwitchOutOfAllFrames() {
    $this->getSession()->switchToIFrame();
    $this->iframe = NULL;
  }

  /**
   * @When I click :text link or button
   */
  public function iClickLinkOrButton($text) {
    $link = $this->getSession()->getPage()->findLink($text);
    if ($link !== NULL) {
      $link->click();
      return;
    }

    $button = $this->getSession()->getPage()->findButton($text);
    if ($button !== NULL) {
      $button->press();
      return;
    }

    throw new \Exception(sprintf('The link or button "%s" was not found on the page %s', $text, $this->getSession()->getCurrentUrl()));
  }

  /**
   * @When I type :text into :field field
   */
  public function iTypeIntoField($text, $field) {
    // The builtin "I fill in..." step will immediately move focus out of the
    // field, which makes it impossible to test autocomplete. This step puts the
    // value in without changing focus.
    $this->getSession()
      ->getDriver()
      ->getWebDriverSession()
      ->element('xpath', $this->getSession()->getSelectorsHandler()->selectorToXpath('named_exact', array('field', $field)))
      ->postValue(array('value' => array($text)));
  }

  /**
   * @Then I should see :text in the :tag element in the :region region
   */
  public function assertRegionElementText($text, $tag, $region) {
    $regionObj = $this->getRegion($region);
    $elements = $regionObj->findAll('css', $tag);
    if (empty($elements)) {
      throw new \Exception(sprintf('The element "%s" was not found in the "%s" region on the page %s', $tag, $region, $this->getSession()->getCurrentUrl()));
    }

    $found = FALSE;
    foreach ($elements as $element) {
      if ($element->getText() == $text) {
        $found = TRUE;
        break;
      }
    }
    if (!$found) {
      throw new \Exception(sprintf('The text "%s" was not found in the "%s" element in the "%s" region on the page %s', $text, $tag, $region, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * @Then I should not see :text in the :tag element with the :attribute attribute set to :value in the :region region
   */
  public function assertNotRegionElementTextAttribute($text, $tag, $attribute, $value, $region) {
    $regionObj = $this->getRegion($region);
    $elements = $regionObj->findAll('css', $tag);
    if (!empty($elements)) {
      foreach ($elements as $element) {
        if ($element->getText() == $text) {
          $attr = $element->getAttribute($attribute);
          if (!empty($attr) && strpos($attr, "$value") !== FALSE) {
            throw new \Exception(sprintf('The text "%s" was found in the "%s" element with the "%s" attribute set to "%s" in the "%s" region on the page %s', $text, $tag, $attribute, $value, $region, $this->getSession()->getCurrentUrl()));
          }
        }
      }
    }
  }

  /**
   * Asserts that the region contains text matching specified pattern.
   *
   * @Then I should see text matching :pattern in the :region region
   */
  public function assertRegionMatchesText($pattern, $region)
  {
    $regionObj = $this->getRegion($region);

    // Find the text within the region
    $regionText = $regionObj->getText();
    if (!preg_match($pattern, $regionText)) {
      throw new \Exception(sprintf("No text matching '%s' was found in the region '%s' on the page %s", $pattern, $region, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * Asserts that the region does not contain text matching specified pattern.
   *
   * @Then I should not see text matching :pattern in the :region region
   */
  public function assertNotRegionMatchesText($pattern, $region)
  {
    $regionObj = $this->getRegion($region);

    // Find the text within the region
    $regionText = $regionObj->getText();
    if (preg_match($pattern, $regionText)) {
      throw new \Exception(sprintf("Text matching '%s' was found in the region '%s' on the page %s", $pattern, $region, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * Asserts that an image is present and not broken.
   *
   * @Then I should see an image in the :region region
   */
  public function assertValidImageRegion($region) {
    $regionObj = $this->getRegion($region);
    $elements = $regionObj->findAll('css', 'img');
    if (empty($elements)) {
      throw new \Exception(sprintf('No image was not found in the "%s" region on the page %s', $region, $this->getSession()->getCurrentUrl()));
    }

    if ($src = $elements[0]->getAttribute('src')) {
      $params = array('http' => array('method' => 'HEAD'));
      $context = stream_context_create($params);
      $fp = @fopen($src, 'rb', FALSE, $context);
      if (!$fp) {
        throw new \Exception(sprintf('Unable to download <img src="%s"> in the "%s" region on the page %s', $src, $region, $this->getSession()->getCurrentUrl()));
      }

      $meta = stream_get_meta_data($fp);
      fclose($fp);
      if ($meta === FALSE) {
        throw new \Exception(sprintf('Error reading from <img src="%s"> in the "%s" region on the page %s', $src, $region, $this->getSession()->getCurrentUrl()));
      }

      $wrapper_data = $meta['wrapper_data'];
      $found = FALSE;
      if (is_array($wrapper_data)) {
        foreach ($wrapper_data as $header) {
          if (substr(strtolower($header), 0, 19) == 'content-type: image') {
            $found = TRUE;
          }
        }
      }

      if (!$found) {
        throw new \Exception(sprintf('Not a valid image <img src="%s"> in the "%s" region on the page %s', $src, $region, $this->getSession()->getCurrentUrl()));
      }
    }
    else {
      throw new \Exception(sprintf('No image had no src="..." attribute in the "%s" region on the page %s', $region, $this->getSession()->getCurrentUrl()));
    }
  }


  /**
   * @Then /^I should see the image alt "(?P<text>(?:[^"]|\\")*)" in the "(?P<region>[^"]*)" region$/
   *
   * NOTE: We specify a regex to allow escaped quotes in the alt text.
   */
  public function assertAltRegion($text, $region) {
    $regionObj = $this->getRegion($region);
    $element = $regionObj->find('css', 'img');
    $tmp = $element->getAttribute('alt');
    if ($text == $tmp) {
      $result = $text;
    }
    if (empty($result)) {
      throw new \Exception(sprintf('No alt text matching "%s" in the "%s" region on the page %s', $text, $region, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * @Then the :field radio button should be set to :option
   *
   * @link: https://www.drupal.org/node/1891584 @endlink
   */
  public function theRadioButtonShouldBeSetTo($field, $option) {
    $page = $this->getSession()->getPage();
    $div = $page->find('xpath', "//div[contains(., '$field') and @class[contains(.,'form-type-radio')]]");
    if ($div) {
      $radios = $div->find('xpath', "//input[@type='radio']");
      if ($radios) {
        $checkedRadio = $div->find('xpath', "//input[@type='radio' and @checked='checked' and @id=(//label[contains(., '$option')]/@for)] ");
        if (!$checkedRadio) {
          throw new \Exception(sprintf('We found the radio buttons for "%s", but "%s" was not selected.', $field, $option));
        }
      } elseif (!$radios) {
        throw new \Exception(sprintf('We found "%s", but it did not contain any radio buttons".', $field));
      }
    } elseif (!$div) {
      throw new \Exception(sprintf('We couldn\'nt find "%s" on the page', $field));
    } else {
      throw new \Exception('General exception from '.__FUNCTION__);
    }
  }

  /**
   * @Then I should see the radio button :field with the id :id
   * @Then I should see the radio button :field
   */
  public function assertSeeRadioById($field, $id = FALSE) {
    $element = $this->getSession()->getPage();
    $radiobutton = $id ? $element->findById($id) : $element->find('named', array('radio', $this->getSession()->getSelectorsHandler()->xpathLiteral($field)));
    if ($radiobutton === NULL) {
      throw new \Exception(sprintf('The radio button with "%s" was not found on the page %s', $id ? $id : $field, $this->getSession()->getCurrentUrl()));
    }
    if ($id) {
      $value = $radiobutton->getAttribute('value');
      $labelonpage = $radiobutton->getParent()->getText();
      if ($field != $labelonpage) {
        throw new \Exception(sprintf("Button with id '%s' has label '%s' instead of '%s' on the page %s", $id, $labelonpage, $field, $this->getSession()->getCurrentUrl()));
      }
    }
  }

  /**
   * @Then I should not see the radio button :field with the id :id
   * @Then I should not see the radio button :field
   */
  public function assertNotSeeRadioById($field, $id = FALSE) {
    $element = $this->getSession()->getPage();
    $radiobutton = $id ? $element->findById($id) : $element->find('named', array('radio', $this->getSession()->getSelectorsHandler()->xpathLiteral($field)));
    if ($radiobutton !== NULL) {
      throw new \Exception(sprintf('The radio button with "%s" was found on the page %s', $id ? $id : $field, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * @Then the :field select should be set to :value
   */
  public function theSelectShouldBeSetTo($field, $value) {
    $select = $this->getSession()->getPage()->findField($field);
    if (empty($select)) {
      throw new \Exception(sprintf('We couldn\'nt find "%s" on the page', $field));
    }
    $options = $select->findAll('xpath', '//option[@selected="selected"]');
    if (empty($select)) {
      throw new \Exception(sprintf('The select "%s" doesn\'t have any options selected', $field));
    }

    $found = FALSE;
    foreach ($options as $option) {
      if ($option->getText() === $value) {
        $found = TRUE;
        break;
      }
    }
    if (!$found) {
      throw new \Exception(sprintf('The select "%s" doesn\'t have the option "%s" selected', $field, $value));
    }
  }

  /**
   * @Given the dblog is empty
   */
  public function clearDblog() {
    db_delete('watchdog')->execute();
  }
  
  /**
   * @When I select the first autocomplete option for :text on the :field field
   */
  public function iSelectFirstAutocomplete($text, $field) {
    $session = $this->getSession();
    $page = $session->getPage();
    $element = $page->findField($field);
    if (empty($element)) {
      throw new \Exception(sprintf('We couldn\'t find "%s" on the page', $field));
    }
    $page->fillField($field, $text);

    $xpath = $element->getXpath();
    $driver = $session->getDriver();

    // autocomplete.js uses key down/up events directly.

    // Press the backspace key.
    $driver->keyDown($xpath, 8);
    $driver->keyUp($xpath, 8);

    // Retype the last character.
    $chars = str_split($text);
    $last_char = array_pop($chars);
    $driver->keyDown($xpath, $last_char);
    $driver->keyUp($xpath, $last_char);

    // Wait for AJAX to finish.
    $this->iWaitForAJAX();

    // And make sure the autocomplete is showing.
    $this->getSession()->wait(5000, 'jQuery("#autocomplete").show().length > 0');

    // And wait for 1 second just to be sure.
    sleep(1);

    // Press the down arrow to select the first option.
    $driver->keyDown($xpath, 40);
    $driver->keyUp($xpath, 40);

    // Press the Enter key to confirm selection, copying the value into the field.
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);

    // Wait for AJAX to finish.
    $this->iWaitForAJAX();
  }

  /**
   * @Given :module has the :dependency dependency at position :key
   */
  public function moduleHasDependencyAtKey($module, $dependency, $key) {
    if (!module_exists($module)) {
      throw new \Exception("$module is not enabled.");
    }
    if (!module_exists($dependency)) {
      throw new \Exception("$dependency is not enabled.");
    }

    $info = drupal_parse_info_file(drupal_get_path('module', $module) . '/' . $module . '.info');

    if ($info['dependencies'][$key] != $dependency) {
      throw new \Exception("$module did not have $dependency dependency at $key position");
    }

  }

  /**
   * @When /^I click the (?P<count>\d+)(?:st|nd|rd|th) "(?P<link>[^"]*)" in the "(?P<region>[^"]*)" region$/
   */
  public function clickNthLink($count, $link, $region) {
    $session = $this->getSession();
    $region = $session->getPage()->find('region', $region);

    // We index from zero, rather than one.
    $index = $count - 1;

    foreach ($region->findAll('xpath', '//a[text()="' . $link . '"]') as $element) {
      if ($index == 0) {
        $element->click();
        return;
      }
      elseif ($index < 0) {
        break;
      }
      else {
        $index--;
      }
    }

    throw new \Exception(sprintf("Cannot find link with text '%s' and index %d", $link, $count));
  }

  /**
   * @When (I )confirm the popup
   */
  public function iConfirmPopup() {
    for ($i = 0; $i < 5; $i++) {
      try {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
        break;
      }
      catch (NoAlertOpenError $e) {
        sleep(1);
      }
    }
  }

  /**
   * @When (I )cancel the popup
   */
  public function iCancelPopup() {
    for ($i = 0; $i < 5; $i++) {
      try {
        $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
        break;
      }
      catch (NoAlertOpenError $e) {
        sleep(1);
      }
    }
  }

}
