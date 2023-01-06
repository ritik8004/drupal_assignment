@javascript @account @smoke @auth @search @coskwuat @mujikwuat @mujisauat @mujiaeuat @pbkkwuat @pbksauat @pbkaeuat @tbseguat @bpaeuat @bpkwuat @bpsauat @westelmaeuat @westelmsauat @westelmkwuat @pbsauat @hmaeuat @mckwuat @mcsauat @mcaeuat @flsauat @bbwsauat @hmkwuat @hmsauat @flkwuat @flaeuat @bbwaeuat @vssauat @vsaeuat
Feature: Test search functionality

  Background:
    Given I am on "{spc_basket_page}"
    And I wait for element "#block-page-title"

  @desktop
  Scenario: Verify user should be able to search with valid keyword and see relevant results
    And I should see an "#alshaya-algolia-autocomplete" element
    When I fill in "search" with "{spc_search_keyword}"
    And I wait for AJAX to finish
    And I wait for element ".c-products__item"
    And I should see "Search results"
    Then I should see an ".c-products-list" element
    And I should see "{spc_search_keyword}" in the "#hits" element
    And the element ".alshaya_search_gallery" should exist
    And the element ".js-pager__items" should exist
    And the element ".pager__item button" should exist
    And I click jQuery ".js-pager__items .button" element on page
    And I wait for AJAX to finish
    Then I click on "#react-algolia-searchbar-clear-button" element

  @desktop
  Scenario: Verify Search Results Message for No Results
    When I fill in "search" with "ABCDE"
    And I wait for AJAX to finish
    And I wait for element ".hits-empty-state"
    Then the element ".hits-empty-state" should exist

  @desktop
  Scenario: As a Guest, I should be able to see the header on SRP
    Then I should see the link "{create_account}"
    Then I should see the link "{find_store}"
    Then I should see the link "{language_link}"
    Then I should see an ".plp-facet-product-filter" element

  @desktop @language
  Scenario: As a Guest, I should be able to see the header and the footer on SRP
    When I follow "{language_link}"
    And I wait for the page to load
    When I fill in "search" with "{spc_search_keyword}"
    Then I should see an ".stores-finder" element
    Then I should see an ".register-link" element
    Then I should see an ".plp-facet-product-filter" element

  @desktop @language
  Scenario: As a Guest, I should be able to select filters on PLP page
    When I follow "{language_link}"
    And I wait for the page to load
    And the element ".plp-facet-product-filter" should exist