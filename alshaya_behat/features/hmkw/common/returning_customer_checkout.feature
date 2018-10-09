@javascript @checkout @english @eng_checkout @mmcpa-1930 @manual
Feature: Test various checkout scenarios as returning customer

  Background:
    Given I am on a configurable product
    And I wait for the page to load
    When I press "add to basket"
    And I wait for AJAX to finish
    Then I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "kanchan.patil+test@qed42.com"
    And I fill in "edit-checkout-login-pass" with "Password@1"
    When I press "sign in"
    And I wait for the page to load

  @hd @cod
  Scenario: As a returning customer
  I should be able to place an order for HD - COD
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
    Then I accept terms and conditions
    When I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test"
    And I should see text matching "Your order number is "
        #checking product attributes
    When I click on ".product--count td" element
    And I wait for AJAX to finish
    Then I should see "size:"
    And I should see "Item code:"
    And I should see "Quantity:"
    And I should see "color:"


  @hd @knet
  Scenario: As a returning customer
  I should be able to place an order for HD - KNET
    When I follow "Home delivery"
    And I wait for the page to load
    When I select address
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for the page to load
    Then I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    Then I accept terms and conditions
    And I wait for the page to load
    When I press "place order"
    And I wait for the page to load
    And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect"
    And I fill in an element having class ".paymentinput" with "0000000001"
    When I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    When I fill in "Ecom_Payment_Pin_id" with "1234"
    Then I press "Submit"
    When I press "Confirm"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test"
    And I should see text matching "Your order number is "
        #checking product attributes
    When I click on ".product--count td" element
    And I wait for AJAX to finish
    Then I should see "size:"
    And I should see "Item code:"
    And I should see "Quantity:"
    And I should see "color:"


  @cc @knet
  Scenario: As a returning customer
  I should be able to place an order for CC - KNET
    And I follow "Click & Collect"
    And I wait for the page to load
    When I select a store
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
    And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect"
    And I fill in an element having class ".paymentinput" with "0000000001"
    When I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    When I fill in "Ecom_Payment_Pin" with "1234"
    Then I press "Submit"
    And I press "Confirm"
    When I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test"
    And I should see text matching "Your order number is "
        #checking product attributes
    When I click on ".product--count td" element
    And I wait for AJAX to finish
    Then I should see "size:"
    And I should see "Item code:"
    And I should see "Quantity:"
    And I should see "color:"


  @hd @cs
  Scenario: As a returning customer
    I should be able to checkout using HD - Cybersource
    When I follow "Home delivery"
    And I wait for the page to load
    When I select address
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
    And I press "place order"
    And I wait 10 seconds
    When I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test User"
    And I should see text matching "Your order number is "
        #checking product attributes
    When I click on ".product--count td" element
    And I wait for AJAX to finish
    Then I should see "size:"
    And I should see "Item code:"
    And I should see "Quantity:"
    And I should see "color:"


  @cc @cs
  Scenario: As a returning customer
  I should be able to checkout using CC - Cybersource
    When I follow "Click & Collect"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I select a store
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
    When I press "place order"
    And I wait 10 seconds
    When I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test User"
    And I should see text matching "Your order number is "
        #checking product attributes
    When I click on ".product--count td" element
    And I wait for AJAX to finish
    Then I should see "size:"
    And I should see "Item code:"
    And I should see "Quantity:"
    And I should see "Color:"

