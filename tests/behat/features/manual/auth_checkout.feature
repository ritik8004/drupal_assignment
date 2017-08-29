@javascript @checkout @mmcpa-1930 @manual
Feature: As an authenticated user
  I should be able to checkout
  using various payment options

  Background:
    Given I am logged in as an authenticated user "shweta+2@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    And I am on "/stronglax"
    And I wait for the page to load
    And I press "Add to basket"
    And I wait for AJAX to finish
    And I go to "/cart"
    And I wait for the page to load
    And I press "checkout securely"
    And I wait for the page to load

  @hd @cod
  Scenario: As an authenticated user
  I should be able to checkout using Home delivery
  and pay by Cash-on-delivery
  When I press "proceed to payment"
  And I wait for the page to load
  When I select a payment option "payment_method_title_cashondelivery"
  And I wait for AJAX to finish
  And I accept terms and conditions
  When I press "place order"
  And I wait for the page to load
  Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "

  @hd @knet
  Scenario: As an authenticated user
    I should be able to checkout using Home delivery
    and pay by KNET
    When I follow "deliver to this address"
    And I wait for AJAX to finish
    When I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I wait for the page to load
    When I press "place order"
    And I wait for the page to load
    And I select "ABK" from "bank"
    And I fill in "cardN" with "8888880000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin_id" with "1234"
    And I press "Submit"
    And I press "Confirm"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "

  @cc @knet
  Scenario: As an authenticated user
  I should be able to use click and collect option
  and pay by KNET
    When I follow "Click & Collect"
    And I wait for the page to load
    When I select the first autocomplete option for "Shuwaikh" on the "edit-store-location" field
    And I wait for AJAX to finish
    And I follow "select this store"
    And I wait 10 seconds
    And I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "97004455"
    And I select "Abbasiya" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-locality" with "Block A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    When I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    And I select "ABK" from "bank"
    And I fill in "cardN" with "8888880000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin" with "1234"
    And I press "Submit"
    And I press "Confirm"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "

  @hd @cs
  Scenario: As an authenticated user
  I should be able to checkout on HD
  using Cybersource payment method
    When I follow "Home delivery"
    And I wait for AJAX to finish
    When I follow "deliver to this address"
    And I wait for AJAX to finish
    When I follow "Home delivery"
    And I wait for AJAX to finish
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_number]" with "4111111111111111"
    And I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_cvv]" with "123"
    When I select "2020" from "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_exp_year]"
    And I accept terms and conditions
    And I press "place order"
    When I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "
    Then I should see text matching "Your Privileges Card Number is: 6362544000135844"

  @cc @cs
  Scenario: As an authenticated user
  I should be able to checekout on Click and Collect
  using Cybersource payment method
    When I follow "Click & Collect"
    And I wait for the page to load
    When I select the first autocomplete option for "Shuwaikh" on the "edit-store-location" field
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I follow "select this store"
    And I wait 10 seconds
    And I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_number]" with "4111111111111111"
    And I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_cvv]" with "123"
    When I select "2020" from "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_exp_year]"
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "97004455"
    And I select "Abbasiya" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-locality" with "Block A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    When I fill in "edit-billing-address-address-billing-address-line2" with "1"
    And I accept terms and conditions
    And I press "place order"
    When I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "
    Then I should see text matching "Your Privileges Card Number is: 6362544000135844"
