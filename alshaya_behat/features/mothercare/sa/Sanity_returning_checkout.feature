@javascript
Feature: To verify a returning customer is able to
  able to checkout with HD and C&C

  Scenario: As a returning customer
  I should be able to place an order for HD - COD, KNET and Cybersource
#    Given I am on a configurable product on mcsa
#    When I select a size for the product
    Given I am on a configurable product
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/cart"
    And I wait for the page to load
#    When I press "checkout securely"
    When I go to "/cart/checkout/login"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "trupti@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "password@1"
    When I press "sign in"
    And I wait for the page to load
    When I follow "Home delivery"
    And I wait for the page to load
    When I select address
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for the page to load
    Then I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    Then I should see "I confirm that I have read and accept the"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I accept terms and conditions
    And I accept terms and conditions
    And I wait for the page to load
    And I press "place order"
    And I wait for the page to load


  Scenario: As a returning customer
  I should be able to place an order for CC - KNET & Cybersource
    Given I am on a configurable product
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/cart"
    And I wait for the page to load
#    When I press "checkout securely"
    When I go to "/cart/checkout/login"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "trupti@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "password@1"
    When I press "sign in"
    And I wait for the page to load
    And I follow "click & collect"
    And I wait for the page to load
    When I select a store for Saudi arabia
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I wait for AJAX to finish
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "571898767"
    And I select "Dahran" from "edit-billing-address-address-billing-area-parent"
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I select "ad danah al janubiyah" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    When I fill in "edit-billing-address-address-billing-address-line2" with "1"
    When I accept terms and conditions





