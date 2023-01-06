@javascript @Qpay @checkoutPayment @QpayPayment @clickCollect @flqauat
Feature: SPC Checkout Click and Collect using QPay Payment method

  Background:
    Given I am on "{spc_basket_page}"
    And I wait for element "#block-page-title"

  @cc @cnc @desktop @Qpay
  Scenario: As a Guest, I should be able to checkout using click and collect with qpay
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for the page to load
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-methods div.payment-method-checkout_com_upapi_qpay" element on page
    And I wait for element "input#payment-method-checkout_com_upapi_qpay[checked]"
    And I wait for AJAX to finish
    And I add the billing address on checkout page
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for AJAX to finish
    Then I fill in "cardNumber" with "{spc_Qpay_card}"
    And I select "{spc_Qpay_month}" from "expiryDatemm"
    And I select "{spc_Qpay_year}" from "expiryDateyy"
    Then I press "btnSubmit"
    And I wait for element "#pay"
    And I fill in Qpay pin code
    And I click jQuery "#pay" element on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file

  @cc @cnc @language @desktop @Qpay
  Scenario: As a Guest, I should be able to checkout using click and collect with qpay
    When I follow "{language_link}"
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for the page to load
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-methods div.payment-method-checkout_com_upapi_qpay" element on page
    And I wait for element "input#payment-method-checkout_com_upapi_qpay[checked]"
    And I wait for AJAX to finish
    And I add the billing address on checkout page
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for AJAX to finish
    Then I fill in "cardNumber" with "{spc_Qpay_card}"
    And I select "{spc_Qpay_month}" from "expiryDatemm"
    And I select "{spc_Qpay_year}" from "expiryDateyy"
    Then I press "btnSubmit"
    And I wait for element "#pay"
    And I fill in Qpay pin code
    And I click jQuery "#pay" element on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file

  @cc @cnc @mobile @Qpay
  Scenario: As a Guest, I should be able to checkout using click and collect with qpay
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element ".checkout-login-wrapper"
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait for the page to load
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-methods div.payment-method-checkout_com_upapi_qpay" element on page
    And I wait for element "input#payment-method-checkout_com_upapi_qpay[checked]"
    And I wait for AJAX to finish
    And I add the billing address on checkout page
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for AJAX to finish
    Then I fill in "cardNumber" with "{spc_Qpay_card}"
    And I select "{spc_Qpay_month}" from "expiryDatemm"
    And I select "{spc_Qpay_year}" from "expiryDateyy"
    Then I press "btnSubmit"
    And I wait for element "#pay"
    And I fill in Qpay pin code
    And I click jQuery "#pay" element on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
