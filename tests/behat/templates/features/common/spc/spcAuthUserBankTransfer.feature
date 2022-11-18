@javascript @auth @bank-transfer @pbaeuat @hmaeuat @mcsauat
Feature: SPC Checkout Home Delivery using Bank Transfer method for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"
    When I am on "{spc_bank_product}"
    And I wait for element "#block-content"

  @hd @desktop
  Scenario: As an Authenticated user, I should be able to checkout using Bank Transfer
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 5 seconds
    Then I should see "2"
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait for element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-banktransfer" element on page
    And I wait for AJAX to finish
    Then the "payment-method-banktransfer" checkbox should be checked
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for element "#block-page-title"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_auth_user_email}"
    Then I should see "{order_detail}"

  @hd @language @desktop
  Scenario: As an Authenticated user, I should be able to checkout using Bank Transfer in second language
    When I follow "{language_link}"
    And I wait for element "#block-page-title"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-3" element
    And I wait 5 seconds
    Then I should see "3"
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait for element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-banktransfer" element on page
    And I wait for AJAX to finish
    Then the "payment-method-banktransfer" checkbox should be checked
    And I scroll to the "#spc-payment-methods" element
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for element "#block-page-title"
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{language_order_detail}"

  @hd @language @mobile
  Scenario: As a Guest, I should be able to checkout using Bank Transfer in second language
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait 3 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for element "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link"
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 15 seconds
    Then I should see "2"
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait for element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-banktransfer" element on page
    And I wait for AJAX to finish
    Then the "payment-method-banktransfer" checkbox should be checked
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for element "#block-page-title"
    And I should save the order details in the file
    Then I should see "{language_order_confirm_text}"
    Then I should see "{language_order_detail}"

