@javascript @account @smoke @auth @bbwbhuat @benefit-pay
Feature: Test the BenefitPay payment feature functionality

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    When I am on "{spc_pdp_page}"
    And I wait for element "#block-content"

  @desktop
  Scenario: As an Authenticated user, I should be able to place an order using BenefitPay payment method
    When I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-method-checkout_com_upapi_benefitpay" element on page
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for element "#block-page-title"
    And I should save the order details in the file
    Then the element "#b_p iframe" should exist
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{order_detail}"

  @language
  Scenario: As an authenticated user, I should be able to place an order using BenefitPay payment method in second language
    When I follow "{language_link}"
    And I wait for element "#block-content"
    When I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-method-checkout_com_upapi_benefitpay" element on page
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for element "#block-page-title"
    And I should save the order details in the file
    Then the element "#b_p iframe" should exist
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{language_order_detail}"

  @mobile
  Scenario: As an Authenticated user, I should be able to place an order using BenefitPay payment method on mobile
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for element ".checkout-link.submit"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-method-checkout_com_upapi_benefitpay" element on page
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for element "#block-page-title"
    And I should save the order details in the file
    Then the element "#b_p iframe" should exist
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{order_detail}"
