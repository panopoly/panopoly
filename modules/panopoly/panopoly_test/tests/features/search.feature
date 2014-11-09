Feature: Search
  In order to find content on the site
  As an anonymous user
  I should be able to find content using the site search

  Background:
    Given I am on "/demo"

  Scenario: Trying an empty search should yield a message
    When I press "Search"
    Then I should see "Search Results"
    And I should see "Enter your keywords"

  Scenario: Trying a search with no results
    When I enter "stuff" for "Enter your keywords"
    And I press "Search"
    Then I should see "Search Results"
    And I should see "0 items matched stuff"
    And I should see "Your search did not return any results."

  Scenario: Performing a search with results
    When I enter "Vegetables" for "Enter your keywords"
    And I press "Search"
    Then I should see "Search Results"
    And I should see "3 items matched Vegetables"
    And I should see "Filter by Type"
    