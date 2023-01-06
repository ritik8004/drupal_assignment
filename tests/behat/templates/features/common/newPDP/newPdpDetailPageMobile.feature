@javascript @smoke @newPdp @mobile @flaepprod @flkwpprod @flsapprod @mcaepprod @mckwpprod @mcsapprod @mckwuat @flkwuat @mckwprod @mcaeprod @mcsaprod @flkwprod
Feature: Testing new PDP page for Mobile

  Background:
    Given I am on "{np_plp_page}"
    And I wait for element "#block-page-title"
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"

  Scenario: To verify, add to cart button is visible
    Then I should see a "#add-to-cart-main" element on page

  Scenario: To verify user is able to see the Product info with size drawer
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

  Scenario: To verify user is able to see product details
    Then I should see a ".magv2-pdp-description-wrapper" element on page
    Then I should see a ".magv2-pdp-description-wrapper .magv2-pdp-section-title" element on page
    And I should see "product details"
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
    And I wait for element ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container"
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
    And I wait for element ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container"
    Then the element ".magv2-desc-popup-container .magv2-desc-popup-wrapper .magv2-desc-popup-header-wrapper a.close" should not exist
    Then the element ".magv2-popup-panel .magv2-pdp-popup-content .magv2-desc-popup-container .magv2-desc-popup-wrapper" should not exist

  @language
  Scenario: As a Guest, I should be able add content in minicart in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    And I wait for AJAX to finish
    When I press "{language_add_to_cart_link}"
    And I wait for element "#block-content"
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification img" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .qty" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .name" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification a" element
    And I wait for element "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link"
    Then I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-currency" element
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-amount" element
    And I wait for element "#spc-cart"
    Then I should be on "/{language_short}/cart" page

  Scenario: As a Guest, I should be able add content in minicart
    When I press "{language_add_to_cart_link}"
    And I wait for element "#block-content"
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification col-1 img" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification col-2 .qty" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification col-2 .name" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification col-2 a" element
    And I wait for element "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link"
    Then I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait for element "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-currency"
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-currency" element
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-amount" element
    And I click on "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait for element "#spc-cart"
    Then I should be on "/cart"
