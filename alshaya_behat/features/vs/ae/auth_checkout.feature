@javascript
Feature: As a customer
  I should be able to checkout
  using various payment options

  Background:
    Given I am logged in as an authenticated user "trupti@axelerant.com" with password "password@1"
    And I wait for the page to load
    Then I should see the link "My account"
    When I am on a sport product
    And I wait for the page to load
    And I remove promo panel
    When I press "add to cart"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load

  @hd @cod
  Scenario: As an authenticated user
  I should be able to checkout using Home delivery
  and pay by Cash-on-delivery
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I select address
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    And I scroll to the "#edit-actions-next" element
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "

  @hd @cs
  Scenario: As an authenticated user
    I should be able to checkout on HD
    using Cybersource payment method
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I select address
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
    And I wait 10 seconds
    When I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "

  @cc @cs
  Scenario: As an authenticated user
    I should be able to checekout on Click and Collect
    using Cybersource payment method
    When I follow "Click & Collect"
    And I wait for the page to load
    When I select a store for UAE
    And I wait for the page to load
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "555667733"
    And I select "Dubai" from "edit-billing-address-address-billing-area-parent"
    And I wait for AJAX to finish
    And I select "Abu Hail" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    And I accept terms and conditions
    And I press "place order"
    And I wait 10 seconds
    When I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "
