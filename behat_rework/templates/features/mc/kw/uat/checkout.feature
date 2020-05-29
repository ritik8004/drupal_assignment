@javascript @checkout
Feature: Test Checkout feature
#  Background:
#    Given I am on "{url_product_page}"
#    And I wait for the page to load
#    When I press "add to basket"
#    And I wait for AJAX to finish
#    And I go to "/cart"
#    And I wait 5 seconds
#    And I press "checkout securely"
#    And I wait for the page to load
#    When I click the element with id "edit-checkout-guest-checkout-as-guest" on page
#    And I wait for the page to load
#    And I wait 5 seconds
#
#  @hd @2d @credit
#  Scenario: As a Guest, I should be able to checkout using 2D Credit Card
#    Then I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Bharat"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
#    And I select "Kuwait International Airport" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
#    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
#    And I press "deliver to this address"
#    And I wait for AJAX to finish
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I press "proceed to payment"
#    And I wait 5 seconds
#    When I select a payment option "payment_method_title_checkout_com"
#    And I wait for AJAX to finish
#    And I fill in "cardName" with "Bharat Test"
#    And I fill in "cardNumber" with "4242 4242 4242 4242"
#    And I fill in "cardCvv" with "100"
#    And I accept terms and conditions
#    And I wait 5 seconds
#    And I press "place order"
#    And I wait 10 seconds
#    Then I should see text matching "Thank you for shopping online with us, Bharat Test"
#    And I should see text matching "Your order number is "
#    #checking product attributes on order confirmation
#    When I click on ".product--count td" element
#    And I wait for AJAX to finish
#    Then I should see "size:"
#    And I should see "Item code:"
#
#  @hd @2d @debit
#  Scenario: As a Guest, I should be able to checkout using 2D Debit Card
#    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Bharat"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
#    And I select "Kuwait International Airport" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
#    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
#    And I press "deliver to this address"
#    And I wait for AJAX to finish
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I press "proceed to payment"
#    And I wait 5 seconds
#    When I select a payment option "payment_method_title_checkout_com"
#    And I wait for AJAX to finish
#    And I fill in "cardName" with "Bharat Test"
#    And I fill in "cardNumber" with "4658 5840 9000 0001"
#    And I fill in "cardCvv" with "257"
#    And I accept terms and conditions
#    And I wait 5 seconds
#    And I press "place order"
#    And I wait 10 seconds
#    Then I should see text matching "Thank you for shopping online with us, Bharat Test"
#    And I should see text matching "Your order number is "
#    #checking product attributes on order confirmation
#    When I click on ".product--count td" element
#    And I wait for AJAX to finish
#    Then I should see "size:"
#    And I should see "Item code:"
#
#  @hd @2d @wrongcvv
#  Scenario: As a Guest, I should not be able to checkout using 2D Credit Card when wrong CVV is used
#    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Bharat"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
#    And I select "Kuwait International Airport" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
#    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
#    And I press "deliver to this address"
#    And I wait for AJAX to finish
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I press "proceed to payment"
#    And I wait 5 seconds
#    When I select a payment option "payment_method_title_checkout_com"
#    And I wait for AJAX to finish
#    And I fill in "cardName" with "Bharat Test"
#    And I fill in "cardNumber" with "4242 4242 4242 4242"
#    And I fill in "cardCvv" with "700"
#    And I accept terms and conditions
#    And I wait 5 seconds
#    And I press "place order"
#    And I wait 10 seconds
#    Then I should see text matching "Transaction has been declined. Please try again later."
#
#  @cod @hd
#  Scenario: As a Guest, I should be able to checkout using COD
#    And I should not see the link "create an account"
#    And I should not see the link "Sign in"
#    And I should not see the link "Find Store"
#    And I should not see "عربية"
#    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
#    And I select "Kuwait International Airport" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
#    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
#    And I press "deliver to this address"
#    And I wait for AJAX to finish
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I press "proceed to payment"
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cashondelivery"
#    And I wait for AJAX to finish
#    And I accept terms and conditions
#    And I press "place order"
#    And I wait for the page to load
#    Then I should see text matching "Thank you for shopping online with us, Test Test"
#    And I should see text matching "Your order number is "
#    #checking product attributes on order confirmation
#    And I wait 10 seconds
#    When I click on ".product--count td" element
#    And I wait for AJAX to finish
#    Then I should see "size:"
#    And I should see "Item code:"
#    And I should see "Quantity:"
#
#  @hd @knet
#  Scenario: As a Guest, I should be able to checkout using KNET
#    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
#    And I select "Kuwait International Airport" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
#    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
#    And I press "deliver to this address"
#    And I wait for AJAX to finish
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I press "proceed to payment"
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_knet"
#    And I wait for AJAX to finish
#    And I accept terms and conditions
#    And I wait for the page to load
#    And I press "place order"
#    And I wait for the page to load
#    And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect.bank"
#    And I wait 5 seconds
#    And I fill in "debitNumber" with "0000000001"
#    And I select "09" from "debitMonthSelect"
#    And I select "2021" from "debitYearSelect"
#    And I fill in "cardPin" with "1234"
#    And I press "Submit"
#    And I wait 10 seconds
#    And I press "Confirm"
#    And I wait 10 seconds
#    And I wait for the page to load
#    Then I should see text matching "Thank you for shopping online with us, Test Test"
#    And I should see text matching "Your order number is "
#    #checking product attributes on order confirmation
#    When I click on ".product--count td" element
#    And I wait for AJAX to finish
#    Then I should see "size:"
#    And I should see "Item code:"
#    And I should see "Quantity:"
#
#  @cc @knet
#  Scenario: As a Guest, I should be able to use click and collect option and pay by KNET
#    When I follow "click & collect"
#    And I wait for AJAX to finish
#    And I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
#    And I wait for AJAX to finish
#    When I wait 5 seconds
#    And I follow "select this store"
#    And I wait for AJAX to finish
#    When I fill in "edit-cc-firstname" with "Test"
#    And I fill in "edit-cc-lastname" with "Test"
#    When I enter a valid Email ID in field "edit-cc-email"
#    And I fill in "edit-cc-mobile-number-mobile" with "55004455"
#    And I select an element having class ".cc-action"
#    And I wait for AJAX to finish
#    When I select a payment option "payment_method_title_knet"
#    And I wait for AJAX to finish
#    When I fill in "edit-billing-address-address-billing-given-name" with "Test"
#    And I fill in "edit-billing-address-address-billing-family-name" with "Test"
#    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
#    And I select "Kuwait International Airport" from "edit-billing-address-address-billing-administrative-area"
#    And I fill in "edit-billing-address-address-billing-locality" with "Block A"
#    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
#    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
#    And I accept terms and conditions
#    And I press "place order"
#    And I wait for the page to load
#    And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect.bank"
#    And I wait 5 seconds
#    And I fill in "debitNumber" with "0000000001"
#    And I select "09" from "debitMonthSelect"
#    And I select "2021" from "debitYearSelect"
#    And I fill in "cardPin" with "1234"
#    And I press "Submit"
#    And I wait 10 seconds
#    And I press "Confirm"
#    And I wait 10 seconds
#    And I wait for the page to load
#    Then I should see text matching "Thank you for shopping online with us, Test Test"
#    And I should see text matching "Your order number is "
#    #checking product attributes on order confirmation
#    When I click on ".product--count td" element
#    And I wait for AJAX to finish
#    Then I should see "size:"
#    And I should see "Item code:"
#    And I should see "Quantity:"
#
#  @hd @cs
#  Scenario: As a Guest, I should be able to checkout on HD using Cybersource payment method
#    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
#    When I select "Kuwait International Airport" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
#    When I press "deliver to this address"
#    And I wait for AJAX to finish
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    When I press "proceed to payment"
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cybersource"
#    And I wait for AJAX to finish
#    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
#    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
#    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
#    When I accept terms and conditions
#    And I press "place order"
#    When I wait for the page to load
#    And I wait 10 seconds
#    Then I should see text matching "Thank you for shopping online with us, Test Test "
#    And I should see text matching "Your order number is "
#    #checking product attributes on order confirmation
#    When I click on ".product--count td" element
#    And I wait for AJAX to finish
#    Then I should see "size:"
#    And I should see "Item code:"
#    And I should see "Quantity:"
#
#  @cc @cs
#  Scenario:  As a Guest, I should be able to checkout on Click and Collect using Cybersource payment method
#    When I follow "click & collect"
#    And I wait for the page to load
#    When I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
#    And I wait for AJAX to finish
#    When I wait 5 seconds
#    When I follow "select this store"
#    And I wait for AJAX to finish
#    When I fill in "edit-cc-firstname" with "Test"
#    And I fill in "edit-cc-lastname" with "Test"
#    When I enter a valid Email ID in field "edit-cc-email"
#    And I fill in "edit-cc-mobile-number-mobile" with "55004455"
#    And I select an element having class ".cc-action"
#    And I wait for AJAX to finish
#    When I select a payment option "payment_method_title_cybersource"
#    And I wait for AJAX to finish
#    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
#    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
#    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
#    When I fill in "edit-billing-address-address-billing-given-name" with "Test"
#    And I fill in "edit-billing-address-address-billing-family-name" with "Test"
#    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
#    And I select "Kuwait International Airport" from "edit-billing-address-address-billing-administrative-area"
#    And I fill in "edit-billing-address-address-billing-locality" with "Block A"
#    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
#    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
#    And I accept terms and conditions
#    And I press "place order"
#    When I wait for the page to load
#    And I wait 10 seconds
#    Then I should see text matching "Thank you for shopping online with us, Test Test "
#    And I should see text matching "Your order number is "
#    #checking product attributes on order confirmation
#    When I click on ".product--count td" element
#    And I wait for AJAX to finish
#    Then I should see "size:"
#    And I should see "Item code:"
#    And I should see "Quantity:"
#
#  @cc @cs @newtest
#  Scenario: As a Guest user I should be able to search for a store on Map view select it and complete the checkout journey
#    When I follow "click & collect"
#    And I wait for the page to load
#    When I follow "Map view"
#    Then the "Map view" tab should be selected
#    When I select the first autocomplete option for "shuwaikh" on the "edit-store-location" field
#    And I wait for AJAX to finish
#    When I click the label for "#click-and-collect-map-view > div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(3) > div:nth-child(2) > div:nth-child(3) > div > img"
#    And I wait 10 seconds
#    When I click the label for "#click-and-collect-map-view > div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(3) > div:nth-child(2) > div:nth-child(4) > div > div.gm-style-iw > div:nth-child(1) > div > div > div.store-actions > a"
#    And I wait for AJAX to finish
#    And I wait for the page to load
#    When I fill in "edit-cc-firstname" with "Test"
#    And I fill in "edit-cc-lastname" with "Test"
#    When I enter a valid Email ID in field "edit-cc-email"
#    And I fill in "edit-cc-mobile-number-mobile" with "55004455"
#    And I select an element having class ".cc-action"
#    And I wait for AJAX to finish
#    When I select a payment option "payment_method_title_cybersource"
#    And I wait for AJAX to finish
#    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
#    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
#    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
#    When I fill in "edit-billing-address-address-billing-given-name" with "Test"
#    And I fill in "edit-billing-address-address-billing-family-name" with "Test"
#    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
#    And I select "Kuwait International Airport" from "edit-billing-address-address-billing-administrative-area"
#    And I fill in "edit-billing-address-address-billing-locality" with "Block A"
#    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
#    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
#    And I accept terms and conditions
#    And I press "place order"
#    When I wait for the page to load
#    And I wait 10 seconds
#    Then I should see text matching "Thank you for shopping online with us, Test Test "
#    #checking product attributes on order confirmation
#    When I click on ".product--count td" element
#    And I wait for AJAX to finish
#    Then I should see "size:"
#    And I should see "Item code:"
#    And I should see "Quantity:"
