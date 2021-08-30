Feature: Add submenu widget
  In order to make better navigation of the site
  As a site administrator
  I need to be able to add a submenu widget

  Background:
    Given I am logged in as a user with the "administrator" role
    # Create two pages with a parent-child relationship in the menu.
    When I visit "/node/add/panopoly_test_content_page"
      And I fill in the following:
        | Title               | Parent page     |
        | Text format         | restricted_html |
        | body[0][value]      | Testing body    |
      And I click "Menu settings"
      And I check the box "menu[enabled]"
      And I fill in "Rodzic" for "menu[title]"
      And I select "<Main navigation>" from "menu[menu_parent]"
      # Normally, here we'd press "Publish", however some child distribtions
      # don't use 'save_draft', and this makes this test compatible with them.
      #And I press "Publish"
      And I press "edit-submit"
    When I visit "/node/add/panopoly_test_content_page"
      And I fill in the following:
        | Title               | Child page      |
        | Text format         | restricted_html |
        | body[0][value]      | Testing body    |
      And I click "Menu settings"
      And I check the box "menu[enabled]"
      And I fill in "Dziecko" for "menu[title]"
      And I select "-- Rodzic" from "menu[menu_parent]"
      And I press "edit-submit"

  @api @javascript @panopoly_widgets @panopoly_widgets_menu @panopoly2
  Scenario: Add submenu widget to page
    Given I am viewing a landing page
    When I click "Layout"
      And I click "Add block in Section 1, Content region"
      And I click "Add Main navigation"
      And I fill in the following:
        | settings[label] | Submenu title |
    When I press "Menu levels"
    When I select "1" from "settings[level]"
      And I check the box "settings[expand_all_items]"
    And I press "Save" in the "Settings tray" region
      And I press "Save layout"
    Then I should see "Rodzic"
      And I should see "Dziecko"
    # Change the starting level to show the parent too.
    When I click "Layout"
      And I press "Open Submenu title configuration options" in the "Layout Builder"
      And I click "Configure" in the "Layout Builder" region
    When I select "1" from "settings[level]"
    And I press "Save" in the "Settings tray" region
      And I press "Save layout"
    Then I should see "Rodzic"
      And I should see "Dziecko"
    When I click "Layout"
      And I press "Open Submenu title configuration options" in the "Layout Builder"
      And I click "Configure" in the "Layout Builder"
      And the "settings[expand_all_items]" checkbox should be checked
      And I uncheck the box "settings[expand_all_items]"
    And I press "Save" in the "Settings tray" region
      And I press "Save layout"
    When I click "Layout"
      And I press "Open Submenu title configuration options" in the "Layout Builder" region
      And I click "Configure" in the "Boxton Content" region
      And the "settings[expand_all_items]" checkbox should not be checked

