@javascript @account @smoke @auth @pbsaprod @hmaeprod @bbwkwprod @mckwprod @hmkwprod @hmsaprod @flkwprod @flaeprod @bbwaeprod
Feature: Verify the search functionality on site.

  Scenario: Verify user should be able to search with valid keyword and see relevant results
    Given I am on homepage
    Then I should see "search" in the "#alshaya-algolia-autocomplete" element
    When I fill in "search" with "Gifts"
    And I wait for the page to load
    And I should see "Search results"
    And I should see "Gift" in the "c-content__region" region
    
