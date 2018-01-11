@javascript @checkout @english @eng_checkout @mmcpa-1930 @manual @prod
Feature: Test various checkout scenarios as returning customer

  Background:
    Given I am on a configurable product
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+3@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "sign in"
    And I wait for the page to load

  @hd @cod
  Scenario: As a returning customer
  I should be able to place an order for HD - COD
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "deliver to this address"
    And I wait for AJAX to finish
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    Then I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    Then I accept terms and conditions
    Then I should see "I confirm that I have read and accept the"

  @hd @knet
  Scenario: As a returning customer
  I should be able to place an order for HD - KNET
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "deliver to this address"
    And I wait for AJAX to finish
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    Then I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    Then I accept terms and conditions
    And I wait for the page to load
    When I press "place order"
    And I wait for the page to load
    Then I select "Knet Test Card [KNET1]" from "bank"
    And I fill in "cardN" with "0000000001"
    When I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    When I fill in "Ecom_Payment_Pin_id" with "1234"
    Then I press "Submit"

  @cc @knet
  Scenario: As a returning customer
  I should be able to place an order for CC - KNET
    And I follow "click & collect"
    And I wait for AJAX to finish
    And I select the first autocomplete option for "Shuwaikh" on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    And I follow "select this store"
    And I wait 10 seconds
    And I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
    And I select "Kuwait City" from "edit-billing-address-address-billing-administrative-area"
    When I fill in "edit-billing-address-address-billing-locality" with "Block A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    When I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    When I accept terms and conditions
    Then I press "place order"
    And I wait for the page to load
    When I select "Knet Test Card [KNET1]" from "bank"
    And I fill in "cardN" with "0000000001"
    When I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    When I fill in "Ecom_Payment_Pin" with "1234"
    Then I press "Submit"

  @hd @cs
  Scenario: As a returning customer
    I should be able to checkout using HD - Cybersource
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "deliver to this address"
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I accept terms and conditions
    Then I should see "I confirm that I have read and accept the"

  @cc @cs
  Scenario: As a returning customer
  I should be able to checkout using CC - Cybersource
    When I follow "click & collect"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Shuwaikh" on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    And I follow "select this store"
    When I wait 10 seconds
    And I select an element having class ".cc-action"
    When I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
    And I select "Kuwait City" from "edit-billing-address-address-billing-administrative-area"
    When I fill in "edit-billing-address-address-billing-locality" with "Block A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    When I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    And I accept terms and conditions
    Then I should see "I confirm that I have read and accept the"
