Feature: Add image widget
  In order to add an image to page
  As a site administrator
  I need to be able to use the image widget

  @api @javascript @panopoly_widgets @local_files @panopoly2
  Scenario: Add a image
    Given I am logged in as a user with the "administrator" role
      And I am viewing a landing page
    When I click "Layout"
      And I click "Add block in Section 1, Content region"
      And I click "Create custom block"
      And I click "Image"
    Then I should see "Allowed types: png gif jpg jpeg."
    When I fill in the following:
      | Title            | Testing image widget title |
      | Text format      | restricted_html            |
      | Caption          | Testing caption            |
      And I attach the file "test-sm.png" to "files[settings_block_form_field_panopoly_widgets_image_0]"
      And I fill in "Alternative text" with "Testing alt text"
      And I press "Save" in the "Settings tray" region
      And I press "Save layout"
    Then I should see "Testing image widget title"
      And I should see "Testing caption"
      And I should see the image alt "Testing alt text" in the "Boxton Content" region
      And I should not see the link "Testing alt text" in the "Boxton Content" region

  @api @javascript @panopoly_widgets @local_files @panopoly2
  Scenario: Add an image with link
    Given I am logged in as a user with the "administrator" role
      And I am viewing a landing page
    When I click "Layout"
      And I click "Add block in Section 1, Content region"
      And I click "Create custom block"
      And I click "Image"
    Then I should see "Allowed types: png gif jpg jpeg."
    When I fill in the following:
      | Title       | Testing image widget title              |
      | Text format | restricted_html                         |
      | Caption     | Testing caption                         |
      | Link        | https://www.drupal.org/project/panopoly |
      And I attach the file "test-sm.png" to "files[settings_block_form_field_panopoly_widgets_image_0]"
      And I fill in "Alternative text" with "Testing alt text"
      And I press "Save" in the "Settings tray" region
      And I press "Save layout"
    Then I should see "Testing image widget title"
      And I should see "Testing caption"
      And I should see the image alt "Testing alt text" in the "Boxton Content" region
      And I should see the link "Testing alt text" in the "Boxton Content" region
    When I follow "Testing alt text" in the "Boxton Content" region
    Then the url should match "/project/panopoly"

  # TODO: we use the @panopoly_wysiwyg tag because that is where Linkit comes
  #       from in a default install.
  @api @javascript @panopoly_widgets @panopoly_wysiwyg
  Scenario: Add an image with Linkit support
    Given I am logged in as a user with the "administrator" role
      And I am viewing a landing page
    When I customize this page with the Panels IPE
      And I click "Add new pane"
      And I click "Add image" in the "CTools modal" region
    Then I should see "Configure new Add image"
    When I click the 2nd "Search for existing content" in the "CTools modal" region
    Then I should see "Linkit"
