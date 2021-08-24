Feature: Add table widget
  In order to put a table on a page
  As a site administrator
  I need to be able to use the table widget

  Background:
    Given I am logged in as a user with the "administrator" role
      And I am viewing a landing page
    When I click "Layout"
      And I click "Add block in Section 1, Content region"
    And I click "Create custom block"
    And I click "Table"

  @api @javascript @panopoly2 @panopoly_widgets_table
  Scenario: Add table to a page
    When I fill in the following:
      | Title                 | Widget title |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][0][0] | c-1-r-1      |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][0][1] | c-2-r-1      |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][1][0] | c-1-r-2      |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][1][1] | c-2-r-2      |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][2][0] | c-1-r-3      |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][2][1] | c-2-r-3      |
      And I press "Save" in the "Settings tray" region
      And I press "Save layout"
    Then I should see "Widget title"
      And I should see "c-2-r-3"

  @api @javascript @panopoly2 @panopoly_widgets_table
  Scenario: Add table with custom columns and rows
    Given I press "Change number of rows/columns."
    When I fill in the following:
      | Title                                                            | Widget title |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][rebuild][cols] | 3            |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][rebuild][rows] | 2            |
      And I press "Rebuild Table"
    Then I should see "Table structure rebuilt."
    When I fill in the following:
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][0][0] | c-1-r-1      |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][0][1] | c-2-r-1      |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][0][2] | c-3-r-1      |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][1][0] | c-1-r-2      |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][1][1] | c-2-r-2      |
      | settings[block_form][field_panopoly_widgets_table][0][tablefield][table][1][2] | c-3-r-2      |
      And I press "Save" in the "Settings tray" region
      And I press "Save layout"
    Then I should see "Widget title"
      And I should see "c-3-r-2"
