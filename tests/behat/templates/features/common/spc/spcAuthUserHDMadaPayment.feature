@javascript @auth @checkoutPayment @madaPayment @homeDelivery @coskwuat @cosaeuat @coskwuat @mujikwuat @mujisauat @mujiaeuat @pbkkwuat @pbksauat @pbkaeuat @bpaeuat @bpkwuat @bpsauat @aeoaeuat @aeokwuat @aeosauat @westelmaeuat @westelmsauat @westelmkwuat @pbkwuat @flsauat @tbskwuat @mcsauat @mcaeuat @pbaeuat @pbsauat @hmaeuat @mckwuat @bbwaeuat @bbwsauat @vssauat @vsaeuat @bbwkwuat @auth @hmkwuat @hmsauat @flkwuat @flaeuat
Feature: SPC Checkout Home Delivery MADA Card Payment for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    When I am on "{spc_basket_page}"
    And I wait for the page to load

  @cc @hd @checkout_com @visa @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) with MADA Cards (VISA Card)
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "txtPassword" with "{spc_mada_password}"
    Then I press "txtButton"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{delivery_type_text}"
    Then I should see "{delivery_type}"
    Then I should see "{payment_type_text}"
    Then I should see "{cc_mada_payment_type}"
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
    And I should see "{subtotal}"
    Then I should see "{order_total}"
    And I should see "{vat}"
    And I should see "{continue_shopping_text}"

  @cc @hd @language @desktop @checkout_com @visa @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (VISA Card)
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "txtPassword" with "{spc_mada_password}"
    Then I press "txtButton"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{spc_mada_anon_username}"
    Then I should see "{language_order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{language_delivery_type_text}"
    Then I should see "{language_delivery_type}"
    Then I should see "{language_payment_type_text}"
    Then I should see "{language_cc_mada_payment_type}"
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
    And I should see "{language_subtotal}"
    Then I should see "{language_order_total}"
    And I should see "{language_vat}"
    And I should see "{language_continue_shopping_text}"

  @cc @hd @language @mobile @checkout_com @visa @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (VISA Card)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_visa_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_visa_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_visa_card_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "txtPassword" with "{spc_mada_password}"
    Then I press "txtButton"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I should save the order details in the file

  @cc @hd @checkout_com @mastercard @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) with MADA Cards (Mastercard Card)
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I select the home delivery address
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_master_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_master_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_master_card_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "txtPassword" with "{spc_mada_password}"
    Then I press "txtButton"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{delivery_type_text}"
    Then I should see "{delivery_type}"
    Then I should see "{payment_type_text}"
    Then I should see "{cc_mada_payment_type}"
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
    And I should see "{subtotal}"
    Then I should see "{order_total}"
    And I should see "{vat}"
    And I should see "{continue_shopping_text}"

  @cc @hd @language @desktop @checkout_com @mastercard @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (Mastercard Card)
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_master_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_master_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_master_card_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "txtPassword" with "{spc_mada_password}"
    Then I press "txtButton"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I should save the order details in the file
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{language_order_confirm_text}"
    Then I should see "{spc_mada_anon_username}"
    Then I should see "{language_order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{language_delivery_type_text}"
    Then I should see "{language_delivery_type}"
    Then I should see "{language_payment_type_text}"
    Then I should see "{language_cc_mada_payment_type}"
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
    And I should see "{language_subtotal}"
    Then I should see "{language_order_total}"
    And I should see "{language_vat}"
    And I should see "{language_continue_shopping_text}"

  @cc @hd @language @mobile @checkout_com @mastercard @mada
  Scenario: As an Authenticated user, I should be able to checkout using CC (checkout.com) in second language with MADA Cards (Mastercard Card)
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_mada_master_card}"
    Then I fill checkout card details having class ".spc-type-expiry input" with "{spc_mada_master_card_expiry}"
    Then I fill checkout card details having class ".spc-type-cvv input" with "{spc_mada_master_card_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link ".checkout-link.submit" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "txtPassword" with "{spc_mada_password}"
    Then I press "txtButton"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I should save the order details in the file
