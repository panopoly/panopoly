Feature: Add content item
  In order to put in a particular content item on a page
  As a site administrator
  I need to be able to choose which content item

  @api @javascript @panopoly_widgets @panopoly2
  Scenario: Content item autocomplete should only offer nodes of the selected type
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
      And "panopoly_test_landing_page" content:
      | title       | body      | created            | status |
      | Test Page 1 | Test body | 01/01/2001 11:00am |      1 |
      And I am viewing a landing page
    When I click "Layout"
      And I click "Add block in Section 1, Content region"
      And I click "Add Content item" in the "Offcanvas dialog"
    When I select "Any" from "settings[content_type]"
    And I wait for AJAX to finish
      And I select the first autocomplete option for "test" on the "settings[node]" field
    Then the entity reference "settings[node]" field should contain "Test Page 1"
    When I select "Test landing page" from "settings[content_type]"
    And I wait for AJAX to finish
      And I select the first autocomplete option for "test" on the "settings[node]" field
    Then the entity reference "settings[node]" field should contain "Test Page 1"
    # TODO: Test when fixed in: https://www.drupal.org/project/panopoly/issues/3064989
    # When I select "Content page" from "settings[content_type]"
    #  And I select the first autocomplete option for "test" on the "settings[node]" field
    # Then the entity reference "settings[node]" field should not contain "Test Page 1"

  @api @javascript @panopoly_widgets @panopoly2
  Scenario: Add content item (as "Fields")
    Given I am logged in as a user with the "administrator" role
    And Panopoly magic live previews are disabled
    And "panopoly_test_content_page" content:
      | title       | body      | created            | status |
      | Test Page 1 | Test body | 01/01/2001 11:00am |      1 |
      And I am viewing a landing page
    When I click "Layout"
      And I click "Add block in Section 1, Content region"
      And I click "Add Content item" in the "Offcanvas dialog"
    When I select "Test landing page" from "settings[content_type]"
      And I select the first autocomplete option for "Test Page 1" on the "settings[node]" field
      And I fill in "settings[label]" with "Test Widget Title"
      And I press "Save" in the "Offcanvas dialog" region
      And I press "Save layout"
      And I wait for the Panels IPE to deactivate
    Then I should see "Test Widget Title"
      And I should see "Test Page 1"
      And I should see "01/01/2001 - 11:00"
      # Note: nodes are created and assigned to the currently logged in user, which is a dynamic username.
      And I should see "Submitted by"

  @api @javascript @panopoly_widgets @panopoly2
  Scenario: Add content item (as "Content")
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
      And "panopoly_test_content_page" content:
      | title       | body      | created            | status |
      | Test Page 1 | Test body | 01/01/2001 11:00am |      1 |
      And I am viewing a landing page
    When I click "Layout"
      And I click "Add block in Section 1, Content region"
      And I click "Add Content item" in the "Offcanvas dialog"
    When I select "Test landing page" from "settings[content_type]"
      And I select the first autocomplete option for "Test Page 1" on the "settings[node]" field
      # TODO: Revisit in https://www.drupal.org/project/panopoly/issues/3220412
      # SEE: panopoly_magic_form_views_content_views_panes_content_type_edit_form_alter
      # And I select the radio button "Content"
      And I select the radio button "Teaser"
      And I press "Save" in the "Offcanvas dialog" region
      And I press "Save layout"
    Then I should see "Test Page 1"
      And I should see "Test body"
    # Now, if we override the title, the page title should disappear and be
    # replaced by our override.
    When I click "Layout"
      And I hover over ".block-panopoly-widgets-content-item"
      And I press "Open Content item configuration options"
      And I click "Configure" in the "Boxton Content" region
    When I fill in the following:
      | settings[label]   | Test Widget Title |
    And I press "Save" in the "Offcanvas dialog" region
    And I press "Save layout"
    Then I should see "Test Widget Title"
      # TODO: ContentItem does not override the node title with the block title.
     # And I should not see "Test Page 1"

  @api @javascript @panopoly_widgets
  Scenario: Title override should work for all view modes
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
      And "panopoly_test_page" content:
      | title       | body      | created            | status |
      | Test Page 1 | Test body | 01/01/2001 11:00am |      1 |
      And I am viewing a landing page
    When I customize this page with the Panels IPE
      And I click "Add new pane"
      And I click "Add content item" in the "CTools modal" region
    Then I should see "Configure new Add content item"
    When I select "Test Page" from "exposed[type]"
      And I fill in the following:
      | exposed[title] | Test Page 1       |
      | widget_title   | Test Widget Title |
      And I select the radio button "Content"
      And I select the radio button "Teaser"
      And I press "Save" in the "CTools modal" region
    Then I should see "Test Widget Title"
     And I should not see "Test Page 1"
    # Next, try Full content.
    When I click "Settings" in the "Boxton Content" region
      And I select the radio button "Full content"
      And I press "Save" in the "CTools modal" region
    Then I should see "Test Widget Title"
     And I should not see "Test Page 1"
    # Next, try Featured.
    When I click "Settings" in the "Boxton Content" region
      And I select the radio button "Featured"
      And I press "Save" in the "CTools modal" region
    Then I should see "Test Widget Title"
     And I should not see "Test Page 1"
    # Prevent modal popup from breaking subsequent tests.
    When I press "Save"
      And I wait for the Panels IPE to deactivate

  @api @javascript @panopoly_widgets
  Scenario: Title override should work with non-Panelizer content types
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
      And "panopoly_test_page_simple" content:
      | title       | body      | created            | status |
      | Test Page 1 | Test body | 01/01/2001 11:00am |      1 |
      And I am viewing a landing page
    When I customize this page with the Panels IPE
      And I click "Add new pane"
      And I click "Add content item" in the "CTools modal" region
    Then I should see "Configure new Add content item"
    When I select "Test Page (without Panelizer)" from "exposed[type]"
      And I fill in the following:
      | exposed[title] | Test Page 1       |
      | widget_title   | Test Widget Title |
      And I select the radio button "Content"
      And I select the radio button "Teaser"
      And I press "Save" in the "CTools modal" region
    Then I should see "Test Widget Title"
     And I should not see "Test Page 1"
    # Next, try Full content.
    When I click "Settings" in the "Boxton Content" region
      And I select the radio button "Full content"
      And I press "Save" in the "CTools modal" region
    Then I should see "Test Widget Title"
     And I should not see "Test Page 1"
    # Next, try Featured.
    When I click "Settings" in the "Boxton Content" region
      And I select the radio button "Featured"
      And I press "Save" in the "CTools modal" region
    Then I should see "Test Widget Title"
     And I should not see "Test Page 1"
    # Prevent modal popup from breaking subsequent tests.
    When I press "Save"
      And I wait for the Panels IPE to deactivate

  @api @javascript @panopoly_widgets
  Scenario: Content item widget continues to work after renaming content
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
      And "panopoly_test_page" content:
      | title       | body      | created            | status |
      | Test Page 1 | Test body | 01/01/2001 11:00am |      1 |
      And I am viewing a landing page
    When I customize this page with the Panels IPE
      And I click "Add new pane"
      And I click "Add content item" in the "CTools modal" region
    Then I should see "Configure new Add content item"
    When I select "Test Page" from "exposed[type]"
      And I fill in the following:
      | exposed[title] | Test Page 1       |
      | widget_title   | Test Widget Title |
      And I press "Save" in the "CTools modal" region
      And I press "Save"
      And I wait for the Panels IPE to deactivate
    Then I should see "Test Widget Title"
      And I should see "Test Page 1"
    When follow "Test Page 1"
      And I click "Edit" in the "Tabs" region
      And I fill in "Test Page 2" for "Title"
      And I press "edit-submit"
    # @todo: Find a better way to get back to the original page.
    When I move backward one page
      And I move backward one page
      And I move backward one page
      And I reload the page
    Then I should see "Test Widget Title"
      And I should see "Test Page 2"
    # Check that the edit form shows the new title now too.
    When I customize this page with the Panels IPE
      And I click "Settings" in the "Boxton Content" region
    Then the "exposed[title]" field should contain "Test Page 2"
    # Make sure that saving without changes works OK.
    When I press "Save" in the "CTools modal" region
      And I press "Save"
      And I wait for the Panels IPE to deactivate
    Then I should see "Test Widget Title"
      And I should see "Test Page 2"
