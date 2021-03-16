@javascript @smoke @desktop @newPdp @mcaeuat @flsauat @aeoaeuat @flsauat
Feature: Testing new PDP MetaData and Add to cart for desktop

  Background:
    Given I am on "{np_plp_product_page}"
    And I wait 10 seconds
    And I wait for the page to load

  Scenario: To verify user is able to see product metadata
    Then I should see a ".magv2-main .magv2-sidebar" element on page
    Then I should see a ".magv2-main .magv2-sidebar .magv2-detail-wrapper" element on page
    Then I should see a ".magv2-main .magv2-sidebar .magv2-detail-wrapper .magv2-pdp-title" element on page
#    Then I should see a ".magv2-main .magv2-sidebar .magv2-detail-wrapper .magv2-pdp-brand-logo" element on page
    And I should see a ".magv2-main .magv2-sidebar .magv2-pdp-price" element on page
    And I should see a ".magv2-main .magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container" element on page
    And I should see a ".magv2-main .magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper .magv2-pdp-price-wrapper .magv2-pdp-price-currency" element on page
    And I should see a ".magv2-main .magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper .magv2-pdp-price-wrapper .magv2-pdp-price-amount" element on page
    And I should see a ".magv2-main .magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper .magv2-pdp-vat-text" element on page
    And I should see "{vat}"
    And I should see special price on newpdp having promotion "{np_promotion}"

  Scenario: As a Guest, I should be able add content in minicart
    When I press "add-to-cart-main"
    And I wait 10 seconds
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification img" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .qty" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .name" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification a" element
    And I wait 5 seconds
    Then I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 5 seconds
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-currency" element
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-amount" element
    And I click on "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 10 seconds
    Then I should be on "/cart"

  @language
  Scenario: To verify user is able to see product metadata
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    Then I should see a ".magv2-main .magv2-sidebar" element on page
    Then I should see a ".magv2-main .magv2-sidebar .magv2-detail-wrapper" element on page
    Then I should see a ".magv2-main .magv2-sidebar .magv2-detail-wrapper .magv2-pdp-title" element on page
#    Then I should see a ".magv2-main .magv2-sidebar .magv2-detail-wrapper .magv2-pdp-brand-logo" element on page
    And I should see a ".magv2-main .magv2-sidebar .magv2-pdp-price" element on page
    And I should see a ".magv2-main .magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container" element on page
    And I should see a ".magv2-main .magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper .magv2-pdp-price-wrapper .magv2-pdp-price-currency" element on page
    And I should see a ".magv2-main .magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper .magv2-pdp-price-wrapper .magv2-pdp-price-amount" element on page
    And I should see a ".magv2-main .magv2-sidebar .magv2-pdp-price .magv2-pdp-price-container .magv2-meta-data-wrapper .magv2-pdp-vat-text" element on page
    And I should see "{language_vat}"
    And I should see special price on newpdp having promotion "{np_promotion}"

  @language
  Scenario: As a Guest, I should be able add content in minicart in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I press "add-to-cart-main"
    And I wait 10 seconds
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification img" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .qty" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification .name" element
    Then I should see an "#block-alshayareactcartminicartblock #cart_notification .notification a" element
    And I wait 5 seconds
    Then I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 5 seconds
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-currency" element
    And I should see an "#block-alshayareactcartminicartblock #mini-cart-wrapper .cart-link-total .price .price-amount" element
    And I click on "#block-alshayareactcartminicartblock #mini-cart-wrapper .acq-mini-cart a.cart-link" element
    And I wait 10 seconds
    Then I should be on "/{language_short}/cart" page
