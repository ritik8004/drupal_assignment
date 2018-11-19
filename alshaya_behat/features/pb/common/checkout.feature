@javascript
Feature: Test Checkout feature
  Background:
    Given I am on a configured product
    And I wait for AJAX to finish
    When I press "add to cart"
    And I wait for AJAX to finish
    And I go to "/cart"
    And I wait for the page to load
    And I remove promo panel
    And I press "checkout securely"
    And I wait for the page to load
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I wait for the page to load

  Scenario: As a Guest,
  I should be able to checkout using COD
    And I enter address for Saudi Arabia
    And I wait for AJAX to finish
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    And I remove promo panel from delivery page
    And I scroll to x "200" y "400" coordinates of page
    And I press "proceed to payment"
    And I wait for the page to load
    And I remove promo panel from delivery page
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    And I scroll to x "200" y "400" coordinates of page
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test"
    And I should see text matching "Your order number is "


  Scenario: As a Guest
    I should be able to checkout on HD
    using Cybersource payment method
    And I remove promo panel from delivery page
    And I wait for AJAX to finish
    And I scroll to x "200" y "300" coordinates of page
    And I enter address for Saudi Arabia
    And I wait for AJAX to finish
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    And I scroll to x "100" y "700" coordinates of page
    When I press "proceed to payment"
    And I wait for the page to load
#    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    And I scroll to x "100" y "400" coordinates of page
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I accept terms and conditions
    And I press "place order"
    When I wait for the page to load
    And I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "

  @tc
  Scenario: As a Guest,
  I should see the error message when terms and condition unchecked
    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "555675765"
    And I select "Abu Dhabi" from "edit-guest-delivery-home-address-shipping-area-parent"
    And I wait for AJAX to finish
    And I select "Abu Dhabi Media Co" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I scroll to x "884" y "674" coordinates of page
    And I press "deliver to this address"
    And I wait for AJAX to finish
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    And I remove promo panel from delivery page
    And I scroll to x "200" y "400" coordinates of page
    And I press "proceed to payment"
    And I wait for the page to load
    And I remove promo panel from delivery page
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I scroll to x "884" y "674" coordinates of page
  # By default terms and condition is unchecked.
    And I press "place order"
    And I wait for the page to load
    Then I should see text matching "Please agree to the Terms and Conditions."