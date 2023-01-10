@javascript @auth @bank-transfer
Feature: SPC Checkout Home Delivery using Bank Transfer method for Authenticated user

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_bank_product}"
    And I wait for the page to load

  @hd @desktop
  Scenario: As an Authenticated user, I should be able to checkout using Bank Transfer
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 15 seconds
    Then I should see "2"
    When I follow "continue to checkout"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-banktransfer" element on page
    And I wait 10 seconds
    Then the "payment-method-banktransfer" checkbox should be checked
    Then the element ".checkout-link.submit" should exist

  @hd @language @desktop
  Scenario: As an Authenticated user, I should be able to checkout using Bank Transfer in second language
    When I follow "{language_link}"
    And I wait 5 seconds
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-3" element
    And I wait 15 seconds
    Then I should see "3"
    When I follow "continue to checkout"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait 10 seconds
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-banktransfer" element on page
    And I wait 10 seconds
    Then the "payment-method-banktransfer" checkbox should be checked
    And I scroll to the "#spc-payment-methods" element
    Then the element ".checkout-link.submit" should exist

  @hd @language @mobile
  Scenario: As a Guest, I should be able to checkout using Bank Transfer in second language
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
    Then I click on ".spc-product-tile-actions .spc-select .spcSelect__control" element
    And I click on ".spcSelect__menu .spcSelect__menu-list #react-select-2-option-2" element
    And I wait 15 seconds
    Then I should see "2"
    When I follow "continue to checkout"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method.home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-banktransfer" element on page
    And I wait 10 seconds
    Then the "payment-method-banktransfer" checkbox should be checked
    Then the element ".checkout-link.submit" should exist
