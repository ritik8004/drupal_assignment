@javascript @QpayPayment @homeDelivery @auth @flqauat
Feature: SPC Checkout Home Delivery QPay Payment for Authenticated User

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load

  @cc @hd @Qpay
  Scenario: As an authenticated user, I should be able to checkout using Qpay
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I select the home delivery address
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-methods div.payment-method-checkout_com_upapi_qpay" element on page
    And I wait 10 seconds
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I fill in "cardNumber" with "{spc_Qpay_card}"
    And I select "{spc_Qpay_month}" from "expiryDatemm"
    And I select "{spc_Qpay_year}" from "expiryDateyy"
    Then I press "btnSubmit"
    And I wait 10 seconds
    And I fill in Qpay pin code
    And I click jQuery "#pay" element on page
    And I wait 10 seconds
    Then I should be on "/checkout/confirmation" page

  @cc @hd @language @desktop @Qpay
  Scenario: As an authenticated user, I should be able to checkout using Qpay in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I select the home delivery address
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-methods div.payment-method-checkout_com_upapi_qpay" element on page
    And I wait 10 seconds
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I fill in "cardNumber" with "{spc_Qpay_card}"
    And I select "{spc_Qpay_month}" from "expiryDatemm"
    And I select "{spc_Qpay_year}" from "expiryDateyy"
    Then I press "btnSubmit"
    And I wait 10 seconds
    And I fill in Qpay pin code
    And I click jQuery "#pay" element on page
    And I wait 10 seconds
    Then I should be on "{language_short}/checkout/confirmation" page

  @cc @hd @language @mobile @Qpay
  Scenario: As an authenticated user, I should be able to checkout using Qpay in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I select the home delivery address
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-methods div.payment-method-checkout_com_upapi_qpay" element on page
    And I wait 10 seconds
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I fill in "cardNumber" with "{spc_Qpay_card}"
    And I select "{spc_Qpay_month}" from "expiryDatemm"
    And I select "{spc_Qpay_year}" from "expiryDateyy"
    Then I press "btnSubmit"
    And I wait 10 seconds
    And I fill in Qpay pin code
    And I click jQuery "#pay" element on page
    And I wait 10 seconds
    Then I should be on "{language_short}/checkout/confirmation" page
