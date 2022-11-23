@javascript @returnUser @checkoutPayment @clickCollect @cosaeuat @coskwuat @mujikwuat @mujisauat @mujiaeuat @pbkaeuat @tbseguat @bpaeuat @bpkwuat @bpsauat @aeoaeuat @aeokwuat @aeosauat @westelmsauat @westelmkwuat @hmaeuat @mcsauat @mcaeuat @flsauat @bbwsauat @vssauat @tbskwuat @auth @hmkwuat @hmsauat @flkwuat @bbwsauat @flaeuat @bbwaeuat @vsaeuat
Feature: SPC Checkout using Click & Collect store for returning customer using Checkout (2D) Card Payment Method

  Background:
    Given I am on "{spc_product_listing_page}"
    And I wait for element "#block-page-title"

  @cc @cnc @checkout_com
  Scenario: As a returning customer, I should be able to checkout using click and collect with credit card
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait 10 seconds
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I scroll to the ".spc-section-billing-address" element
    And I wait 5 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait for the page to load
    And I wait 20 seconds
    And I should save the order details in the file
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    And I should see "{subtotal}"
    Then I should see "{order_total}"

  @cc @cnc @language @mobile @checkout_com
  Scenario: As a returning customer, I should be able to checkout using click and collect with credit card
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait 5 seconds
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I scroll to the ".spc-section-billing-address" element
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait for the page to load
    And I wait 20 seconds
    And I should save the order details in the file
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    Then I should see "{language_order_total}"
