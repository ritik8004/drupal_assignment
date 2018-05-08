@javascript
Feature: As an authenticated user
  I should be able to checkout
  using various payment options

  Background:
    Given I am logged in as an authenticated user "trupti@axelerant.com" with password "password@1"
    And I wait for the page to load
    Then I should see the link "My account"
    When I am on a configurable product
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/en/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load

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
    Then I should see text matching "Thank you for shopping online with us"
    And I should see text matching "Your order number is "

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
    When I wait 10 seconds
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
    And I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us "
    And I should see text matching "Your order number is "

  Scenario: As an authenticated user
    I should be able to checekout on Click and Collect
    using Cybersource payment method
    When I wait 10 seconds
    When I follow "click & collect"
    And I wait for the page to load
    When I select a store
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "571678876"
    And I select "Dahran" from "edit-billing-address-address-billing-area-parent"
    And I wait for AJAX to finish
    And I select "ad danah ash shamaliyah" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    When I fill in "edit-billing-address-address-billing-address-line2" with "1"
    And I accept terms and conditions
    And I press "place order"
    When I wait for the page to load
    And I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us"
    And I should see text matching "Your order number is "