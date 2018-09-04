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