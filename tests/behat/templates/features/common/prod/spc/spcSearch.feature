@javascript @account @smoke @auth @flsaprod @vsaeprod @vssaprod @pbsaprod @mcsaprod @pbkwprod @bbwsaprod @pbaeprod @mcaeprod @hmaeprod @bbwkwprod @mckwprod @hmkwprod @hmsaprod @flkwprod @flaeprod @bbwaeprod
Feature: Test search functionality

  Scenario: Verify user should be able to search with valid keyword and see relevant results
    Given I am on homepage
    And I should see an "#alshaya-algolia-autocomplete" element
    When I fill in "search" with "{spc_search_keyword}"
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I should see "Search results"
    And I wait 10 seconds
    Then I should see an ".c-products-list" element
    And I should see "{spc_search_keyword}" in the "#hits" element
    And the element ".alshaya_search_gallery" should exist
    And the element ".js-pager__items" should exist
    And the element ".pager__item button" should exist
    And I click jQuery ".js-pager__items .button" element on page
    And I wait for AJAX to finish
    And I click on "#react-algolia-searchbar-clear-button" element
    And I wait 10 seconds
    Then I should be on homepage

  Scenario: Verify Search Results Message for No Results
    Given I am on homepage
    When I fill in "search" with "NO-Results"
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then the element ".dy_unit .dy-404" should exist
