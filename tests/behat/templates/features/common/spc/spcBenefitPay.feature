@javascript @account @checkoutPayment @smoke @auth @bbwbhuat @benefit-pay
Feature: Test the BenefitPay payment feature functionality

  Background:
    When I am on "{spc_pdp_page}"
    And I wait for element "#block-page-title"

  Scenario: As a Guest user, I should be able to place an order using BenefitPay payment method
    When I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    When fill in billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {road_number} |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-method-checkout_com_upapi_benefitpay" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I should save the order details in the file
    Then the element "#b_p iframe" should exist
    Then I should see "{order_confirm_text}"
    Then I should see "{order_detail}"

  @language
  Scenario: As a Guest user, I should be able to place an order using BenefitPay payment method in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {road_number} |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-method-checkout_com_upapi_benefitpay" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I should save the order details in the file
    Then the element "#b_p iframe" should exist
    Then I should see "{language_order_confirm_text}"
    Then I should see "{language_order_detail}"

  @mobile
  Scenario: As a Guest user, I should be able to place an order using BenefitPay payment method on mobile
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    When fill in billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {road_number} |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#spc-checkout .spc-main .spc-content #spc-payment-methods .payment-method-checkout_com_upapi_benefitpay" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I should save the order details in the file
    Then the element "#b_p iframe" should exist
    Then I should see "{order_confirm_text}"
    Then I should see "{order_detail}"
