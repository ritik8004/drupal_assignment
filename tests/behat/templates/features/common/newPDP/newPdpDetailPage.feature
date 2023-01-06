@javascript @smoke @desktop @newPdp @mckwuat @flkwuat @mckwprod @mcaeprod @mcsaprod @flkwprod @flaeprod @flsaprod @flaepprod @flkwpprod @flsapprod @mcaepprod @mckwpprod @mcsapprod
Feature: Testing new PDP page for desktop

  Background:
    Given I am on "{np_plp_page}"
    And I wait for element "#block-page-title"
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"

  Scenario: To verify user is able to see the Product info with size drawer
    Then I should see a ".magv2-main .magv2-pdp-title-wrapper" element on page
    Then I should see a ".magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container" element on page
    Then I should see a ".magv2-size-btn-wrapper" element on page
    And I should see a ".magv2-qty-container .magv2-qty-btn--up" element on page
    And I click jQuery ".magv2-qty-container .magv2-qty-btn--up" element on page
    And I wait for AJAX to finish
    And I click jQuery ".magv2-qty-container .magv2-qty-btn--down" element on page
    And I wait for AJAX to finish
    And I click the element ".magv2-size-btn-wrapper" on page
    And I wait for AJAX to finish
    Then I should see a ".overlay-select" element on page
    And I should see a ".overlay-select .size-guide" element on page
    And I click jQuery ".magv2-select-popup-content-wrapper .size-guide a" element on page
    And I wait for AJAX to finish
    Then I should see a ".ui-dialog-content .modal-content" element on page
    And I click jQuery ".ui-dialog-titlebar-close" element on page
    And I wait for AJAX to finish
    And I wait for element ".overlay-select .magv2-confirm-size-btn"
    And I should see a ".overlay-select .magv2-confirm-size-btn" element on page
    And I click jQuery ".magv2-select-popup-content-wrapper .magv2-confirm-size-btn" element on page
    And I wait for AJAX to finish
    Then I should see a ".magv2-qty-container" element on page

  Scenario: To verify user is able to see product details
    Then I should see a ".magv2-pdp-description-wrapper" element on page
    Then I should see a ".magv2-pdp-description-wrapper .magv2-pdp-section-title" element on page
    And the element ".magv2-pdp-description-wrapper .magv2-pdp-section-text.short-desc" should exist
    And the element ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" should exist
    And I click jQuery ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" element on page
    And I wait for AJAX to finish
    Then I should see a ".overlay-desc" element on page
    Then I should see a ".overlay-desc .magv2-desc-popup-container div.magv2-pdp-title" element on page
    Then I should see a ".overlay-desc .magv2-desc-popup-pdp-item-code-attribute" element on page


  Scenario: To verify user is able to see product details when clicking on read more link
    Then I should see a ".magv2-pdp-description-wrapper" element on page
    And the element ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" should exist
    When I click on ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" element
    And I wait for element ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container .magv2-desc-popup-wrapper"
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
    When I click on ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper a.close" element
    And I wait for element ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper"
    Then the element ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper a.close" should not exist
    Then the element ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container .magv2-desc-popup-wrapper" should not exist

  Scenario: To verify user is able to see the standard delivery methods
    Then I should see a ".magv2-pdp-standard-delivery-wrapper" element on page
    Then I should see a ".magv2-pdp-standard-delivery-wrapper .magv2-pdp-section-title" element on page
    And I click the element ".magv2-pdp-standard-delivery-wrapper .magv2-pdp-section-title" on page
    Then I should see a ".standard-delivery-detail" element on page

  Scenario: To verify that user is able to share the PDP page
    Then I should see a ".magv2-pdp-share-wrapper" element on page
    Then I should see a ".magv2-share-title-wrapper" element on page
    Then I should see a ".sharethis-wrapper" element on page
    Then I should see the link for ".sharethis-wrapper .st_facebook_custom"
    Then I should see the link for ".sharethis-wrapper .st_twitter_custom"
    And I click jQuery ".copy-button" element on page
    And I wait for the page to load
    And I navigate to the copied URL
    And I wait for element ".magv2-pdp-description-wrapper"
    Then I should see a ".magv2-pdp-description-wrapper" element on page

  Scenario: To verify, add to cart button is visible and is sticky
    Then I should see a "#add-to-cart-main" element on page
    When I scroll to the ".c-footer" element
    And I wait for element ".magv2-pdp-sticky-header .magv2-header-wrapper #sticky-header-btn #add-to-cart-sticky"
    Then I should see a ".magv2-pdp-sticky-header .magv2-header-wrapper #sticky-header-btn #add-to-cart-sticky" element on page

  @language
  Scenario: To verify user is able to see the Product info with size drawer
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I should see a ".magv2-main .magv2-pdp-title-wrapper" element on page
    Then I should see a ".magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container" element on page
    Then I should see a ".magv2-size-btn-wrapper" element on page
    And I click the element ".magv2-size-btn-wrapper" on page
    And I wait for AJAX to finish
    Then I should see a ".overlay-select" element on page
    And I should see a ".overlay-select .size-guide" element on page
    And I click jQuery ".magv2-select-popup-content-wrapper .size-guide a" element on page
    And I wait for AJAX to finish
    And I wait for element ".ui-dialog-content .modal-content"
    Then I should see a ".ui-dialog-content .modal-content" element on page
    And I click jQuery ".ui-dialog-titlebar-close" element on page
    And I wait for AJAX to finish
    And I wait for element ".overlay-select .magv2-confirm-size-btn"
    And I should see a ".overlay-select .magv2-confirm-size-btn" element on page
    And I click jQuery ".magv2-select-popup-content-wrapper .magv2-confirm-size-btn" element on page
    And I wait for AJAX to finish
    Then I should see a ".magv2-qty-container" element on page

  @language
  Scenario: To verify user is able to see product details with Read More
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I should see a ".magv2-pdp-description-wrapper" element on page
    Then I should see a ".magv2-pdp-description-wrapper .magv2-pdp-section-title" element on page
    And the element ".magv2-pdp-description-wrapper .magv2-pdp-section-text.short-desc" should exist
    And the element ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" should exist
    And I click jQuery ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" element on page
    And I wait for AJAX to finish
    Then I should see a ".overlay-desc" element on page
    Then I should see a ".overlay-desc .magv2-desc-popup-container div.magv2-pdp-title" element on page
    Then I should see a ".overlay-desc .magv2-desc-popup-pdp-item-code-attribute" element on page


  @language
  Scenario: To verify user is able to see product details when clicking on read more link
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I should see a ".magv2-pdp-description-wrapper" element on page
    And the element ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" should exist
    When I click on ".magv2-pdp-description-wrapper .magv2-desc-readmore-link" element
    And I wait for element ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container .magv2-desc-popup-wrapper"
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
    When I click on ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper a.close" element
    And I wait for element ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container .magv2-desc-popup-wrapper"
    Then the element ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper a.close" should not exist
    Then the element ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container .magv2-desc-popup-wrapper" should not exist

  @language
  Scenario: To verify user is able to see the standard delivery methods
    When I follow "{language_link}"
    And I wait for the page to load
    Then I should see a ".magv2-pdp-standard-delivery-wrapper" element on page
    Then I should see a ".magv2-pdp-standard-delivery-wrapper .magv2-pdp-section-title" element on page
    And I click the element ".magv2-pdp-standard-delivery-wrapper .magv2-pdp-section-title" on page
    Then I should see a ".standard-delivery-detail" element on page

  @language
  Scenario: To verify that user is able to share the PDP page
    When I follow "{language_link}"
    And I wait for the page to load
    Then I should see a ".magv2-pdp-share-wrapper" element on page
    Then I should see a ".magv2-share-title-wrapper" element on page
    Then I should see a ".sharethis-wrapper" element on page
    Then I should see the link for ".sharethis-wrapper .st_facebook_custom"
    Then I should see the link for ".sharethis-wrapper .st_twitter_custom"
    And I click jQuery ".copy-button" element on page
    And I wait for the page to load
    And I navigate to the copied URL
    And I wait for element ".magv2-pdp-description-wrapper"
    Then I should see a ".magv2-pdp-description-wrapper" element on page
