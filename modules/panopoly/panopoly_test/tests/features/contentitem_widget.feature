Feature: Add content item
  In order to put in a particular content item on a page
  As a site administrator
  I need to be able to choose which content item
 
  @api @javascript @panopoly_widgets
  Scenario: Add content item (as "Fields")
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
      And "panopoly_test_page" nodes:
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
      And I should see "January 1, 2001"
      And I should see "Posted by Anonymous"

  @api @javascript @panopoly_widgets
  Scenario: Add content item (as "Content")
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
      And "panopoly_test_page" nodes:
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
      And I select the radio button "Content"
      And I select the radio button "Full content"
      And I press "Save" in the "CTools modal" region
      And I press "Save"
      And I wait for the Panels IPE to deactivate
    Then I should see "Test Page 1"
      And I should see "Test body"
    # Now, if we override the title, the page title should disappear and be
    # replaced by our override.
    When I customize this page with the Panels IPE
      And I click "Settings" in the "Boxton Content" region
    When I fill in the following:
      | widget_title   | Test Widget Title |
      And I press "Save" in the "CTools modal" region
      And I press "Save"
      And I wait for the Panels IPE to deactivate
    Then I should see "Test Widget Title"
     And I should not see "Test Page 1"
