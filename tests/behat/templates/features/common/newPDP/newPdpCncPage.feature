@javascript @smoke @desktop @newPdp @mckwuat @flkwuat @mckwprod @mcaeprod @mcsaprod @flkwprod @flaeprod @flsaprod @flaepprod @flkwpprod @flsapprod @mcaepprod @mckwpprod @mcsapprod
Feature: Testing new PDP CNC Block for desktop

  Background:
    Given I am on "{np_plp_product_page}"
    And I wait for element "#block-content"

  Scenario: To verify user is able to see CNC Block
    When I scroll to the ".magv2-pdp-standard-delivery-wrapper" element
    And the element ".magv2-pdp-click-and-collect-wrapper" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-title-wrapper.title" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-title-wrapper .magv2-pdp-section-title" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-title-wrapper .magv2-accordion" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-content-wrapper" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-content-wrapper .magv2-pdp-section-text.click-collect-detail" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-content-wrapper .instore-wrapper" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-content-wrapper .instore-wrapper #click-n-collect-search-field" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-content-wrapper .instore-wrapper #click-n-collect-search-field .location-field-wrapper .location-field" should exist

  @language
  Scenario: To verify user is able to see CNC Block
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I scroll to the ".magv2-pdp-standard-delivery-wrapper" element
    And the element ".magv2-pdp-click-and-collect-wrapper" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-title-wrapper.title" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-title-wrapper .magv2-pdp-section-title" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-title-wrapper .magv2-accordion" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-content-wrapper" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-content-wrapper .magv2-pdp-section-text.click-collect-detail" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-content-wrapper .instore-wrapper" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-content-wrapper .instore-wrapper #click-n-collect-search-field" should exist
    And the element ".magv2-pdp-click-and-collect-wrapper .magv2-click-collect-content-wrapper .instore-wrapper #click-n-collect-search-field .location-field-wrapper .location-field" should exist

  Scenario: To verify user is able to search stores for CNC
    When I scroll to the ".magv2-pdp-standard-delivery-wrapper" element
    And the element ".magv2-pdp-click-and-collect-wrapper" should exist
    Then I select the first autocomplete option for "{np_plp_store}" on the "edit-store-location" field
    And I wait for AJAX to finish
    And I wait for element ".instore-wrapper"
    Then I should see a ".magv2-click-collect-results" element on page
    Then I should see a ".magv2-click-collect-results .store-detail-wrapper" element on page
    Then I should see a ".magv2-click-collect-results .store-detail-wrapper .store-count" element on page
    Then I should see a ".magv2-click-collect-results .store-detail-wrapper .store-details" element on page
    Then I should see a ".magv2-click-collect-results .store-detail-wrapper .store-details .store-name" element on page
    Then I should see a ".magv2-click-collect-results .store-detail-wrapper .store-details .store-address" element on page
    Then I should see a ".magv2-click-collect-content-wrapper .magv2-click-collect-show-link" element on page

  @language
  Scenario: To verify user is able to search stores for CNC in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I scroll to the ".magv2-pdp-standard-delivery-wrapper" element
    And the element ".magv2-pdp-click-and-collect-wrapper" should exist
    Then I select the first autocomplete option for "{np_plp_language_store}" on the "edit-store-location" field
    And I wait for AJAX to finish
    And I wait for element ".instore-wrapper"
    Then I should see a ".magv2-click-collect-results" element on page
    Then I should see a ".magv2-click-collect-results .store-detail-wrapper" element on page
    Then I should see a ".magv2-click-collect-results .store-detail-wrapper .store-count" element on page
    Then I should see a ".magv2-click-collect-results .store-detail-wrapper .store-details" element on page
    Then I should see a ".magv2-click-collect-results .store-detail-wrapper .store-details .store-name" element on page
    Then I should see a ".magv2-click-collect-results .store-detail-wrapper .store-details .store-address" element on page
    Then I should see a ".magv2-click-collect-content-wrapper .magv2-click-collect-show-link" element on page