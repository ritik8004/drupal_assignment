@javascript @account @smoke @auth @pbsauat @hmaeuat @mckwuat @hmkwuat @hmsauat
Feature: Test search functionality

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load

  Scenario: As a Guest, I should be able to see the header and the footer on SRP
    When I fill in "search" with "{spc_search_keyword}"
    Then I should see the link "{create_account}"
    Then I should see the link "{find_store}"
    Then I should see the link "{language_link}"
    Then I should see "{sort_filter}"
    Then I should see "{filters}"

  Scenario: As a Guest, I should be able to sort in ascending and descending order the list on SRP
    When I fill in "search" with "{spc_search_keyword}"
    And I wait for AJAX to finish
    When I select "Name A to Z" from the filter "#alshaya-algolia-search .container-without-product #sort_by"
    And I wait for AJAX to finish
    Then I should see results sorted in ascending order
    When I select "Name Z to A" from the filter "#alshaya-algolia-search .container-without-product #sort_by"
    And I wait for AJAX to finish
    Then I should see results sorted in descending order
    When I select "Price Low to High" from the filter "#alshaya-algolia-search .container-without-product #sort_by"
    And I wait for AJAX to finish
    Then I should see results sorted in ascending price order
    When I select "Price High to Low" from the filter "#alshaya-algolia-search .container-without-product #sort_by"
    And I wait for AJAX to finish
    Then I should see results sorted in descending price order

  Scenario: As a Guest, I should be able to select filters on SRP
    When I fill in "search" with "{spc_search_keyword}"
    And I wait 10 seconds
    Then I select the filter "{filter_1}"
    And I wait 10 seconds
    And I select the filter "{filter_2}"
    And I wait 10 seconds
    And I select the filter "{filter_3}"
    And I wait 10 seconds
    Then I click on "#block-filterbar #clear-filter" element
    And I wait for AJAX to finish
    And should not see an "#block-filterbar #clear-filter" element
