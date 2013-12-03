Feature: Add spotlight widget
  In order to promote content
  As a site administrator
  I need to be able to add a spotlight
 
  @api @javascript
  Scenario: Add a spotlight
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
      And I click "Add spotlight"
    Then I should see "Configure new Add spotlight"
    When I fill in the following:
      | field_basic_spotlight_items[und][0][title] | Testing item title  |
      | Link                                       | http://drupal.org   |
      | Description                                | Testing description |
      And I attach the file "panopoly.png" to "files[field_basic_spotlight_items_und_0_fid]"
      And I press "edit-return"
      And I press "Save as custom"
      And I wait for the Panels IPE to deactivate
    Then I should see "Testing description"
      And I should see "Testing item title"
      And I should not see "Spotlight"
