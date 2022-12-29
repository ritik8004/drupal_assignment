@javascript @checkoutPayment @auth @clickCollect @fawry
Feature: SPC Checkout using Click & Collect store for Guest user using Fawry payment method

  Background:
    Given I am on "{spc_basket_page}"
    And I wait for element "#block-page-title"

  @cnc @fawry
  Scenario: As a Guest user, I should be able to checkout using click and collect with Fawry payment
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".edit-checkout-as-guest"
    Then I follow "checkout as guest"
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-checkout_com_upapi_fawry" element on page
    And I wait for AJAX to finish
    And I wait for element "input#payment-method-checkout_com_upapi_fawry[checked]"
    And the element ".payment-form-wrapper div.fawry-prefix-description" should exist
    And the element "input[name=fawry-email]" should exist
    And the element "input[name=fawry-mobile-number]" should exist
    And I add the billing address on checkout page
    Then the element ".checkout-link.submit a" should exist

  @cnc @language @desktop @fawry
  Scenario: As a Guest user, I should be able to checkout using click and collect with Fawry payment in second language
    When I follow "{language_link}"
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "إتمام الشراء بأمان"
    And I wait for element ".edit-checkout-as-guest"
    Then I follow "سجل الإشتراك هنا"
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-checkout_com_upapi_fawry" element on page
    And I wait for AJAX to finish
    And I wait for element "input#payment-method-checkout_com_upapi_fawry[checked]"
    And the element ".payment-form-wrapper div.fawry-prefix-description" should exist
    And the element "input[name=fawry-email]" should exist
    And the element "input[name=fawry-mobile-number]" should exist
    And I add the billing address on checkout page
    Then the element ".checkout-link.submit a" should exist

  @cnc @language @mobile @fawry
  Scenario: As a Guest user, I should be able to checkout using click and collect with Fawry payment
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "إتمام الشراء بأمان"
    And I wait for element ".edit-checkout-as-guest"
    Then I follow "سجل الإشتراك هنا"
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-checkout_com_upapi_fawry" element on page
    And I wait for AJAX to finish
    And I wait for element "input#payment-method-checkout_com_upapi_fawry[checked]"
    And the element ".payment-form-wrapper div.fawry-prefix-description" should exist
    And the element "input[name=fawry-email]" should exist
    And the element "input[name=fawry-mobile-number]" should exist
    And I add the billing address on checkout page
    Then the element ".checkout-link.submit a" should exist
