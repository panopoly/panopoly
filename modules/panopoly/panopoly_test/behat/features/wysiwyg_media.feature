Feature: Add media using the rich text editor
  In order to add visual interest to my pages
  As a site builder
  I need to be able to insert media using the WYSIWYG editor

  Background:
    Given I am logged in as a user with the "administrator" role
    When I visit "/node/add/panopoly_test_content_page"
      And I fill in the following:
        | Title       | Testing WYSIWYG        |
        | Text format | panopoly_wysiwyg_basic |

  # For some inexplicable reason this is necessary on Travis-CI. Without it,
  # the first test always fails: it can't find the "Bryant Content" region.
  @api @chrome @panopoly_wysiwyg @panopoly_wysiwyg_image @panopoly_images @drupal_private_files @panopoly2
  Scenario: Fix issues on Travis-CI (on Chrome)
    # Normally, here we'd press "Publish", however some child distribtions
    # don't use 'save_draft', and this makes this test compatible with them.
    #When I press "Publish"
    When I press "edit-submit"

  # TODO: About 10% of the time this test will hang with Firefox, so for now,
  # we will run in Chrome only on Travis-CI to get consistent builds.
  @api @javascript @chrome @panopoly_wysiwyg @panopoly_wysiwyg_image @panopoly_images @drupal_private_files @panopoly2
  Scenario: Upload an image with format and alt text
    When I type "Testing body" in the "edit-body-0-value" WYSIWYG editor
    # Upload the file.
    When I click the "Panopoly Media WYSIWYG Media Embed" button in the "edit-body-0-value" WYSIWYG editor
      And I wait for AJAX to finish
      And I switch to the frame "entity_browser_iframe_panopoly_media_wysiwyg_media_browser"
      And I click "Upload images"
      And I drop the file "test-sm.png" to "edit-upload"
      And I wait for AJAX to finish
      And I wait 1 second
      And I press "Select"
    # Fields for the image.
    And I fill in "Alternative text" with "Sample alt text"
      And I press "Select"
    # The media style selector.
    And I wait for AJAX to finish
      And I select "view_mode:media.embed_medium" from "Display as"
      And I wait for AJAX to finish
    # @todo keeps hanging onto previous element, not current one.
    # Then the "attributes[data-entity-embed-display-settings][alt]" field placeholder should contain "Sample Alt text"
      And I press "Embed" in the "Dialog buttons" region
    # Save the whole node.
    #When I press "Publish"
    When I press "edit-submit"
    # See the image on the view page.
    Then I should see the "img" element in the "Bryant Content" region
      And I should see the image alt "Sample alt text" in the "Bryant Content" region

  # TODO: About 10% of the time this test will hang with Firefox, so for now,
  # we will run in Chrome only on Travis-CI to get consistent builds.
  @api @javascript @chrome @panopoly_wysiwyg @panopoly_wysiwyg_image @panopoly_images @drupal_private_files @panopoly2
  Scenario: The second alt/title text sticks
    When I type "Testing body" in the "edit-body-0-value" WYSIWYG editor
    When I click the "Panopoly Media WYSIWYG Media Embed" button in the "edit-body-0-value" WYSIWYG editor
      And I wait for AJAX to finish
      And I switch to the frame "entity_browser_iframe_panopoly_media_wysiwyg_media_browser"
      And I click "Upload images"
      And I drop the file "test-sm.png" to "edit-upload"
      And I wait for AJAX to finish
      And I wait 1 second
      And I press "Select"
    # We need to set the alt/title text differently in the two steps that ask
    # for it - so, that we can test that the 2nd overrides.
    When I fill in "Alternative text" with "Sample alt text"
      And I press "Select"
    And I wait for AJAX to finish
    # @todo keeps hanging onto previous element, not current one.
    # Then the "attributes[data-entity-embed-display-settings][alt]" field placeholder should contain "Sample Alt text"
    When I fill in the following:
        | attributes[data-entity-embed-display-settings][alt]   | Second alt text   |
    And I press "Embed" in the "Dialog buttons" region
    # Save the whole node.
    When I press "edit-submit"
    # See the image with the 2nd alt text.
    Then I should see the "img" element in the "Bryant Content" region
      And I should see the image alt "Second alt text" in the "Bryant Content" region
    # Next, we edit the node again, so we can verify that the second
    # alt text will load when editing the image again.
    When I click "Edit" in the "Tabs" region
      And I click the "drupal-entity" element in the "edit-body-0-value" WYSIWYG editor
      And I click the "Panopoly Media WYSIWYG Media Embed" button in the "edit-body-0-value" WYSIWYG editor
    Then the "attributes[data-entity-embed-display-settings][alt]" field should contain "Second Alt text"

  # TODO: About 10% of the time this test will hang with Firefox, so for now,
  # we will run in Chrome only on Travis-CI to get consistent builds.
  @api @javascript @chrome @panopoly_wysiwyg @panopoly_wysiwyg_image @panopoly_images @drupal_private_files @panopoly2
  Scenario: HTML entities in alt/title text get decoded/encoded correctly
    When I type "Testing body" in the "edit-body-0-value" WYSIWYG editor
    When I click the "Panopoly Media WYSIWYG Media Embed" button in the "edit-body-0-value" WYSIWYG editor
      And I wait for AJAX to finish
      And I switch to the frame "entity_browser_iframe_panopoly_media_wysiwyg_media_browser"
      And I click "Upload images"
      And I drop the file "test-sm.png" to "edit-upload"
      And I wait for AJAX to finish
      And I wait 1 second
      And I press "Select"
    # We need to set the alt/title text differently in the two steps that ask
    # for it - so, that we can test that the 2nd overrides.
    When I fill in "Alternative text" with "Alt & some > \"character's\" <"
    And I press "Select"
    And I wait for AJAX to finish
    And I press "Embed" in the "Dialog buttons" region
    # Save the whole node.
    When I press "edit-submit"
    # See the image with the 2nd alt text.
    Then I should see the "img" element in the "Bryant Content" region
      And I should see the image alt "Alt & some > \"character's\" <" in the "Bryant Content" region
    # Next, we edit the node again, so we can verify that the second
    # alt text will load when editing the image again.
    When I click "Edit" in the "Tabs" region
    And I click the "drupal-entity" element in the "edit-body-0-value" WYSIWYG editor
    And I click the "Panopoly Media WYSIWYG Media Embed" button in the "edit-body-0-value" WYSIWYG editor
    Then the "attributes[data-entity-embed-display-settings][alt]" field placeholder should contain "Alt & some > \"character's\" <"

  @api @javascript @chrome @panopoly_wysiwyg @panopoly_wysiwyg_image @panopoly_images @drupal_private_files
  Scenario: Use an image from elsewhere on the web
    When I type "Testing body" in the "edit-body-0-value" WYSIWYG editor
    When I click the "Panopoly Media WYSIWYG Media Embed" button in the "edit-body-0-value" WYSIWYG editor
      And I wait for AJAX to finish
      And I switch to the frame "entity_browser_iframe_panopoly_media_wysiwyg_media_browser"
      And I click "Web"
    Then I should see "File URL or media resource"
    When I fill in "File URL or media resource" with "https://www.drupal.org/files/drupal_logo-blue.png"
      And I press "Next" in the "Media web tab" region
    Then I should see "Destination"
    # Select the destination (public/private files).
    When I select the radio button "Public local files served by the webserver."
      And I press "Next" in the "Media web tab" region
    Then I should see a "#edit-submit" element
      And I should see the "Crop" button
    # Fields for the image.
    When I fill in the following:
        | Alt Text   | Sample alt text   |
        | Title Text | Sample title text |
      And I press "Save"
    # The media style selector.
    When I wait 2 seconds
      And I switch to the frame "mediaStyleSelector"
      And I select "Quarter Size" from "format"
    Then the "Alt Text" field should contain "Sample Alt text"
      And the "Title Text" field should contain "Sample Title text"
      And I click the fake "Submit" button
      And I switch out of all frames
      And I press "edit-submit"
    # See the image on the view page.
    Then I should see the "img" element in the "Bryant Content" region
      And I should see the image alt "Sample alt text" in the "Bryant Content" region

  # TODO: About 10% of the time this test will hang with Firefox, so for now,
  # we will run in Chrome only on Travis-CI to get consistent builds.
  @api @javascript @chrome @panopoly_wysiwyg @panopoly_wysiwyg_video @panopoly_widgets @panopoly2
  Scenario: Add a YouTube video
    When I type "Testing body" in the "edit-body-0-value" WYSIWYG editor
    # Upload the file.
    When I click the "Panopoly Media WYSIWYG Media Embed" button in the "edit-body-0-value" WYSIWYG editor
      And I wait for AJAX to finish
      And I switch to the frame "entity_browser_iframe_panopoly_media_wysiwyg_media_browser"
      And I click "Add remote video"
      And I fill in "Video URL" with "https://www.youtube.com/watch?v=W_-vFa-IyB8"
      And I press "Select"
    Then I should see "Minecraft: Development history"
    And I select "view_mode:media.embed_large" from "Display as"
      And I wait for AJAX to finish
      And I press "Embed" in the "Dialog buttons" region
    # Save the whole node.
    #When I press "Publish"
    When I press "edit-submit"
    # See the image on the view page.
    Then I should see the "iframe.media-oembed-content" element in the "Bryant Content" region

  # TODO: About 10% of the time this test will hang with Firefox, so for now,
  # we will run in Chrome only on Travis-CI to get consistent builds.
  @api @javascript @chrome @panopoly_wysiwyg @panopoly_wysiwyg_video @panopoly_widgets  @panopoly2
  Scenario: Add a Vimeo video
    When I type "Testing body" in the "edit-body-0-value" WYSIWYG editor
    # Upload the file.
    When I click the "Panopoly Media WYSIWYG Media Embed" button in the "edit-body-0-value" WYSIWYG editor
      And I wait for AJAX to finish
      And I switch to the frame "entity_browser_iframe_panopoly_media_wysiwyg_media_browser"
      And I click "Add remote video"
      And I fill in "Video URL" with "https://vimeo.com/59482983"
      And I press "Select"
      And I wait for AJAX to finish
    Then I should see "Panopoly by Troels Lenda"
    And I select "view_mode:media.embed_large" from "Display as"
      And I wait for AJAX to finish
      And I press "Embed" in the "Dialog buttons" region
    # Save the whole node.
    #When I press "Publish"
    When I press "edit-submit"
    # See the image on the view page.
    Then I should see the "iframe.media-oembed-content" element in the "Bryant Content" region
