@javascript @smoke @desktop @newPdp @mcaeuat @aeoaeuat
Feature: Testing new PDP page for desktop

  Background:
    Given I am on "{np_plp_page}"
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load

  Scenario: To verify user is able to see product details
    Then I should see a ".magv2-pdp-description-wrapper" element on page
    Then I should see a ".magv2-pdp-description-wrapper .magv2-pdp-section-title" element on page
    And I should see "product details"
    And the element ".magv2-pdp-description-wrapper .magv2-pdp-section-text.short-desc" should exist
    And the element ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" should exist
    And I should see "read more"

  Scenario: To verify user is able to see product details when clicking on read more link
    Then I should see a ".magv2-pdp-description-wrapper" element on page
    And the element ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" should exist
    When I click on ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" element
    And I wait 5 seconds
    Then I should see a ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container .magv2-desc-popup-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper a.close" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper .magv2-pdp-title-wrapper .magv2-pdp-title" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper .magv2-pdp-price .magv2-pdp-price-container" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper .magv2-pdp-price-wrapper .magv2-pdp-price-currency" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper .magv2-pdp-price-wrapper .magv2-pdp-price-amount" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-pdp-item-code-attribute" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-pdp-item-code-attribute .magv2-desc-popup-pdp-item-code-label" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-pdp-item-code-attribute .magv2-desc-popup-pdp-item-code-value" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-description-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-description-wrapper .desc-label-text-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-description-wrapper .desc-label-text-wrapper .magv2-pdp-section-text " element on page
    When I click on ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper a.close" element
    And I wait 5 seconds
    Then the element ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper a.close" should not exist
    Then the element ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container .magv2-desc-popup-wrapper" should not exist

  Scenario: To verify, add to cart button is visible and is sticky
    Then I should see a "#add-to-cart-main" element on page
    When I scroll to the ".magv2-pdp-click-and-collect-wrapper" element
    And I wait 3 seconds
    Then I should see a ".magv2-pdp-sticky-header .magv2-header-wrapper #sticky-header-btn #add-to-cart-sticky" element on page

  @language
  Scenario: To verify user is able to see product details
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I should see a ".magv2-pdp-description-wrapper" element on page
    Then I should see a ".magv2-pdp-description-wrapper .magv2-pdp-section-title" element on page
    And I should see "product details"
    And the element ".magv2-pdp-description-wrapper .magv2-pdp-section-text.short-desc" should exist
    And the element ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" should exist
    And I should see "اقرأ المزيد"

  @language
  Scenario: To verify user is able to see product details when clicking on read more link
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I should see a ".magv2-pdp-description-wrapper" element on page
    And the element ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" should exist
    When I click on ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" element
    And I wait 5 seconds
    Then I should see a ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container .magv2-desc-popup-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper a.close" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper .magv2-pdp-title-wrapper .magv2-pdp-title" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper .magv2-pdp-price .magv2-pdp-price-container" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper .magv2-pdp-price-wrapper .magv2-pdp-price-currency" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper .magv2-pdp-price-wrapper .magv2-pdp-price-amount" element on page
    Then I should see a ".magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper .magv2-compact-detail-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-pdp-item-code-attribute" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-pdp-item-code-attribute .magv2-desc-popup-pdp-item-code-label" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-pdp-item-code-attribute .magv2-desc-popup-pdp-item-code-value" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-description-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-description-wrapper .desc-label-text-wrapper" element on page
    Then I should see a ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-content-wrapper .magv2-desc-popup-description-wrapper .desc-label-text-wrapper .magv2-pdp-section-text " element on page
    When I click on ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper a.close" element
    And I wait 5 seconds
    Then the element ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper a.close" should not exist
    Then the element ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container .magv2-desc-popup-wrapper" should not exist

