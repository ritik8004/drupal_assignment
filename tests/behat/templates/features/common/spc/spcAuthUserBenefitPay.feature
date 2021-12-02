@javascript @account @smoke @auth @bbwbhuat @benefit-pay
Feature: Test the BenefitPay payment feature functionality

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_pdp_page}"
    And I wait for the page to load

  @desktop
  Scenario: As an Authenticated user, I should be able to place an order using BenefitPay payment method
    When I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-method-checkout_com_upapi_benefitpay" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I should save the order details in the file
    Then the element "#b_p iframe" should exist
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{order_detail}"

  @language
  Scenario: As an authenticated user, I should be able to place an order using BenefitPay payment method in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-method-checkout_com_upapi_benefitpay" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I should save the order details in the file
    Then the element "#b_p iframe" should exist
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{language_order_detail}"

  @mobile
  Scenario: As an Authenticated user, I should be able to place an order using BenefitPay payment method on mobile
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-method-checkout_com_upapi_benefitpay" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I should save the order details in the file
    Then the element "#b_p iframe" should exist
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{order_detail}"


