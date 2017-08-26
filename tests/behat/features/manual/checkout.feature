@javascript @checkout @mmcpa-1930 @manual
Feature: Test Checkout feature
  Background:
    Given I am on "/stronglax"
    And I wait for the page to load
    And I press "Add to basket"
    And I wait for AJAX to finish
    And I go to "/cart"
    And I wait for the page to load
    And I press "checkout securely"
    And I wait for the page to load
    And I follow "checkout as guest"
    And I wait for the page to load

  @cod @hd
  Scenario: As a Guest,
  I should be able to checkout using COD
    And I should be able to see the header for checkout
    And I should not see the link "create an account"
    And I should not see the link "Sign in"
    And I should not see the link "Find Store"
    And I should not see "عربية"
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
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "

  @hd @knet
  Scenario: As a Guest,
  I should be able to checkout using KNET
    And I should be able to see the header for checkout
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

  @cc @knet
  Scenario: As a Guest
  I should be able to use click and collect option
  and pay by KNET
    And I should be able to see the header for checkout
    And I follow "Click & Collect"
    And I wait for AJAX to finish
    And I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    And I follow "select this store"
    And I wait for AJAX to finish
    When I fill in "edit-cc-firstname" with "Shweta"
    And I fill in "edit-cc-lastname" with "Sharma"
    When I fill in "edit-cc-email" with "shweta@axelerant.com"
    And I fill in "edit-cc-mobile-number-mobile" with "97004455"
    And I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    When I fill in "edit-billing-address-address-billing-given-name" with "Shweta"
    And I fill in "edit-billing-address-address-billing-family-name" with "Sharma"
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

  @knet
  Scenario: As a Guest
  I should be directed to basket page on cancelling a KNET transaction
  and User should be able to place order again
    And I should be able to see the header for checkout
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
    And I should see the link "Stronglax"
    And I should see "18.000" in the cart area
    And I should see "KWD" in the cart area
    And I should not see "out-of-stock"
    And I press "checkout securely"
    And I wait for the page to load
    And I follow "checkout as guest"
    And I wait for the page to load
    And I should be able to see the header for checkout
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

  @hd @knet
  Scenario: As a Guest
  I should be able to checkout using HD - KNET
  after adding both configurable and non-configurable product to the basket
    When I go to "/grey-navy-and-yellow-jersey-shorts-3-pack"
    And I wait for the page to load
    When I follow "9-12 Months"
    And I wait for AJAX to finish
    And I select "10" quantity
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    When I follow "checkout as guest"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Shweta"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Sharma"
    When I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
    When I select "Abbasiya" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    When I press "deliver to this address"
    And I wait for AJAX to finish
    When I press "proceed to payment"
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

  @cc @knet
  Scenario: As a Guest
  I should be able to checkout using Click and Collect - KNET
  after adding both configurable and non-configurable product to the basket
    When I go to "/grey-navy-and-yellow-jersey-shorts-3-pack"
    And I wait for the page to load
    When I follow "2-3 Years"
    And I wait for AJAX to finish
    And I select "2" quantity
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    When I follow "checkout as guest"
    And I wait for the page to load
    When I follow "Click & Collect"
    And I wait for the page to load
    When I select the first autocomplete option for "shuwaikh " on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "select this store"
    And I wait for AJAX to finish
    When I fill in "edit-cc-firstname" with "Shweta"
    And I fill in "edit-cc-lastname" with "Sharma"
    When I fill in "edit-cc-email" with "shweta@axelerant.com"
    And I fill in "edit-cc-mobile-number-mobile" with "97004455"
    And I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    When I fill in "edit-billing-address-address-billing-given-name" with "Shweta"
    And I fill in "edit-billing-address-address-billing-family-name" with "Sharma"
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


  @cc
  Scenario: As a Guest
  I should be able to view the number of results displayed
  Buttons to toggle between list and Map view
  and link to navigate to the basket
    And I should be able to see the header for checkout
    And I follow "Click & Collect"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then I should see the number of stores displayed
    And I should see the link "List view"
    And I should see the link "Map view"
    And I should see the link "Back to basket"

  @cc
  Scenario: As a Guest
  I should be able to see the two tabs
  on Click and Collect
    When I follow "Click & Collect"
    And I wait for the page to load
    Then I should see the link "List view"
    And I should see the link "Map view"
    But the "List view" tab should be selected

  @cc
  Scenario: As a Guest
  I should be able to see various options
  for each Store on Click & Collect
    When I follow "Click & Collect"
    And I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
    When I wait for AJAX to finish
    And I wait 5 seconds
    Then I should see store name and location for all the listed stores
    And I should see opening hours for all the listed stores
    Then I should see collect in store info for all the listed stores
    And I should see select this store for all the listed stores
    Then I should see view on map button for all the listed stores

  @cc
  Scenario: As a Guest
  I should be navigated to basket page
  On clicking 'back to basket' from checkout CC page
    When I follow "Click & Collect"
    And I wait for the page to load
    When I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "Back to basket"
    Then I should see the button "checkout securely"
    And the url should match "/cart"

  @cc
  Scenario: As a Guest
  I should be able to see the store timings
  on clicking the Opening hours link and
  link should toggle
    When I follow "Click & Collect"
    And I wait for the page to load
    When I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click the label for "div.hours--label"
    And I wait for AJAX to finish
    Then I should see "Monday"
    And I should see "9am to 6pm"
    And I should see "Tuesday"
    When I click the label for ".hours--label.open"
    Then I should not see "Monday"
    And I should not see "9am to 6pm"
    And I should not see "Tuesday"

  @hd @cs
  Scenario: As a Guest
  I should be able to checkout on HD
  using Cybersource payment method
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Shweta"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Sharma"
    When I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
    When I select "Abbasiya" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    When I press "deliver to this address"
    And I wait for AJAX to finish
    When I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_number]" with "4111111111111111"
    And I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_cvv]" with "123"
    When I select "2020" from "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_exp_year]"
    When I accept terms and conditions
    And I press "place order"
    When I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "

  @cc @cs
  Scenario:  As a Guest
  I should be able to checkout on Click and Collect
  using Cybersource payment method
    When I follow "Click & Collect"
    And I wait for the page to load
    When I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "select this store"
    And I wait for AJAX to finish
    When I fill in "edit-cc-firstname" with "Shweta"
    And I fill in "edit-cc-lastname" with "Sharma"
    When I fill in "edit-cc-email" with "shweta@axelerant.com"
    And I fill in "edit-cc-mobile-number-mobile" with "97004455"
    And I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_number]" with "4111111111111111"
    And I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_cvv]" with "123"
    When I select "2020" from "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_exp_year]"
    When I fill in "edit-billing-address-address-billing-given-name" with "Shweta"
    And I fill in "edit-billing-address-address-billing-family-name" with "Sharma"
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "97004455"
    And I select "Abbasiya" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-locality" with "Block A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    And I accept terms and conditions
    And I press "place order"
    When I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "
