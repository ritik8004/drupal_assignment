@javascript @checkout @english @eng_checkout @mmcpa-1930 @manual
Feature: Test Checkout feature
#  Background:
#    Given I am logged in as an authenticated user "bharat.ahuja@acquia.com" with password "tester@123"
#    And I wait 5 seconds
#    And I am on "{url_product_page}"
#    And I wait for the page to load
#    When I press "Add to basket"
#    And I wait for AJAX to finish
#    And I wait 5 seconds
#    And I go to "/cart"
#    And I wait for the page to load
#    And I press "checkout securely"
#    And I wait for the page to load
#
#  @hd @2d @creditnosave
#  Scenario: As a auth user, I should be able to checkout using 2D credit- use new card (do not save)
#    And I wait 5 seconds
#    When I follow "Home delivery"
#    And I wait for AJAX to finish
#    When I select address
#    And I wait 5 seconds
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I scroll to the "#edit-actions-next" element
#    And I press "proceed to payment"
#    And I wait for AJAX to finish
#    When I select a payment option "payment_method_title_checkout_com"
#    And I wait for AJAX to finish
#    And I wait 5 seconds
#    When I select a payment option "payment_method_title_new"
#    And I wait 5 seconds
#    And I wait for AJAX to finish
#    And I fill in "cardName" with "Bharat Test"
#    And I fill in "cardNumber" with "4543 4740 0224 9996"
#    And I fill in "cardCvv" with "956"
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
#    And I should see "Quantity:"
#    And I go to "{url_product_page}"
#    And I wait 5 seconds
#    And I should be able to click my Account
#    And I wait for the page to load
#    Then I should see the link "Payment Cards" in ".my-account-nav" section
#    And I wait 5 seconds
#    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(5) > a"
#    And I wait 5 seconds
#    And I should not see "9996"
#
#  @hd @2d @creditsave
#  Scenario: As a auth user, I should be able to checkout using 2D credit- use new card (save card for future)
#    And I wait 5 seconds
#    When I follow "Home delivery"
#    And I wait for AJAX to finish
#    When I select address
#    And I wait 5 seconds
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I scroll to the "#edit-actions-next" element
#    And I press "proceed to payment"
#    And I wait for AJAX to finish
#    When I select a payment option "payment_method_title_checkout_com"
#    And I wait for AJAX to finish
#    And I wait 5 seconds
#    When I select a payment option "payment_method_title_new"
#    And I wait 5 seconds
#    And I wait for AJAX to finish
#    And I fill in "cardName" with "Bharat Test"
#    And I fill in "cardNumber" with "5436 0310 3060 6378"
#    And I fill in "cardCvv" with "257"
#    And I save card for future use
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
#    And I should see "Quantity:"
#    And I go to "{url_product_page}"
#    And I wait 5 seconds
#    And I should be able to click my Account
#    And I wait for the page to load
#    Then I should see the link "Payment Cards" in ".my-account-nav" section
#    And I wait 5 seconds
#    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(5) > a"
#    And I wait 5 seconds
#    And I should see "6378"
#
#  @hd @2d @creditsaved
#  Scenario: As a auth user, I should be able to checkout using 2D  already SAVED credit card
#    And I wait 5 seconds
#    When I follow "Home delivery"
#    And I wait for AJAX to finish
#    When I select address
#    And I wait 5 seconds
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I scroll to the "#edit-actions-next" element
#    And I press "proceed to payment"
#    And I wait for AJAX to finish
#    When I select a payment option "payment_method_title_checkout_com"
#    And I wait for AJAX to finish
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
#    And I should see "Quantity:"
#
#  @cod @hd
#  Scenario: As a Guest, I should be able to checkout using COD
#    When I follow "Home delivery"
#    And I wait for AJAX to finish
#    When I select address
#    And I wait for the page to load
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I scroll to the "#edit-actions-next" element
#    And I press "proceed to payment"
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cashondelivery"
#    And I wait for AJAX to finish
#    And I accept terms and conditions
#    And I press "place order"
#    And I wait for the page to load
#    Then I should see text matching "Thank you for shopping online with us, test test "
#    And I should see text matching "Your order number is "
#    And I wait 5 seconds
#    When I click on ".product--count td" element
#    And I wait for AJAX to finish
#    Then I should see "size:"
#    And I should see "Item code:"
#    And I should see "Quantity:"
#
#  @hd @knet
#  Scenario: As a Guest, I should be able to checkout using KNET
#    When I follow "Home delivery"
#    And I wait for AJAX to finish
#    When I select address
#    And I wait for the page to load
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I scroll to the "#edit-actions-next" element
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
#    And I wait 5 seconds
#    And I press "Confirm"
#    And I wait 5 seconds
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
#  @hd @3d @visa
#  Scenario: As a Guest, I should be able to checkout using 3D Credit Card
#    And I should be able to see the header for checkout
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
#    And I fill in "cardNumber" with "4484 0700 0003 5519"
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
#  @hd @3d @mastercard
#  Scenario: As a Guest, I should be able to checkout using 2D Debit Card
#    And I should be able to see the header for checkout
#    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Bharat"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
#    And I select "Kuwait International Airport" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
#    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Building C"
#    And I press "deliver to this address"
#    And I wait for AJAX to finish
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I press "proceed to payment"
#    And I wait 5 seconds
#    When I select a payment option "payment_method_title_checkout_com"
#    And I wait for AJAX to finish
#    And I fill in "cardName" with "Bharat Test"
#    And I fill in "cardNumber" with "5352 1515 7000 3404"
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
#  @hd @3d @wrongcvv
#  Scenario: As a Guest, I should not be able to checkout using 2D Credit Card when wrong CVV is used
#    And I should be able to see the header for checkout
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
#  @hd @2d @wrongcvv
#  Scenario: As a auth user, I should be able to checkout using 2D credit- use new card (save card for future)
#    When I follow "Home delivery"
#    And I wait for AJAX to finish
#    When I select address
#    And I wait 5 seconds
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I scroll to the "#edit-actions-next" element
#    And I press "proceed to payment"
#    And I wait for AJAX to finish
#    When I select a payment option "payment_method_title_checkout_com"
#    And I wait for AJAX to finish
#    And I wait 5 seconds
#    When I select a payment option "payment_method_title_new"
#    And I wait 5 seconds
#    And I wait for AJAX to finish
#    And I fill in "cardName" with "Bharat Test"
#    And I fill in "cardNumber" with "5436 0310 3060 6378"
#    And I fill in "cardCvv" with "258"
#    And I save card for future use
#    And I accept terms and conditions
#    And I wait 5 seconds
#    And I press "place order"
#    And I wait 10 seconds
#    Then I should not see text matching "Thank you for shopping online with us, Bharat Test"
#
#  @hd @2d @deletecard
#  Scenario: As a auth user, I should be able to delete cards from my account
#    And I go to "{url_product_page}"
#    And I wait 5 seconds
#    And I should be able to click my Account
#    And I wait for the page to load
#    Then I should see the link "Payment Cards" in ".my-account-nav" section
#    And I wait 5 seconds
#    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(5) > a"
#    And I wait 5 seconds
#    When I follow "Delete"
#    And I wait for AJAX to finish
#    When I press "No, take me back"
#    Then I should see "Payment cards"
#    When I follow "Delete"
#    And I wait for AJAX to finish
#    And I press "Yes, delete this card"
#    And I wait for AJAX to finish
#    Then I should see "Your card has been deleted."
