@javascript @returnUser @checkoutPayment @homeDelivery @cosaeuat @coskwuat @mujikwuat @mujisauat @mujiaeuat @pbkkwuat @pbksauat @pbkaeuat @tbseguat @bpaeuat @bpkwuat @bpsauat @aeoaeuat @aeokwuat @aeosauat @westelmsauat @westelmkwuat @pbsauat @bbwsauat @mcsauat @mcaeuat @hmaeuat @mckwuat @tbskwuat @flsauat @vssauat @bbwkwuat @auth @flaeuat @bbwaeuat @vsaeuat @hmkwuat @hmsauat @flkwuat
Feature: SPC Checkout Home Delivery CC for Returning Customers using Checkout (2D) Card Payment Method

  Background:
    Given I am on "{spc_product_listing_page}"
    And I wait 10 seconds

  @cc @hd @checkout_com
  Scenario: As a returning customer, I should be able to checkout using CC (checkout.com)
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I should save the order details in the file
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    Then I should see "{order_total}"

  @cc @hd @language @desktop @checkout_com
  Scenario: As a returning customer, I should be able to checkout using CC (checkout.com) in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    And print current URL
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I should save the order details in the file
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    Then I should see "{language_order_total}"

  @cc @hd @language @mobile @checkout_com @test34
  Scenario: As a returning customer, I should be able to checkout using CC (checkout.com) in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I should save the order details in the file
