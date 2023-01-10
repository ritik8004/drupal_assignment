@javascript @guest @checkoutPayment @homeDelivery @fawry @tbsegprod
Feature: SPC Checkout Home Delivery using Fawry payment method for guest user

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load

  @hd @fawry
  Scenario: As a Guest, I should be able to checkout using Fawry payment
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
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-checkout_com_upapi_fawry" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And the element ".payment-form-wrapper div.fawry-prefix-description" should exist
    And the element "input[name=fawry-email]" should exist
    And the element "input[name=fawry-mobile-number]" should exist
    Then the "payment-method-checkout_com_upapi_fawry" checkbox should be checked
    Then the element ".checkout-link.submit" should exist

  @hd @language @desktop @fawry
  Scenario: As a Guest, I should be able to checkout using Fawry payment in second language
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
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for the page to load
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-checkout_com_upapi_fawry" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And the element ".payment-form-wrapper div.fawry-prefix-description" should exist
    And the element "input[name=fawry-email]" should exist
    And the element "input[name=fawry-mobile-number]" should exist
    Then the "payment-method-checkout_com_upapi_fawry" checkbox should be checked
    Then the element ".checkout-link.submit" should exist


  @hd @language @mobile @fawry
  Scenario: As a Guest, I should be able to checkout using Fawry payment in second language
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
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I wait for the page to load
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-checkout_com_upapi_fawry" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And the element ".payment-form-wrapper div.fawry-prefix-description" should exist
    And the element "input[name=fawry-email]" should exist
    And the element "input[name=fawry-mobile-number]" should exist
    Then the "payment-method-checkout_com_upapi_fawry" checkbox should be checked
    Then the element ".checkout-link.submit" should exist

