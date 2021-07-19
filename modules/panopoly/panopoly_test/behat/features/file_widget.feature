Feature: Add a file to a page
  In order to add a file to a page
  As a site administrator
  I need to be able to use the file widget

  @api @javascript @panopoly_widgets @local_files @panopoly2
  Scenario: Add a file widget
    Given I am logged in as a user with the "administrator" role
      And there are no files with uri "public://file/test.txt"
      And Panopoly magic live previews are disabled
      And I am viewing a landing page
	When I click "Layout"
	  And I click "Add block in Section 1, Content region"
	  And I click "Create custom block"
	  And I click "File"
    Then I should see "Allowed types: txt doc docx xls xlsx pdf ppt pptx pps ppsx odt ods odp."
    When I fill in the following:
      | Title       | Testing file title  |
      | Text format | restricted_html     |
      | Text        | Testing file text   |
      And I attach the file "test.txt" to "files[settings_block_form_field_panopoly_widgets_file_0]"
      #And I press "Upload"
      And I press "Save" in the "Settings tray" region
	  And I press "Save layout"
    Then I should see "Testing file title"
      And I should see "Testing file text"
      And I should see the link "test.txt"

