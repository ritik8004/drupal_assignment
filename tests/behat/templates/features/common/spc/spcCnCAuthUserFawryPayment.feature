@javascript @checkoutPayment @auth @clickCollect @fawry @hmeguat
Feature: SPC Checkout using Click & Collect store for Authenticated user using Fawry payment method

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    When I go to in stock category page
    And I wait 10 seconds

  @cnc @fawry
  Scenario: As an authenticated user, I should be able to checkout using click and collect with Fawry payment
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
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-checkout_com_upapi_fawry" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And the element "div.fawry-prefix-description" should exist
    And the element "input[name=fawry-email]" should exist
    And the element "input[name=fawry-mobile-number]" should exist
    And I add the billing address on checkout page
    And I wait for the page to load
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I should save the order details in the file
    And the element ".order-summary-banner-fawry" should exist
    Then I should see "Reference number:"
    Then I should see "Complete payment by:"
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

  @cnc @language @desktop @fawry
  Scenario: As an authenticated user, I should be able to checkout using click and collect with Fawry payment in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-checkout_com_upapi_fawry" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And the element "div.fawry-prefix-description" should exist
    And the element "input[name=fawry-email]" should exist
    And the element "input[name=fawry-mobile-number]" should exist
    And I add the billing address on checkout page
    And I wait for the page to load
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 90 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/" page
    And I should save the order details in the file
    And the element ".order-summary-banner-fawry" should exist
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
    And I should see "{language_continue_shopping_text}"

  @cnc @language @mobile @fawry
  Scenario: As an authenticated user, I should be able to checkout using click and collect with Fawry payment
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-checkout_com_upapi_fawry" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And the element "div.fawry-prefix-description" should exist
    And the element "input[name=fawry-email]" should exist
    And the element "input[name=fawry-mobile-number]" should exist
    And I add the billing address on checkout page
    And I wait for the page to load
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 90 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/" page
    And I should save the order details in the file
    Then the element ".order-summary-banner-fawry" should exist
