Feature: Add text widget
  In order to put additional text on a page (beyond the main content)
  As a site administrator
  I need to be able to add a text widget
 
  @api @javascript @panopoly_widgets @panopoly2
  Scenario: Add text to a page
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
      And I am viewing a landing page
    When I create new "Text" content in the Panels IPE
    Then I should see "Create new Text content"
    When I fill in the following:
      | Text format | restricted_html         |
      | Text        | Testing text body field |
      And I press "Create and Place" in the "IPE" region
      And I wait for the block form to load in the Panels IPE
    When I fill in the following:
      | Title   | Text widget title |
      | Region  | contentmain       |
      And I press "Add"
      And I click "Save" in the "IPE" region
    Then I should see "Text widget title"
      And I should see "Testing text body field"
