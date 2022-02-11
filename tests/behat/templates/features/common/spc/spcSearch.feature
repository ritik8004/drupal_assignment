@javascript @account @smoke @auth @search @mujikwuat @mujisauat @mujiaeuat @pbkkwuat @pbksauat @pbkaeuat @tbseguat @bpaeuat @bpkwuat @bpsauat @westelmaeuat @westelmsauat @westelmkwuat @pbsauat @hmaeuat @mckwuat @mcsauat @mcaeuat @flsauat @bbwsauat @hmkwuat @hmsauat @flkwuat @flaeuat @bbwaeuat @vssauat @vsaeuat
Feature: Test search functionality

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 10 seconds

  @desktop
  Scenario: As a Guest, I should be able to see the header and the footer on SRP
    When I fill in "search" with "{spc_search_keyword}"
    Then I should see the link "{create_account}"
    Then I should see the link "{find_store}"
    Then I should see the link "{language_link}"
    Then I should see "{sort_filter}"
    Then I should see "{filters}"
    Then I should see an ".plp-facet-product-filter" element

  @desktop
  Scenario: As a Guest, I should be able to sort in ascending and descending order the list on SRP
    When I fill in "search" with "{spc_search_keyword}"
    And I wait for AJAX to finish
    And the element ".plp-facet-product-filter #sort_by" should exist
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

  @desktop
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

  @desktop @language
  Scenario: As a Guest, I should be able to see the header and the footer on SRP
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I fill in "search" with "{spc_search_keyword}"
    Then I should see an ".stores-finder" element
    Then I should see an ".register-link" element
    Then I should see an ".plp-facet-product-filter" element

  @desktop @language
  Scenario: As a Guest, I should be able to select filters on PLP page
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    And the element ".plp-facet-product-filter #sort_by" should exist
    And I click jQuery ".plp-facet-product-filter #sort_by h3.c-facet__title" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then I should see an ".plp-facet-product-filter #sort_by li.facet-item" element
    And I click jQuery ".plp-facet-product-filter #sort_by li.facet-item a.facet-item__value" element on page
    And I wait for AJAX to finish
    And I should see an "#plp-hits" element
