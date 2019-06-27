Feature: Add text widget
  In order to put additional text on a page (beyond the main content)
  As a site administrator
  I need to be able to add a text widget
 
  @api @javascript @panopoly_widgets @panopoly2
  Scenario: Add text to a page
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
      And I am viewing a landing page
	When I click "Layout"
	  And I click "Add Block"
	  And I click "Create custom block"
	  And I click "Text"
    Then I should see "Configure block"
    When I fill in the following:
	  | Title       | Text widget title       |
      | Text format | restricted_html         |
      | Text        | Testing text body field |
      And I press "Add Block" in the "Settings tray" region
	  And I press "Save layout"
    Then I should see "Text widget title"
      And I should see "Testing text body field"
