@javascript @checkout @1930 @manual
Feature: Test Checkout feature
  Background:
    Given I am on "/Ton-Fax"
    And I wait for the page to load
    And I press "Add to basket"
    And I wait for AJAX to finish
    And I follow "view basket"
    And I wait for the page to load
    And I press "checkout securely"
    And I wait for the page to load
    And I follow "checkout as guest"
    And I wait for the page to load

  Scenario: As a Guest,
  I should be able to checkout using COD
    When I see the header for checkout
    Then I should not see the link "create an account"
    And I should not see the link "Sign in"
    And I should not see the link "Find Store"
    And I should not see "عربية"
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Shweta"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Sharma"
    And I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
    And I select "Abbasiya" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I press "deliver to this address"
    And I wait for AJAX to finish
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "

  Scenario: As a Guest,
    I should be able to checkout using KNET
    When I see the header for checkout
    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Shweta"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Sharma"
    And I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
    And I select "Abbasiya" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I press "deliver to this address"
    And I wait for AJAX to finish
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I wait for the page to load
    And I press "place order"
    And I wait for the page to load
    And I select "Knet Test Card [KNET1]" from "bank"
    And I fill in "cardN" with "0000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin_id" with "1234"
    And I press "Submit"
    And I press "Confirm"
    And I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma"

  Scenario: As a Guest
  I should be able to use click and collect option
  and pay by KNET
    When I see the header for checkout
    And I follow "Click & Collect"
    And I wait for the page to load
    And I select the first autocomplete option for "Shuwaikh" on the "edit-store-location" field
    And I wait for AJAX to finish
    And I follow "select this store"
    And I wait for AJAX to finish
    And I fill in "edit-cc-mobile-number-mobile" with "97004455"
    And I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "97004455"
    And I select "Abbasiya" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-locality" with "Block A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    And I select "Knet Test Card [KNET1]" from "bank"
    And I fill in "cardN" with "0000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin" with "1234"
    And I press "Submit"
    And I press "Confirm"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "

  Scenario: As a Guest
    I should be directed to basket page on cancelling a KNET transaction
    and User should be able to place order again
    When I see the header for checkout
    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Shweta"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Sharma"
    And I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
    And I select "Abbasiya" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I press "deliver to this address"
    And I wait for AJAX to finish
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I wait for the page to load
    And I press "place order"
    And I wait for the page to load
    And I press "Cancel"
    And I wait for the page to load
    Then the url should match "/cart"
    And I should see the link "Ton-Fax"
    And I should see "18.000" in the cart area
    And I should see "KWD" in the cart area
    And I should not see "out-of-stock"
    And I press "checkout securely"
    And I wait for the page to load
    And I follow "checkout as guest"
    And I wait for the page to load
    And I see the header for checkout
    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Shweta"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Sharma"
    And I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
    And I select "Abbasiya" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I press "deliver to this address"
    And I wait for AJAX to finish
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I wait for the page to load
    And I press "place order"
    And I wait for the page to load
    And I select "Knet Test Card [KNET1]" from "bank"
    And I fill in "cardN" with "0000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin" with "1234"
    And I press "Submit"
    And I press "Confirm"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "

  Scenario: As a Guest
    I should be able to view the number of results displayed
    Buttons to toggle between list and Map view
    and link to navigate to the basket
    When I see the header for checkout
    And I follow "Click & Collect"
    And I wait for the page to load
    When I select the first autocomplete option for "Shuwaikh" on the "edit-store-location" field
    And I wait for AJAX to finish
    Then I should see the number of stores displayed
    And I should see the link "List view"
    And I should see the link "Map view"
    And I should see the link "Back to basket"
