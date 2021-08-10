Feature: Add links widget
  In order to add a list with links
  As a site administrator
  I need to be able to use the links widget
 
  @api @javascript @panopoly_widgets @panopoly2
  Scenario: Add links
    Given I am logged in as a user with the "administrator" role
      And I am viewing a landing page
    When I click "Layout"
      And I click "Add block in Section 1, Content region"
      And I click "Create custom block"
      And I click "Links"
    Then I should see "Block description Links"
    When I fill in the following:
      | Title     | Testing link title                 |
      | Link text | Testing url title                  |
      | URL       | http://drupal.org/project/panopoly |
      And I press "Save" in the "Settings tray" region
      And I press "Save layout"
    Then I should see "Testing link title"
      And I should see "Testing url title"
    When I follow "Testing url title" in the "Boxton Content" region
    Then the url should match "/project/panopoly"

