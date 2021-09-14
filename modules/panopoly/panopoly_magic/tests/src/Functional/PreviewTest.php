<?php

namespace Drupal\Tests\panopoly_magic\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Functional tests for Panopoly previews.
 *
 * @group PanopolyMagic
 * @group Panopoly
 */
class PreviewTest extends BrowserTestBase {

  /**
   * The profile to install.
   *
   * @var string
   */
  protected $profile = 'panopoly';

  /**
   * The theme to use.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * Disable strict config schema.
   *
   * @var bool
   *
   * @todo Remove this! It's missing schema from contrib we use.
   */
  protected $strictConfigSchema = FALSE;

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    'panopoly_magic_preview_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->createNode([
      'type' => 'panopoly_landing_page',
      'title' => 'Test landing page',
    ]);
  }

  /**
   * Tests that all the previews from the panopoly_magic_preview_test are there.
   */
  public function testPreviews() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->drupalLogin($this->drupalCreateUser([
      'access contextual links',
      'configure any layout',
      'create and edit custom blocks',
    ]));

    $this->drupalGet('node/1');
    $page->clickLink('Layout');
    $page->find('css', '.layout-builder__region .layout-builder__link--add')
      ->click();

    // BlockWithPreview.
    $assert_session->pageTextContains("BlockWithPreview: preview block content");
    $assert_session->pageTextNotContains("BlockWithPreview: normal block content");

    // BlockWithImagePreview.
    $assert_session->elementExists('css', 'img[alt="BlockWithImagePreview: default preview image"]');

    // BlockWithAlteredSettings.
    $assert_session->pageTextNotContains("BlockWithAlteredSettings: The default message");
    $assert_session->pageTextContains("BlockWithAlteredSettings: The altered message");

    // BlockWithAlteredPreview.
    $assert_session->pageTextNotContains("BlockWithAlteredPreview: normal block content");
    $assert_session->pageTextContains("panopoly_magic_preview_test: block preview from a callback");

    // BlockWithAlteredImage.
    $assert_session->pageTextNotContains("BlockWithAlteredImage: normal block content");
    $assert_session->elementExists('css', 'img[alt="panopoly_magic_preview_test: altered preview image"]');

    $page->clickLink('Create custom block');

    // Block content type: panopoly_magic_preview_test.
    $assert_session->pageTextContains("panopoly_magic_preview_test: content entity preview field value");
  }

}
