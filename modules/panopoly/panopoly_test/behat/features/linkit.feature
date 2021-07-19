Feature: Link to page on the site
  In order to create links to my pages
  As a site builder
  I need to be able to use the Linkit function

  Background:
    Given I am logged in as a user with the "administrator" role
      And a "panopoly_test_content_page" with the title "Linkit Target"
    When I visit "/node/add/panopoly_test_content_page"
      And I fill in the following:
        | Title       | Testing Linkit         |
        | Text format | panopoly_wysiwyg_basic |

  @api @javascript @panopoly_wysiwyg @panopoly2
  Scenario: Add a link to an internal page
    When I type "Testing Linkit link" in the "edit-body-0-value" WYSIWYG editor
      And I select the text in the "edit-body-0-value" WYSIWYG editor
    When I click the "Link" button in the "edit-body-0-value" WYSIWYG editor
      And I select the first autocomplete option for "target" on the "attributes[href]" field
    And I fill in "attributes[title]" with "Testing title"
      And I press "Save" in the "Dialog buttons" region
      # Normally, here we'd press "Publish", however some child distribtions
      # don't use 'save_draft', and this makes this test compatible with them.
      #And I press "Publish"
      And I press "edit-submit"
    Then I should see "Testing Linkit link" in the "a" element with the "title" attribute set to "Testing title" in the "Bryant Content" region
    When I click "Testing Linkit link"
    Then the "h1" element should contain "Linkit Target"

  @api @javascript @panopoly_wysiwyg @panopoly2
  Scenario: Add a link to an external page
    When I click the "Link" button in the "edit-body-0-value" WYSIWYG editor
    And I wait for AJAX to finish
      And I fill in "attributes[href]" with "https://drupal.org/project/panopoly"
      And I fill in "attributes[title]" with "Testing title"
      And I press "Save" in the "Dialog buttons" region
      # Normally, here we'd press "Publish", however some child distribtions
      # don't use 'save_draft', and this makes this test compatible with them.
      #And I press "Publish"
      And I press "edit-submit"
    Then I should see "https://drupal.org/project/panopoly" in the "a" element with the "title" attribute set to "Testing title" in the "Bryant Content" region
    # Drupal.org Perimeter X is causing random failures.
    #When I click "https://drupal.org/project/panopoly"
    #Then the "h1" element should contain "Panopoly"
