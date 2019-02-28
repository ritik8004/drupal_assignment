@javascript
Feature: To verify a returning customer is able to
  able to checkout with HD and C&C

  Scenario: As a returning customer
  I should be able to place an order for HD - COD, KNET and Cybersource
    Given I am on a configurable product
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/en/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "karnika.jain+test@qed42.com"
    And I fill in "edit-checkout-login-pass" with "Password@1"
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
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    Then I should see "I confirm that I have read and accept the"
    And I accept terms and conditions
    And I wait for the page to load
    And I press "place order"
    And I wait for the page to load
    And I press "CancelAction_id"



  Scenario: As a returning customer
  I should be able to place an order for CC - KNET & Cybersource
    Given I am on a configurable product
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/en/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "karnika.jain+test@qed42.com"
    And I fill in "edit-checkout-login-pass" with "Password@1"
    When I press "sign in"
    And I wait for the page to load
    And I follow "click & collect"
    And I wait for the page to load
    When I select a store
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    Then I should see "I confirm that I have read and accept the"
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
    And I select "Kuwait City" from "edit-billing-address-address-billing-administrative-area"
    When I fill in "edit-billing-address-address-billing-locality" with "Block A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    When I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    When I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    And I press "CancelAction_id"




