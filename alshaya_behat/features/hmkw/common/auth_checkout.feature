@javascript @checkout @english @eng_checkout @mmcpa-1930 @manual
Feature: As an authenticated user
  I should be able to checkout
  using various payment options

  Background:
    Given I am logged in as an authenticated user "kanchan.patil+test@qed42.com" with password "Password@1"
    And I wait for the page to load
#    Then I should see the link "My account"
    When I am on a configurable product
    And I wait for the page to load
    When I press "Add to basket"
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
    And I wait for the page to load
    When I select address
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for the page to load
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "

  @hd @knet
  Scenario: As an authenticated user
    I should be able to checkout using Home delivery
    and pay by KNET
      When I follow "Home delivery"
      And I wait for the page to load
      When I select address
      And I wait for the page to load
      When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
      And I wait for the page to load
      And I press "proceed to payment"
      And I wait for the page to load
      When I select a payment option "payment_method_title_knet"
      And I wait for AJAX to finish
      And I accept terms and conditions
      And I wait for the page to load
      And I press "place order"
      And I wait for the page to load
      And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect"
      And I fill in an element having class ".paymentinput" with "0000000001"
      And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
      And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
      And I fill in "Ecom_Payment_Pin_id" with "1234"
      And I press "Submit"
      And I press "Confirm"
      And I wait 5 seconds
      And I wait for the page to load
      Then I should see text matching "Thank you for shopping online with us, Test Test "
      And I should see text matching "Your order number is "

  @hd @cs
    Scenario: As an authenticated user
    I should be able to checkout using Home delivery
    and pay by Cybersource
    When I follow "Home delivery"
    And I wait for the page to load
    When I select address
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for the page to load
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I accept terms and conditions
    And I press "place order"
    And I wait 10 seconds
    When I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "

  @cc @knet
  Scenario: As an authenticated user
  I should be able to use click and collect option
  and pay by KNET
    And I follow "Click & Collect"
    And I wait for the page to load
    When I select a store
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
    And I select "Kuwait City" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-locality" with "Block A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect"
    And I fill in an element having class ".paymentinput" with "0000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin" with "1234"
    And I press "Submit"
    And I press "Confirm"
    And I wait 5 seconds
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "

  @cc @cs
  Scenario: As an authenticated user
    I should be able to checekout on Click and Collect
    using Cybersource payment method
    When I follow "Click & Collect"
    And I wait for the page to load
    When I select a store
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
    And I select "Abbasiya" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-locality" with "Block A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    When I fill in "edit-billing-address-address-billing-address-line2" with "1"
    And I accept terms and conditions
    And I press "place order"
    And I wait 10 seconds
    When I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "