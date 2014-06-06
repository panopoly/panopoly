Feature: Add image widget
  In order to add an image to page
  As a site administrator
  I need to be able to use the image widget
 
  @api @javascript
  Scenario: Add a image
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
    When I visit "/node/add/panopoly-page"
      And I fill in the following:
        | Title               | Testing title |
        | Editor              | plain_text    |
        | body[und][0][value] | Testing body  |
      And I press "Publish"
    Then the "h1" element should contain "Testing title"
    When I customize this page with the Panels IPE
      And I click "Add new pane"
      And I click "Add image"
    Then I should see "Configure new Add image"
    When I fill in the following:
      | Title   | Testing image widget title |
      | Editor  | plain_text                 |
      | Caption | Testing caption            |
      And I attach the file "panopoly.png" to "files[field_basic_image_image_und_0]"
      And I press "Upload"
      And I fill in "Alternate text" with "Testing alt text"
      And I press "edit-return"
      And I press "Save as custom"
      And I wait for the Panels IPE to deactivate
    Then I should see "Testing image widget title"
      And I should see "Testing caption"
      And I should see the image alt "Testing alt text" in the "Bryant Sidebar" region
