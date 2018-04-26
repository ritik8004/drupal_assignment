@javascript
Feature: Test Checkout feature
  Background:
    Given I am on a configurable product
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to basket"
    And I wait for AJAX to finish
    And I go to "/en/cart"
    And I wait for the page to load
    And I press "checkout securely"
    And I wait for the page to load
    And I follow "checkout as guest"
    And I wait for the page to load

  Scenario: As a Guest,
  I should be able to checkout using COD
    And I should be able to see the header for checkout
    And I should not see the link "create an account"
    And I should not see the link "Sign in"
    And I should not see the link "Find Store"
    And I should not see "عربية"
    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "571012345"
    And I select "Dahran" from "edit-guest-delivery-home-address-shipping-area-parent"
    And I wait for AJAX to finish
    And I select "ad danah ash shamaliyah" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I press "deliver to this address"
    And I wait for AJAX to finish
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    And I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test"
    And I should see text matching "Your order number is "

  Scenario: As a Guest
    I should be able to view the number of results displayed
    Buttons to toggle between list and Map view
    and link to navigate to the basket
    And I should be able to see the header for checkout
    And I follow "click & collect"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Janubiyah Saudi Arabia" on the "edit-store-location" field
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then I should see the number of stores displayed
    And I should see the link "List view"
    And I should see the link "Map view"
    And I should see the link "back to basket"

  Scenario: As a Guest
    I should be able to see the two tabs
    on Click and Collect
    When I follow "click & collect"
    And I wait for the page to load
    Then I should see the link "List view"
    And I should see the link "Map view"
    But the "List view" tab should be selected

  Scenario: As a Guest
    I should be able to see various options
    for each Store on click & collect
    When I follow "click & collect"
    And I select the first autocomplete option for "Janubiyah Saudi Arabia" on the "edit-store-location" field
    When I wait for AJAX to finish
    And I wait 5 seconds
    Then I should see store name and location for all the listed stores
    And I should see opening hours for all the listed stores
    Then I should see collect in store info for all the listed stores
    And I should see select this store for all the listed stores
    Then I should see view on map button for all the listed stores

  Scenario: As a Guest
    I should be navigated to basket page
    On clicking 'back to basket' from checkout CC page
    When I follow "click & collect"
    And I wait for the page to load
    When I select the first autocomplete option for "Janubiyah Saudi Arabia" on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "back to basket"
    Then I should see the button "checkout securely"
    And the url should match "/cart"

  Scenario: As a Guest
    I should be able to see the store timings
    on clicking the Opening hours link and
    link should toggle
    When I follow "click & collect"
    And I wait for the page to load
    When I select the first autocomplete option for "Janubiyah Saudi Arabia" on the "edit-store-location" field
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click the label for "div.hours--label"
    And I wait for AJAX to finish
    Then I should see "Monday"
    And I should see "Tuesday"
    When I click the label for ".hours--label.open"
    Then I should not see "Monday"
    And I should not see "Tuesday"

  Scenario: As a Guest
    I should be able to checkout on HD
    using Cybersource payment method
    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "571012345"
    And I select "Dahran" from "edit-guest-delivery-home-address-shipping-area-parent"
    And I wait for AJAX to finish
    And I select "ad danah ash shamaliyah" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I press "deliver to this address"
    And I wait for AJAX to finish
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for AJAX to finish
    When I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I accept terms and conditions
    And I press "place order"
    When I wait for the page to load
    And I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "

  Scenario:  As a Guest
  I should be able to checkout on Click and Collect
  using Cybersource payment method
    When I follow "click & collect"
    And I wait for the page to load
    When I select the first autocomplete option for "Janubiyah Saudi Arabia" on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "select this store"
    And I wait for AJAX to finish
    When I fill in "edit-cc-firstname" with "Test"
    And I fill in "edit-cc-lastname" with "Test"
    When I enter a valid Email ID in field "edit-cc-email"
    And I fill in "edit-cc-mobile-number-mobile" with "571234567"
    And I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I fill in "edit-billing-address-address-billing-given-name" with "Test"
    And I fill in "edit-billing-address-address-billing-family-name" with "Test"
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "571012345"
    And I select "Dahran" from "edit-billing-address-address-billing-area-parent"
    And I wait for AJAX to finish
    And I select "ad danah ash shamaliyah" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Builing C"
    And I accept terms and conditions
    And I press "place order"
    When I wait for the page to load
    And I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us, Test Test "
    And I should see text matching "Your order number is "

  Scenario: As a Guest user
  I should be able to see order summary, back to basket option
  and the customer service block
    When I follow "Home delivery"
    And I wait for the page to load
    Then I should see the Order Summary block
    And I should see the Customer Service block
    When I follow "edit"
    And I wait for the page to load
    Then the url should match "/cart"
    And I should see the button "checkout securely"

  Scenario: As a Guest user
  I should be able to search for a store on Map view
  select it and complete the checkout journey
    When I follow "click & collect"
    And I wait for the page to load
    When I follow "Map view"
    Then the "Map view" tab should be selected
    When I select the first autocomplete option for "Janubiyah Saudi Arabia" on the "edit-store-location" field
    And I wait for AJAX to finish
    When I click the label for "#click-and-collect-map-view > div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(3) > div:nth-child(2) > div:nth-child(3) > div > img"
    And I wait 10 seconds
    When I click the label for "#click-and-collect-map-view > div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(3) > div:nth-child(2) > div:nth-child(4) > div > div.gm-style-iw > div:nth-child(1) > div > div > div.store-actions > a"
    And I wait for AJAX to finish
    And I wait for the page to load
    When I fill in "edit-cc-firstname" with "Test"
    And I fill in "edit-cc-lastname" with "Test"
    When I enter a valid Email ID in field "edit-cc-email"
    And I fill in "edit-cc-mobile-number-mobile" with "55004455"
    And I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I fill in "edit-billing-address-address-billing-given-name" with "Test"
    And I fill in "edit-billing-address-address-billing-family-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "571012345"
    And I select "Dahran" from "edit-guest-delivery-home-address-shipping-area-parent"
    And I wait for AJAX to finish
    And I select "ad danah ash shamaliyah" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I accept terms and conditions
    And I press "place order"
    When I wait for the page to load
    And I wait 10 seconds
    Then I should see text matching "Thank you for shopping online with us, Test Test "

  Scenario: As a Guest user
  whenever I click 'back to basket' link on Map view
  I should be redirected to the basket page
    When I follow "click & collect"
    And I wait for the page to load
    When I follow "Map view"
    Then the "Map view" tab should be selected
    When I select the first autocomplete option for "Janubiyah Saudi Arabia" on the "edit-store-location" field
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click the label for "#click-and-collect-map-view > div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(3) > div:nth-child(2) > div:nth-child(3) > div > img"
    When I wait 2 seconds
    When I click the label for "#click-and-collect-map-view > div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(3) > div:nth-child(2) > div:nth-child(4) > div > div.gm-style-iw > div:nth-child(1) > div > div > div.store-open-hours > div > div.hours--label"
    And I wait 2 seconds
    When I follow "back to basket"
    Then I should see the button "checkout securely"
    And the url should match "/cart"
