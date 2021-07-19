Feature: Add video widget
  In order to add a video
  As a site administrator
  I need to be able to use the video widget

  Background:
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
      And I am viewing a landing page
    When I click "Layout"
      And I click "Add block in Section 1, Content region"
      And I click "Create custom block"
    And I click "Video"

  @api @javascript @panopoly_widgets @panopoly2
  Scenario: Add a YouTube video
    And I fill in "Title" with "Testing video"
    When I press "Browse"
      And I switch to the frame "entity_browser_iframe_panopoly_media_field_video_browser"
    Then I should see "You can link to media from the following services: YouTube, Vimeo"
    When I fill in "Video URL" with "https://www.youtube.com/watch?v=1TV0q4Sdxlc"
      And I press "Select"
      And I wait for AJAX to finish
      And I switch out of all frames
      And I wait 5 seconds
      # TODO: Disabled until #2264187 is fixed!
      #And I should see "Edit"
    And I press "Save" in the "Settings tray" region
    And I press "Save layout"
    Then I should see "Testing video"
    Then I should see the "iframe.media-oembed-content" element in the "Boxton Content" region

  @api @javascript @panopoly_widgets @panopoly2
  Scenario: Add a Vimeo video
    And I fill in "Title" with "Testing video"
    When I press "Browse"
    And I switch to the frame "entity_browser_iframe_panopoly_media_field_video_browser"
    Then I should see "You can link to media from the following services: YouTube, Vimeo"
    When I fill in "Video URL" with "https://vimeo.com/59482983"
      And I press "Select"
      And I wait for AJAX to finish
      And I switch out of all frames
      And I wait 5 seconds
      # TODO: Disabled until #2264187 is fixed!
      #And I should see "Edit"
    And I press "Save" in the "Settings tray" region
    And I press "Save layout"
    Then I should see "Testing video"
    Then I should see the "iframe.media-oembed-content" element in the "Boxton Content" region
