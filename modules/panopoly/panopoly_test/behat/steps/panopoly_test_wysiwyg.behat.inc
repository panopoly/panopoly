<?php

/**
 * @file
 * Provide Behat step-definitions for WYSIWYG editor.
 *
 * @todo This should move to the WYSIWYG module eventually
 */

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Behat subcontext for testing WYSIWYG.
 */
class WysiwygSubContext extends DrupalSubContextBase {

  /**
   * Get the instance variable to use in Javascript.
   *
   * @param string $instanceId
   *   The instanceId used by the WYSIWYG module to identify the instance.
   *
   * @throws \Exception
   *   Throws an exception if the editor doesn't exist.
   *
   * @return string
   *   A Javascript expression representing the WYSIWYG instance.
   */
  protected function getWysiwygInstance($instanceId) {
    $editorType = $this->getEditorType($instanceId);

    if ($editorType === 'ckeditor') {
      $instance = "CKEDITOR.instances['$instanceId']";
    }
    else {
      throw new \Exception("Cannot determine how to get editor instance for '$instanceId'");
    }

    if (!$this->getSession()->evaluateScript("return !!$instance")) {
      throw new \Exception(sprintf('The editor "%s" was not found on the page %s', $instanceId, $this->getSession()->getCurrentUrl()));
    }

    return $instance;
  }

  /**
   * Gets the type of the WYSIWYG editor.
   *
   * @param string $instanceId
   *   The WYSIWYG editor instance id.
   *
   * @return string
   *   The editor type.
   */
  protected function getEditorType(string $instanceId) {
    try {
      $script = "document.getElementById('$instanceId').dataset.editorActiveTextFormat";
      $editorActiveTextFormat = $this->getSession()->evaluateScript("return $script");
    }
    catch (\Exception $e) {
      throw new \Exception("Could not edit active text format for '$instanceId'.");
    }
    try {
      $editorType = $this->getSession()->evaluateScript("return drupalSettings.editor.formats['$editorActiveTextFormat'].editor");
    }
    catch (\Exception $e) {
      throw new \Exception(sprintf('The editor type for text format "%s" could not be found.', $editorActiveTextFormat));
    }
    return $editorType;
  }

  /**
   * Get a Mink Element representing the WYSIWYG toolbar.
   *
   * @param string $instanceId
   *   The instanceId used by the WYSIWYG module to identify the instance.
   * @param string $editorType
   *   Identifies the underlying editor (for example, "tinymce").
   *
   * @throws \Exception
   *   Throws an exception if the toolbar can't be found.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The toolbar DOM Node.
   */
  protected function getWysiwygToolbar($instanceId, $editorType) {
    $driver = $this->getSession()->getDriver();

    $toolbarElement = NULL;
    switch ($editorType) {
      case 'ckeditor':
        $toolbarElement = $driver->find("//div[@id='cke_$instanceId']//span[contains(@class, 'cke_top')]");
        $toolbarElement = !empty($toolbarElement) ? $toolbarElement[0] : NULL;
        break;

      case 'tinymce':
        $toolbarElement = $driver->find("//div[@id='{$instanceId}_toolbargroup']");
        $toolbarElement = !empty($toolbarElement) ? $toolbarElement[0] : NULL;
        break;

      case 'markitup':
        $elementId = 'markItUp' . ucfirst($instanceId);
        $toolbarElement = $driver->find("//div[@id='{$elementId}']//div[@class='markItUpHeader']");
        $toolbarElement = !empty($toolbarElement) ? $toolbarElement[0] : NULL;
        break;
    }

    if (!$toolbarElement) {
      throw new \Exception(sprintf('Toolbar for editor "%s" was not found on the page %s', $instanceId, $this->getSession()->getCurrentUrl()));
    }

    return $toolbarElement;
  }

  /**
   * Types into WYSIWYG editor.
   *
   * @When I type :text in the :instanceId WYSIWYG editor
   */
  public function iTypeInTheWysiwygEditor($text, $instanceId) {
    $editorType = $this->getEditorType($instanceId);
    $instance = $this->getWysiwygInstance($instanceId);
    // Necessary for some WYSIWYG editors (namely, markitup) to be focussed
    // before instance.insert() will do anything.
    $this->getSession()->executeScript("jQuery('#$instanceId').focus();");
    if ($editorType === 'ckeditor') {
      $this->getSession()->executeScript("$instance.insertText(\"$text\");");
    }
    else {
      $this->getSession()->executeScript("$instance.insert(\"$text\");");
    }
  }

  /**
   * Selects text in the WYSIWYG editor.
   *
   * @When I select the text in the :instanceId WYSIWYG editor
   */
  public function iSelectTheTextInTheWysiwygEditor($instanceId) {
    $editorType = $this->getEditorType($instanceId);
    $instance = $this->getWysiwygInstance($instanceId);
    // Necessary for some WYSIWYG editors (namely, markitup) to be focussed
    // before instance.insert() will do anything.
    $this->getSession()->executeScript("jQuery('#$instanceId').focus();");
    if ($editorType === 'ckeditor') {
      $javascript = <<<JS
const sel = $instance.getSelection();
sel.selectElement(sel.getStartElement());
JS;
      $this->getSession()->executeScript($javascript);
    }
    else {
      throw new \RuntimeException("The editor '$editorType' is not supported for this step.");
    }
  }

  /**
   * Presses a key in the WYSIWYG editor.
   *
   * @When I press the enter key in the :instanceId WYSIWYG editor
   *
   * @todo this could be a generic "send a command" step?
   */
  public function iPressTheEnterKeyInTheWysiwygEditor($instanceId) {
    $editorType = $this->getEditorType($instanceId);
    $instance = $this->getWysiwygInstance($instanceId);
    // Necessary for some WYSIWYG editors (namely, markitup) to be focussed
    // before instance.insert() will do anything.
    $this->getSession()->executeScript("jQuery('#$instanceId').focus();");
    if ($editorType === 'ckeditor') {
      $this->getSession()->executeScript("$instance.execCommand('enter');");
    }
    else {
      throw new \RuntimeException("Unknown editor '$editorType' to send commands to.");
    }
  }

  /**
   * Fills in the WYSIWYG editor with text.
   *
   * @When I fill in the :instanceId WYSIWYG editor with :text
   */
  public function iFillInTheWysiwygEditor($instanceId, $text) {
    $instance = $this->getWysiwygInstance($instanceId);
    $this->getSession()->executeScript("$instance.setContent(\"$text\");");
  }

  /**
   * Clicks a button in a WYSIWYG editor.
   *
   * @When I click the :action button in the :instanceId WYSIWYG editor
   */
  public function iClickTheButtonInTheWysiwygEditor($action, $instanceId) {
    $driver = $this->getSession()->getDriver();

    $editorType = $this->getEditorType($instanceId);
    $toolbarElement = $this->getWysiwygToolbar($instanceId, $editorType);

    // Click the action button.
    $button = $toolbarElement->find("xpath", "//a[starts-with(@title, '$action')]");
    if (!$button) {
      throw new \Exception(sprintf('Button "%s" was not found on the page %s', $action, $this->getSession()->getCurrentUrl()));
    }
    $button->click();
    $driver->wait(1000, TRUE);
  }

  /**
   * Clicks the given CSS selector in a WYSIWYG editor.
   *
   * @When I click the :cssSelector element in the :instanceId WYSIWYG editor
   */
  public function iClickTheElementInTheWysiwygEditor($cssSelector, $instanceId) {
    $editorType = $this->getEditorType($instanceId);
    $instance = $this->getWysiwygInstance($instanceId);

    if ($editorType === 'ckeditor') {
      $javascript = <<<JS
const element = {$instance}.document.findOne('{$cssSelector}').getParent();
{$instance}.getSelection().selectElement(element);
JS;
    }
    else {
      // @todo This is tinyMCE specific. We should probably do a switch statement
      // based on $editorType.
      $editor_iframe_id = $instanceId . '_ifr';

      // This Javascript only works on Chrome - not Firefox.
      $javascript  = "jQuery('#{$editor_iframe_id}').each(function() {";
      $javascript .= "  jQuery('{$cssSelector}', this.contentWindow.document || this.contentDocument).click();";
      $javascript .= "});";
    }

    $this->getSession()->executeScript($javascript);
  }

  /**
   * Expands the toolbar in the WYSIWYG editor.
   *
   * @When I expand the toolbar in the :instanceId WYSIWYG editor
   */
  public function iExpandTheToolbarInTheWysiwygEditor($instanceId) {
    $editorType = $this->getEditorType($instanceId);
    $toolbarElement = $this->getWysiwygToolbar($instanceId, $editorType);

    // @todo This is tinyMCE specific. We should probably switch on
    // $editorType.
    $action = 'Show/hide toolbars';

    // Expand wysiwyg toolbar.
    $button = $toolbarElement->find("xpath", "//a[starts-with(@title, '$action')]");
    if (!$button) {
      throw new \Exception(sprintf('Button "%s" was not found on the page %s', $action, $this->getSession()->getCurrentUrl()));
    }
    if (strpos($button->getAttribute('class'), 'cke_button_on') !== FALSE) {
      $button->click();
    }
  }

  /**
   * Asserts the text in a WYSIWYG editor.
   *
   * @Then I should see :text in the :instanceId WYSIWYG editor
   */
  public function assertContentInWysiwygEditor($text, $instanceId) {
    $instance = $this->getWysiwygInstance($instanceId);
    $content = $this->getSession()->evaluateScript("return $instance.getContent()");
    if (strpos($text, $content) === FALSE) {
      throw new \Exception(sprintf('The text "%s" was not found in the "%s" WYSWIYG editor on the page %s', $text, $instanceId, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * Asserts that the given text isn't present in a WYSIWYG editor.
   *
   * @Then I should not see :text in the :instanceId WYSIWYG editor
   */
  public function assertContentNotInWysiwygEditor($text, $instanceId) {
    $instance = $this->getWysiwygInstance($instanceId);
    $content = $this->getSession()->evaluateScript("return $instance.getContent()");
    if (strpos($text, $content) !== FALSE) {
      throw new \Exception(sprintf('The text "%s" was found in the "%s" WYSWIYG editor on the page %s', $text, $instanceId, $this->getSession()->getCurrentUrl()));
    }
  }

}
