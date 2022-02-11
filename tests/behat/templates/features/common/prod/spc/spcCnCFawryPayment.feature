@javascript @checkoutPayment @auth @clickCollect @fawry
Feature: SPC Checkout using Click & Collect store for Guest user using Fawry payment method

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 5 seconds
    And I wait for the page to load

  @cnc @fawry
  Scenario: As a Guest user, I should be able to checkout using click and collect with Fawry payment
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
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
    Then the "payment-method-checkout_com_upapi_fawry" checkbox should be checked
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cnc @language @desktop @fawry
  Scenario: As a Guest user, I should be able to checkout using click and collect with Fawry payment in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
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
    Then the "payment-method-checkout_com_upapi_fawry" checkbox should be checked
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cnc @language @mobile @fawry
  Scenario: As a Guest user, I should be able to checkout using click and collect with Fawry payment
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
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
    Then the "payment-method-checkout_com_upapi_fawry" checkbox should be checked
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist
