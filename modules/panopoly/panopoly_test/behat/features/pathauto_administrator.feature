Feature: Test pathauto for administrator users
  In order to get nice urls
  As a site administrator
  I need to be able to trust that pathauto works consistently

  Background:
    Given I am logged in as a user with the "administrator" role
      And Panopoly magic live previews are disabled
    When I visit "/node/add/panopoly_test_content_page"
      And I fill in the following:
        | Title       | Testing title   |
        | Text format | restricted_html |
        | Body        | Testing body    |
    # Normally, here we'd press "Publish", however some child distribtions
    # don't use 'save_draft', and this makes this test compatible with them.
    #When I press "Publish"
    When I press "edit-submit"
    Then the "h1" element should contain "Testing title"

  @api @panopoly_admin @panopoly2
  Scenario: Pathauto should automatically assign an url
    Then the url should match "testing-title"

  @api @panopoly_admin @panopoly2
  Scenario: Pathauto should automatically assign a new url when changing the title
    When I click "Edit" in the "Tabs" region
      And I fill in the following:
        | Title               | Completely other title |
      And I press "Save"
    Then the url should match "completely-other-title"
    # But visiting the old URL should continue to work
    When I visit "/content/testing-title"
    Then the "h1" element should contain "Completely other title"

  # TODO work when panopoly_admin is installed.
  @api @panopoly_admin
  Scenario: My own permalink should be kept even if changing title
    When I click "Edit" in the "Tabs" region
      And I fill in the following:
        | Permalink           | my-custom-permalink |
      And I press "Save"
    Then the url should match "my-custom-permalink"
    When I click "Edit" in the "Tabs" region
      And I fill in the following:
        | Title               | Saving Title Again  |
      And I press "Save"
    Then the url should match "my-custom-permalink"
    Given I go to "my-custom-permalink"
    Then the response status code should be 200

