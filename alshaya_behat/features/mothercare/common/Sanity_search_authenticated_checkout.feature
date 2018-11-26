@javascript
Feature: to verify search functionality , basket and checkout

  Scenario: As a Guest user
  I should be able to search, verify filter,footer, header and sort on results page
    Given I am on the homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I follow "English"
    Then I should be able to see the header
    And I should be able to see the footer
    When I fill in "edit-keywords" with "randomtext"
    And I press "Search"
    And I wait for the page to load
    Then I should see "Your search did not return any results."
    When I fill in "edit-keywords" with "green t shirt"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "green t shirt"
    When I select "Name A to Z" from the dropdown
    And I wait for the page to load
    Then I should see results sorted in ascending order
    When I select "Name Z to A" from the dropdown
    And I wait for the page to load
    Then I should see results sorted in descending order
    When I select "Price High to Low" from the dropdown
    And I wait for the page to load
    Then I should see results sorted in descending price order
    When I select "Price Low to High" from the dropdown
    And I wait for the page to load
    Then I should see results sorted in ascending price order
    When I follow "Load More"
    And I wait for AJAX to finish
    Then more items should get loaded
    Then I should see "Size"
    And I should see "Colour"
    Then I should see "Price"


  Scenario: As an authenticated user
  I should be able to search product, add product to basket
  and verify the fields on basket
    Given I am logged in as an authenticated user "karnika.jain+test@qed42.com" with password "Password@1"
    And I wait for the page to load
    When I fill in "edit-keywords" with "green t shirtt"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "green t shirt"
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "add to basket"
    And I wait for AJAX to finish
    When I go to "/en/cart"
    And I wait for the page to load
    Then I should see the button "checkout securely"
    And I should see "Basket ("
    Then I should see "Product"
    And I should see "Quantity"
    Then I should see "Unit Price"
    And I should see "Subtotal"
    Then I should see "Available delivery options"
    Then I should see "Order Total"
    And I should see "(Before Delivery)"
    Then I should see the link "continue shopping" in ".edit-actions.form-actions.js-form-wrapper.form-wrapper" section
    And I should see "Add a promotional code"
    When I hover over tooltip "p.home-delivery.tooltip--head"
    And I wait 2 seconds
    Then I should see "Free Delivery in 1-2 days on orders over kwd 20"
    When I hover over tooltip "p.click-collect.tooltip--head"
    And I wait 2 seconds
    Then I should see "Collect the order in store within 1-2 days"
    And I should see "Add a promotional code"
    When I select 2 from dropdown
    And I wait for AJAX to finish
    Then I should see the price doubled for the product
    When I follow "remove"
    And I wait for the page to load
    Then I should see "The product has been removed from your basket."
    Then I should be able to see the footer



  Scenario: As an authenticated user
  I should be able to search for a product
  and add it to the cart, select Home Delivery and see COD, KNET and Cybersource
  Payment methods
    Given I am logged in as an authenticated user "karnika.jain+test@qed42.com" with password "Password@1"
    And I wait for the page to load
    When I fill in "edit-keywords" with "green t shirt"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "green t shirt"
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "add to basket"
    And I wait for AJAX to finish
    When I go to "/en/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
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
    Then I should see "I confirm that I have read and accept the"
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I accept terms and conditions
    Then I should see "I confirm that I have read and accept the"
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I wait for the page to load
    And I press "place order"
    And I wait for the page to load
    And I press "CancelAction_id"


  Scenario: As an authenticated user
  I should be able to search for a product
  and add it to the cart, select Click & Collect and see  KNET and Cybersource
  Payment methods
    Given I am logged in as an authenticated user "karnika.jain+test@qed42.com" with password "Password@1"
    And I wait for the page to load
    When I fill in "edit-keywords" with "green t shirt"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "green t shirt"
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "add to basket"
    And I wait for AJAX to finish
    When I go to "/en/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    When I follow "click & collect"
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
    Then I should see "I confirm that I have read and accept the"
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
    And I press "CancelAction_id"


  Scenario: As a returning customer
  I should be able to place an order for HD - COD, KNET and Cybersource
    Given I am on a configurable product
    And I wait for the page to load
    And I wait for AJAX to finish
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
