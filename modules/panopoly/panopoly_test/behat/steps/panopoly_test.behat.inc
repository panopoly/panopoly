<?php

/**
 * @file
 * Provide Behat step-definitions for generic Panopoly tests.
 */

use Drupal\Core\Extension\InfoParser;
use Drupal\Core\StreamWrapper\PublicStream;
use Behat\Mink\Exception\ExpectationException;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;
use Drupal\DrupalDriverManager;
use Drupal\Component\Utility\Random;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Drupal\DrupalExtension\Hook\Scope\AfterUserCreateScope;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Selector\Xpath\Escaper;

/**
 * Behat sub-context for Panopoly.
 */
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
  protected $externalUsers = [];

  /**
   * Keep track of files added by tests so they can be cleaned up.
   *
   * @var array
   */
  protected $files = [];

  /**
   * Set to TRUE after the window has been resized.
   *
   * @var bool
   */
  protected $windowResized = FALSE;

  /**
   * Store variables so they can be reset to previous values.
   *
   * @var array
   */
  protected $configVariables = [];

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
    // @todo Implement for Drupal 8!
    // This should capture any config that we want to change to defaults for
    // the tests, so we can restore the original config later.
    // In Panopoly 1.x, this disabled live previews and set the add content
    // preview mode to 'Single'.
  }

  /**
   * Restore saved preview configurations or other variables.
   *
   * @AfterScenario @api
   */
  public function restoreConfig() {
    if (count($this->configVariables) > 0) {
      foreach ($this->configVariables as $key => $value) {
        // @todo Implement for Drupal 8!
        // This should restore any saved config to it's original values.
      }
    }
  }

  /**
   * Set a variable to mark the current scenario as using javascript.
   *
   * @BeforeScenario @javascript
   */
  public function setJavascript() {
    $this->javascript = TRUE;
  }

  /**
   * If we will flag PHP errors, clear all of them before we run a suite.
   *
   * @BeforeSuite
   */
  public static function clearPhpBehatNoticeLogs() {
    if (!empty(getenv('PANOPOLY_BEHAT_FLAG_PHP_NOTICES'))) {
      \Drupal::database()->delete('watchdog')
        ->condition('type', ['php', 'behat'], 'IN')
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
      $result = \Drupal::database()->select('watchdog', 'w')
        ->fields('w', [])
        ->condition('w.type', 'php', '=')
        ->execute();

      $errors = [];
      foreach ($result as $entry) {
        $variables = unserialize($entry->variables);
        $time = date('Ymd-Hi', $entry->timestamp);
        // phpcs:ignore Drupal.Semantics.FunctionT.NotLiteralString
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
        elseif (!is_dir($local_file_path)) {
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
        \Drupal::database()->update('watchdog')
          ->fields(['type' => 'behat'])
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
      $number_of_rows = \Drupal::database()->select('watchdog', 'w')
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
    if (!$this->windowResized) {
      $session = $this->getSession();
      if (!$session->isStarted()) {
        $session->start();
      }

      $dimensions = getenv('PANOPOLY_BEHAT_WINDOW_SIZE');
      if (!empty($dimensions)) {
        [$width, $height] = explode('x', $dimensions);
        $session->resizeWindow((int) $width, (int) $height, 'current');
      }
      else {
        $session->getDriver()->maximizeWindow();
      }

      $this->windowResized = TRUE;
    }
  }

  /**
   * Unsets the variable marking the current scenario as using javascript.
   *
   * @AfterScenario @javascript
   */
  public function unsetJavascript() {
    $this->javascript = FALSE;
  }

  /**
   * Configure a private files path if one isn't already configured.
   *
   * @BeforeScenario @api&&@drupal_private_files
   */
  public function configurePrivateFiles($event) {
    $file_public_path = PublicStream::basePath();
    $file_private_path = $file_public_path . '/private';
    \Drupal::getContainer()->get('state')->set('panopoly_test_private_file_path', $file_private_path);

    \Drupal::service('kernel')->invalidateContainer();
  }

  /**
   * Clean up our temporary private files path.
   *
   * @AfterScenario @api&&@drupal_private_files
   */
  public function cleanupPrivateFiles($event) {
    \Drupal::getContainer()->get('state')->delete('panopoly_test_private_file_path');
    \Drupal::service('kernel')->invalidateContainer();
  }

  /**
   * After every step, we want to wait for AJAX loading to finish.
   *
   * Only for @javascript scenarios.
   *
   * @AfterStep
   */
  public function afterStepWaitForJavascript($event) {
    if (isset($this->javascript) && $this->javascript) {
      $text = $event->getStep()->getText();
      if (preg_match('/(follow|press|click|submit|viewing|visit|reload|attach)/i', $text)) {
        $this->iWaitForAjax();

        // For whatever reason, the above isn't very accurate inside iframes.
        if (!empty($this->iframe)) {
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
    $local_screenshot_path = getenv('PANOPOLY_BEHAT_SCREENSHOT_PATH');
    if (empty($local_screenshot_path)) {
      print "Environment variable PANOPOLY_BEHAT_SCREENSHOT_PATH is not set, unable to save screenshot\n";
    }
    elseif (!is_dir($local_screenshot_path)) {
      print "Directory $local_screenshot_path does not exist, unable to save screenshot\n";
    }
    else {
      $file_location = "$local_screenshot_path/$title.png";
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
    if ($event->getTestResult()->getResultCode() === TestResult::FAILED) {
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
   *   The Client ID for your application registered with imgur.
   *
   * @see https://api.imgur.com/oauth2
   */
  protected function uploadScreenshot($image, $title, $imgur_client_id) {
    // @todo This should use Guzzle rather than curl directly
    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL => "https://api.imgur.com/3/image",
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "image=" . urlencode(base64_encode($image)) . "&title=$title",
      CURLOPT_HTTPHEADER => [
        "authorization: Client-ID $imgur_client_id",
        "cache-control: no-cache",
        "content-type: application/x-www-form-urlencoded",
      ],
    ]);

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
    }
    else {
      throw new \Exception("files_path not set");
    }

    $prefix = $public ? 'public://' : 'private://';
    $uri = $prefix . $filename;

    $this->fileCreate($source_path, $uri);
  }

  /**
   * Create a managed Drupal file.
   *
   * @param string $source_path
   *   A file object passed in with the URI already set.
   * @param string $destination
   *   (Optional) The desired URI where the file will be uploaded.
   *
   * @return object
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
    $this->externalUsers[$user->name] = $user;
  }

  /**
   * Get a list of UIDs.
   *
   * @return array
   *   An array of numeric UIDs of users created by Given steps during this
   *   scenario.
   */
  public function getUids() {
    $uids = [];
    foreach ($this->externalUsers as $user) {
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
    $uids = $this->getUids();
    if (!empty($uids)) {

      // @todo Implement for Drupal 8!
    }
  }

  /**
   * Delete a managed Drupal file.
   *
   * @param object $file
   *   A file object to delete.
   */
  public function fileDelete($file) {
    // @todo Implement for Drupal 8!
  }

  /**
   * Disable live previews via Panopoly Magic.
   *
   * @Given Panopoly magic live previews are disabled
   */
  public function disablePanopolyMagicLivePreview() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Enable live previews via Panopoly Magic.
   *
   * @Given Panopoly magic live previews are automatic
   */
  public function enableAutomaticPanopolyMagicLivePreview() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Enable live previews via Panopoly Magic.
   *
   * @Given Panopoly magic live previews are manual
   */
  public function enableManualPanopolyMagicLivePreview() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Disables add content previews via Panopoly Magic.
   *
   * @Given Panopoly magic add content previews are disabled
   */
  public function disablePanopolyMagicAddContentPreview() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Enables automatic add content previews via Panopoly Magic.
   *
   * @Given Panopoly magic add content previews are automatic
   */
  public function enableAutomaticPanopolyMagicAddContentPreview() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Enables manual add content previews via Panopoly Magic.
   *
   * @Given Panopoly magic add content previews are manual
   */
  public function enableManualPanopolyMagicAddContentPreview() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Enables single add content previews via Panopoly Magic.
   *
   * @Given Panopoly magic add content previews are single
   */
  public function enableSinglePanopolyMagicAddContentPreview() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Disable the "Use Advanced Panel Panes" option.
   *
   * @Given Panopoly admin "Use Advanced Panel Plugins" is disabled
   */
  public function disablePanopolyAdminAdvanacedPanelPlugins() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Enable the "Use Advanced Panel Panes" option.
   *
   * @Given Panopoly admin "Use Advanced Panel Plugins" is enabled
   */
  public function enablePanopolyAdminAdvanacedPanelPlugins() {
    // @todo Implement for Drupal 8!
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
    // @todo Implement for Drupal 8!
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
   * Logs in with the one time login URL.
   *
   * @Given I log in with the One Time Login Url
   */
  public function iLogInWithTheOneTimeLoginUrl() {
    if ($this->loggedIn()) {
      $this->logOut();
    }

    $random = new Random();

    // Create user (and project)
    $user = (object) [
      'name' => $random->name(8),
      'pass' => $random->name(16),
      'role' => 'authenticated user',
    ];
    $user->mail = "{$user->name}@example.com";

    // Create a new user.
    $this->getDriver()->userCreate($user);

    $this->users[$user->name] = $this->user = $user;

    $base_url = rtrim($this->locatePath('/'), '/');
    $login_link = $this->getDriver('drush')->drush('uli', [
      "'$user->name'",
      '--browser=0',
      "--uri=${base_url}",
    ]);
    // Trim EOL characters. Required or else visiting the link won't work.
    $login_link = trim($login_link);
    $login_link = str_replace("/login", '', $login_link);
    $this->getSession()->visit($this->locatePath($login_link));
    return TRUE;
  }

  /**
   * Creates and views a landing page.
   *
   * @Given I am viewing a landing page
   */
  public function iAmViewingLandingPage() {
    $node = (object) [
      'type' => 'panopoly_test_landing_page',
      'title' => $this->getRandom()->name(8),
    ];
    $saved = $this->nodeCreate($node);

    // Set internal page on the new node.
    $this->getSession()->visit($this->locatePath('/node/' . $saved->nid));
  }

  /**
   * Switches to frame.
   *
   * @When I switch to the frame :frame
   */
  public function iSwitchToTheFrame($frame) {
    $this->getSession()->switchToIFrame($frame);
    $this->iframe = $frame;
    sleep(3);
  }

  /**
   * Switches out of all frames.
   *
   * @When I switch out of all frames
   */
  public function iSwitchOutOfAllFrames() {
    $this->getSession()->switchToIFrame();
    $this->iframe = NULL;
  }

  /**
   * Tests for element content in region.
   *
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
   * Tests for element content and attributes in region.
   *
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
  public function assertRegionMatchesText($pattern, $region) {
    $regionObj = $this->getRegion($region);

    // Find the text within the region.
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
  public function assertNotRegionMatchesText($pattern, $region) {
    $regionObj = $this->getRegion($region);

    // Find the text within the region.
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
      $params = ['http' => ['method' => 'HEAD']];
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
   * Asserts the image alt text in a region.
   *
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
   * Asserts the selected radio button option.
   *
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
        $checkedRadio = $div->find('xpath', "//input[@type='radio' and @checked='checked']/following-sibling::label[contains(text(), '$option')] ");
        if (!$checkedRadio) {
          throw new \Exception(sprintf('We found the radio buttons for "%s", but "%s" was not selected.', $field, $option));
        }
      }
      elseif (!$radios) {
        throw new \Exception(sprintf('We found "%s", but it did not contain any radio buttons".', $field));
      }
    }
    elseif (!$div) {
      throw new \Exception(sprintf('We couldn\'nt find "%s" on the page', $field));
    }
    else {
      throw new \Exception('General exception from ' . __FUNCTION__);
    }
  }

  /**
   * Asserts the radio button option.
   *
   * @Then I should see the radio button :field with the id :id
   * @Then I should see the radio button :field
   */
  public function assertSeeRadioById($field, $id = FALSE) {
    $escaper = new Escaper();
    $element = $this->getSession()->getPage();
    $radiobutton = $id ? $element->findById($id) : $element->find('named', [
      'radio',
      $escaper->escapeLiteral($field),
    ]);
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
   * Asserts that a radio button is present.
   *
   * @Then I should not see the radio button :field with the id :id
   * @Then I should not see the radio button :field
   */
  public function assertNotSeeRadioById($field, $id = FALSE) {
    $escaper = new Escaper();
    $element = $this->getSession()->getPage();
    $radiobutton = $id ? $element->findById($id) : $element->find('named', [
      'radio',
      $escaper->escapeLiteral($field),
    ]);
    if ($radiobutton !== NULL) {
      throw new \Exception(sprintf('The radio button with "%s" was found on the page %s', $id ? $id : $field, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * Asserts select field value.
   *
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
   * Asserts that the dblog is empty.
   *
   * @Given the dblog is empty
   */
  public function clearDblog() {
    // @todo Implement for Drupal 8!
  }

  /**
   * Selects the first autocomplete option.
   *
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

    // Press the Enter key to confirm selection, copying the value into the
    // field.
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);

    // Wait for AJAX to finish.
    $this->iWaitForAJAX();
  }

  /**
   * Checks, that entity reference field has specified value.
   *
   * Entity references have the entity ID appended to the title. This step
   * allows asserting the main text value without needing to know the entity ID.
   *
   * @Then /^the entity reference "(?P<field>(?:[^"]|\\")*)" field should contain "(?P<value>(?:[^"]|\\")*)"$/
   */
  public function assertEntityReferenceFieldContains($field, $value) {
    $node = $this->assertSession()->fieldExists($field);
    $actual = $node->getValue();
    $regex = '/^' . preg_quote($value, '/') . ' \(.\d*\)$/ui';

    $message = sprintf('The field "%s" value is "%s", but "%s" (%s) expected.', $field, $actual, $value, $regex);

    if (preg_match($regex, $actual) !== 1) {
      throw new ExpectationException($message, $this->getSession()->getDriver());
    }
  }

  /**
   * Checks, that entity reference field with has specified value.
   *
   * Entity references have the entity ID appended to the title. This step
   * allows asserting the main text value without needing to know the entity ID.
   *
   * @Then /^the entity reference "(?P<field>(?:[^"]|\\")*)" field should not contain "(?P<value>(?:[^"]|\\")*)"$/
   */
  public function assertEntityReferenceFieldNotContains($field, $value) {
    $node = $this->assertSession()->fieldExists($field);
    $actual = $node->getValue();
    $regex = '/^' . preg_quote($value, '/') . ' \([0-9]+\)$/ui';

    $message = sprintf('The field "%s" value is "%s", but it should not be.', $field, $actual);

    if (preg_match($regex, $actual) === 1) {
      throw new ExpectationException($message, $this->getSession()->getDriver());
    }
  }

  /**
   * Checks that module has given dependency.
   *
   * @Given :module has the :dependency dependency at position :key
   */
  public function moduleHasDependencyAtKey($module, $dependency, $key) {
    $module_handler = \Drupal::moduleHandler();
    if (!$module_handler->moduleExists($module)) {
      throw new \Exception("$module is not enabled.");
    }

    // Handle namespaced dependencies.
    $dependency_extension_name = $dependency;
    if (strpos($dependency, ':') !== FALSE) {
      [, $dependency_extension_name] = explode(':', $dependency, 2);
    }
    if (!$module_handler->moduleExists($dependency_extension_name)) {
      throw new \Exception("$dependency_extension_name is not enabled.");
    }
    $extension = $module_handler->getModule($module);
    $info_parser = \Drupal::service('info_parser');
    assert($info_parser instanceof InfoParser);
    $info = $info_parser->parse($extension->getPathname());
    if ($info['dependencies'][$key] !== $dependency) {
      var_export($info);
      throw new \Exception("$module did not have $dependency dependency at $key position");
    }
  }

  /**
   * Click the nth link in a region.
   *
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
   * Deletes files with a particular URI.
   *
   * @Given /^there are no files with uri "([^"]*)"$/
   */
  public function thereAreNoFilesNamed($uri) {
    /** @var \Drupal\file\FileInterface[] $files */
    $files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties(['uri' => $uri]);
    foreach ($files as $file) {
      $file->delete();
    }
  }

  /**
   * Hovers over the given CSS selector.
   *
   * @When I hover over :selector
   */
  public function hoverOverElement($selector) {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', $selector);
    if ($element === NULL) {
      throw new \Exception("Unable to find element '$selector'");
    }
    $element->mouseOver();
  }

  /**
   * Drops a file into the given CSS selector.
   *
   * @Given I drop the file :path to :selector
   *
   * @see \Drupal\Tests\dropzonejs\FunctionalJavascript\DropzoneJsWebDriverTestBase::dropFile
   * @see \Behat\MinkExtension\Context\MinkContext::attachFileToField
   */
  public function iDropTheFileTo($path, $selector) {
    $input = <<<JS
      jQuery('.form-type-dropzonejs').append('<input type="file" name="fakefile">');
JS;
    $this->getSession()->evaluateScript($input);

    if ($this->getMinkParameter('files_path')) {
      $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $path;
      if (is_file($fullPath)) {
        $path = $fullPath;
      }
    }

    $this->getSession()->getPage()->attachFileToField('fakefile', $path);

    $drop = <<<JS
    (function(jQuery) {
      var fakeFileInputs = jQuery('input[name=fakefile]' );
      var fileList = fakeFileInputs.map(function (i, el) { return el.files[0] });
      var e = jQuery.Event('drop', { dataTransfer : { files : fileList } });
      document.getElementById('$selector').dropzone.listeners[0].events.drop(e);
      fakeFileInputs.map(function (i, el) { return el.remove(); });
    })(jQuery);
JS;

    $this->getSession()->evaluateScript($drop);
  }

  /**
   * Verifies that a field has expected placeholder text.
   *
   * @Then /^the "(?P<field>(?:[^"]|\\")*)" field placeholder should contain "(?P<value>(?:[^"]|\\")*)"$/
   */
  public function assertFieldContains($field, $value) {
    $node = $this->assertSession()->fieldExists($field);
    $actual = $node->getAttribute('placeholder');
    $regex = '/^' . preg_quote($value, '/') . '$/ui';

    $message = sprintf('The field "%s" value is "%s", but "%s" expected.', $field, $actual, $value);

    $result = (bool) preg_match($regex, $actual);
    if (!$result) {
      throw new ExpectationException($message, $this->getSession()->getDriver());
    }
  }

}
