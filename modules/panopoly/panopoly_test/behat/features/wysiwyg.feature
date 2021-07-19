Feature: Use rich text editor
  In order to format the content of my pages
  As a site builder
  I need to be able to use a WYSIWYG editor

  Background:
    Given I am logged in as a user with the "administrator" role
    When I visit "/node/add/panopoly_content_page"
      And I fill in the following:
        | Title       | Testing WYSIWYG        |
        | Text format | panopoly_wysiwyg_basic |

  # For some inexplicable reason this is necessary on Travis-CI. Without it,
  # the first test always fails: it can't find the "Bryant Content" region.
  @api @panopoly_wysiwyg
  Scenario: Fix issues on Travis-CI (not Chrome)
    # Normally, here we'd press "Publish", however some child distribtions
    # don't use 'save_draft', and this makes this test compatible with them.
    #When I press "Publish"
    When I press "edit-submit"

  @api @javascript @panopoly_wysiwyg @panopoly2
  Scenario Outline: Format text in the editor (first toolbar)
    When I click the "<Action>" button in the "edit-body-0-value" WYSIWYG editor
      And I type "Testing body" in the "edit-body-0-value" WYSIWYG editor
      #And I press "Publish"
      And I press "edit-submit"
    Then I should see "Testing body" in the "<Element>" element with the "<Attribute>" attribute set to "<Value>" in the "Bryant Content" region

    Examples:
      | Action                        | Element    | Attribute  | Value             |
      | Bold                          | strong     |            |                   |
      | Italic                        | em         |            |                   |
      | Strikethrough                 | s          |            |                   |
      | Insert/Remove Bulleted List   | ul > li    |            |                   |
      | Insert/Remove Numbered List   | ol > li    |            |                   |
      | Block Quote                   | blockquote |            |                   |
      # In LTR Align Left does not add a class.
      | Align Left                    | p          |            |                   |
      | Center                        | p          | class      | text-align-center |
      | Align Right                   | p          | class      | text-align-right  |

  @api @javascript @panopoly_wysiwyg @panopoly2
  Scenario Outline: Format text in the editor (advanced toolbar)
    When I expand the toolbar in the "edit-body-0-value" WYSIWYG editor
      And I click the "<Action>" button in the "edit-body-0-value" WYSIWYG editor
      And I type "Testing body" in the "edit-body-0-value" WYSIWYG editor
      #And I press "Publish"
      And I press "edit-submit"
    Then I should see "Testing body" in the "<Element>" element with the "<Attribute>" attribute set to "<Value>" in the "Bryant Content" region

    Examples:
      | Action          | Element | Attribute  | Value              |
      | Underline       | u       |            |                    |
      | Justify         | p       | class      | text-align-justify |

  @api @javascript @panopoly_wysiwyg @panopoly2
  Scenario: Format lists with indentation in the editor
    When I expand the toolbar in the "edit-body-0-value" WYSIWYG editor
    And I click the "Insert/Remove Bulleted List" button in the "edit-body-0-value" WYSIWYG editor
    And I type "Bullet 1" in the "edit-body-0-value" WYSIWYG editor
      And I press the enter key in the "edit-body-0-value" WYSIWYG editor
    And I type "Bullet 2" in the "edit-body-0-value" WYSIWYG editor
      And I press the enter key in the "edit-body-0-value" WYSIWYG editor
    And I type "Bullet 3" in the "edit-body-0-value" WYSIWYG editor
    And I click the "Increase Indent" button in the "edit-body-0-value" WYSIWYG editor
    And I press "edit-submit"
    # NOTE: The step allows an empty property, we use that to assert text in the element.
    Then I should see "Bullet 3" in the "ul > li > ul > li" element with the "" CSS property set to "" in the "Bryant Content" region
