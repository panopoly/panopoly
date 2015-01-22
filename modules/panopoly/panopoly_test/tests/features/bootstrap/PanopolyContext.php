<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalContext;
use Drupal\Component\Utility\Random;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class PanopolyContext extends DrupalContext
{

  /**
   * Keep track of files added by tests so they can be cleaned up.
   *
   * @var array
   */
  public $files = array();

  /**
   * Initializes context.
   * Every scenario gets its own context object.
   *
   * @param array $parameters context parameters (set them up through behat.yml)
   */
  public function __construct(array $parameters) {
    // Initialize your context here

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
   * Override MinkContext::fixStepArgument().
   *
   * Make it possible to use [random].
   * If you want to use the previous random value [random:1].
   */
  public function fixStepArgument($argument) {
    $argument = str_replace('\\"', '"', $argument);

    // Token replace the argument.
    static $random = array();
    for ($start = 0; ($start = strpos($argument, '[', $start)) !== FALSE; ) {
      $end = strpos($argument, ']', $start);
      if ($end === FALSE) {
        break;
      }
      $random_generator = new Random;
      $name = substr($argument, $start + 1, $end - $start - 1);
      if ($name == 'random') {
        $this->vars[$name] = $random_generator->name(8);
        $random[] = $this->vars[$name];
      }
      // In order to test previous random values stored in the form,
      // suppport random:n, where n is the number or random's ago
      // to use, i.e., random:1 is the previous random value.
      elseif (substr($name, 0, 7) == 'random:') {
        $num = substr($name, 7);
        if (is_numeric($num) && $num <= count($random)) {
          $this->vars[$name] = $random[count($random) - $num];
        }
      }
      if (isset($this->vars[$name])) {
        $argument = substr_replace($argument, $this->vars[$name], $start, $end - $start + 1);
        $start += strlen($this->vars[$name]);
      }
      else {
        $start = $end + 1;
      }
    }
    return $argument;
  }

  /**
   * @Given /^the managed file "([^"]*)"$/
   *
   * This function copies the provided file into the site files directory,
   * creates a file object with the URI, and passes that object to a file
   * creation function to create the entity.
   * The function has to be here for now, as it needs some Mink functions.
   *
   * @todo: See if it can be done without Mink functions?
   * @todo: Allow creating private files
   * @todo: Add before and after event dispatchers
   * @todo: Add ability to create multiple files at once using Table
   */
  public function createFile($filename, $public = TRUE) {
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
   *   A file object passed in with the URI already set
   * @param $destination
   *   (Optional) The desired URI where the file will be uploaded.
   *
   * @return
   *   Drupal file object.
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
   * Cleans up files after every scenario.
   *
   * @AfterScenario
   */
  public function cleanUpFiles($event) {
    // Add any files created by test users to the $files variable.
    if (!empty($this->users)) {
      foreach ($this->users as $user) {
        $uids[] = $user->uid;
      }
      $file_ids = db_query('SELECT fid FROM {file_managed} WHERE uid IN (:uids)', array(':uids' => $uids))->fetchAll();
      if (!empty($file_ids)) {
        // file_delete() expects an object.
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

    $this->files = array();
  }

  /**
   * Delete a managed Drupal file.
   *
   * @param $file
   *   A file object to delete
   */
  public function fileDelete($file) {
    // See PanopolyContext::fileCreate() for information on why we do this.
    $cwd = getcwd();
    chdir(DRUPAL_ROOT);

    file_delete($file, TRUE);

    chdir($cwd);
  }

}
