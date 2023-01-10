@javascript @checkoutPayment @QpayPayment @guest @homeDelivery @flqauat
Feature: SPC Checkout Home Delivery QPAy payment for guest users

  Background:
    Given I am on "{spc_basket_page}"
    And I wait for element "#block-page-title"
    And I wait for the page to load

  @cc @hd @Qpay
  Scenario: As a Guest, I should be able to checkout using Qpay
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
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-methods div.payment-method-checkout_com_upapi_qpay" element on page
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I fill in "cardNumber" with "{spc_Qpay_card}"
    And I select "{spc_Qpay_month}" from "expiryDatemm"
    And I select "{spc_Qpay_year}" from "expiryDateyy"
    Then I press "btnSubmit"
    And I wait 5 seconds
    And I fill in Qpay pin code
    And I click jQuery "#pay" element on page
    And I wait 10 seconds
    Then I should be on "/checkout/confirmation" page

  @cc @hd @language @desktop @Qpay
  Scenario: As a Guest, I should be able to checkout using Qpay in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 10 seconds
    And I wait for the page to load
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I select the home delivery address
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-methods div.payment-method-checkout_com_upapi_qpay" element on page
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I fill in "cardNumber" with "{spc_Qpay_card}"
    And I select "{spc_Qpay_month}" from "expiryDatemm"
    And I select "{spc_Qpay_year}" from "expiryDateyy"
    Then I press "btnSubmit"
    And I wait 5 seconds
    And I fill in Qpay pin code
    And I click jQuery "#pay" element on page
    And I wait 10 seconds
    Then I should be on "/{language_short}/checkout/confirmation" page

  @cc @hd @language @mobile @Qpay
  Scenario: As a Guest, I should be able to checkout using Qpay in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
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
    And I wait 10 seconds
    And I wait for the page to load
    And I select the home delivery address
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-methods div.payment-method-checkout_com_upapi_qpay" element on page
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I fill in "cardNumber" with "{spc_Qpay_card}"
    And I select "{spc_Qpay_month}" from "expiryDatemm"
    And I select "{spc_Qpay_year}" from "expiryDateyy"
    Then I press "btnSubmit"
    And I wait 2 seconds
    And I fill in Qpay pin code
    And I click jQuery "#pay" element on page
    And I wait 5 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page