@javascript
Feature: Test various checkout scenarios as returning customer

  Background:
    Given I am on a configurable product
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/en/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "trupti@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "password@1"
    When I press "sign in"
    And I wait for the page to load

  Scenario: As a returning customer
  I should be able to place an order for HD - COD
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "deliver to this address"
    And I wait for AJAX to finish
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    And I scroll to the "#edit-actions-next" element
    Then I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    Then I accept terms and conditions
    When I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, "
    And I should see text matching "Your order number is "

  Scenario: As a returning customer
    I should be able to checkout using HD - Cybersource
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "deliver to this address"
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    And I scroll to the "#edit-actions-next" element
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I accept terms and conditions
    And I press "place order"
    When I wait for the page to load
    When I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us, "
    And I should see text matching "Your order number is "

  Scenario: As a returning customer
  I should be able to checkout using CC - Cybersource
    When I follow "click & collect"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Shuwaikh" on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 15 seconds
    And I follow "select this store"
    When I wait 10 seconds
    And I select an element having class ".cc-action"
    When I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "571012345"
    And I select "Dahran" from "edit-billing-address-address-billing-area-parent"
    And I wait for AJAX to finish
    And I select "ad danah ash shamaliyah" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Builing C"
    And I accept terms and conditions
    When I press "place order"
    When I wait for the page to load
    When I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us, "
    And I should see text matching "Your order number is "