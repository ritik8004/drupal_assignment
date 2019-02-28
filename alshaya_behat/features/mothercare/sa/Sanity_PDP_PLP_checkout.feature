@javascript
Feature: As a guest user I should be able to
  verify the PLP page ,verify fields on PDP,
  Select a product from PLP and proceed to checkout

  Background:
    Given I am on "/en/baby-clothing/baby-newborn-18-months/bodysuits"
    And I wait for the page to load

  Scenario: As a Guest user
  I should be able to verify the fields on
    PLP page
    Then I should be able to see the header
    And I should see the title and count of items
    Then I should be able to see the footer
    Then I should see "Filter"
    And I should see "Collection"
    Then I should see "Colour"
    And I should see "Price"
    Then I should see "Size"
    And I should see "Brand"
    When I follow "Load More"
    And I wait for the page to load
    Then more items should get loaded
    When I select "Name A to Z" from the dropdown
    And I wait for AJAX to finish
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



  Scenario: As a Guest user I should be able to select product from
    PLP page and verify the fields on PDP page
    PLP page and verify the fields on PDP page
    When I select a product from a product category
    And I wait for the page to load
    Then I should be able to see the header
    Then I should be able to see the footer
    Then it should display title, price and item code
    And I wait for AJAX to finish
    Then I should see the button "add to basket"
    Then I should see "Size : "
    And I should see "quantity"
    Then I should see the button "Add to basket"
    Then I should see "product description"
    And I should see the link for ".read-more-description-link"
    Then I should see buttons for facebook, Twitter and Pinterest
    And I should see "Delivery Options"
    Then I should see "Home Delivery"
    And I should see "delivered in 1-3 days ( main cities) and 1-5 (other areas) for just sar 25"
    Then I should see "Click and Collect"
    And I should see "free delivery to stores across kingdom of saudi arabia in 1-3 days ( main cities) and 1-5 ( other areas)"
    When I select a size for the product
    And I wait for AJAX to finish
    When I click the label for "#ui-id-2"
    And I wait 5 seconds
    Then I should see "home delivery"
    When I click the label for "#ui-id-4"
    Then I should see "This service is "
    And I should see "FREE"
    Then I should see " of charge."
    And I should see "Check in-store availability"
    When I select the first autocomplete option for "King Fahd Road, Jeddah Saudi Arabia" on the "edit-location" field
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I should see "Prince Mohammed Bin Abdulaziz St Jeddah AI Andalus"
    And I should see the link for ".change-location-link"
#    Then I should see "Other stores nearby"
    When I click the label for ".change-location-link"
    Then I select the first autocomplete option for "King Fahd Road, Jeddah Saudi Arabia" on the "store-location" field
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I should see "773 Mothercare Roshan Mall"
#    When I click the label for ".other-stores-link"
#    And I wait for AJAX to finish
#    Then I should see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
#    When I scroll to x "0" y "0" coordinates of page
#    When I click the label for ".close-inline-modal"
#    Then I should not see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
    When I click the label for ".read-more-description-link"
    And I wait for AJAX to finish
    Then I should see the inline modal for ".description-wrapper.desc-open"
    When I click the label for ".close"
    Then I should not see the inline modal for ".description-wrapper.desc-open"
    And I wait 10 seconds
    When I follow "size guide"
    And I wait for AJAX to finish
    Then I should see "Please select the category you require"
    When I press "Close"
    Then I should not see "Please select the category you require"
    And I should see the link "Size Guide"

  Scenario: As a Guest user I should be able to select product from
  PLP page, add to basket select Home Delivery and see COD, Cybersource
  and KNET as payment methods
    When I select a product from a product category
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    And I wait 10 seconds
#    When I go to "/cart/checkout/login"
    And I press "checkout securely"
    And I wait for the page to load
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I go to "/cart/checkout/delivery"
    And I wait for the page to load
    And I should be able to see the header for checkout
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
    And I go to "/cart/checkout/payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I accept terms and conditions
    And I press "place order"
    And I wait for the page to load



  Scenario: As a Guest user I should be able to select product from
  PLP page, add to basket select Click and Collect and see  Cybersource
  and KNET as payment methods
    When I select a product from a product category
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    And I wait 10 seconds
#    When I go to "/cart/checkout/login"
    And I press "checkout securely"
    And I wait for the page to load
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I wait for the page to load
    And I should be able to see the header for checkout
    When I follow "click & collect"
    And I wait for the page to load
    When I select the first autocomplete option for "King Fahd Road, Jeddah Saudi Arabia" on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "select this store"
    And I wait for AJAX to finish
    When I fill in "edit-cc-firstname" with "Test"
    And I fill in "edit-cc-lastname" with "Test"
    When I enter a valid Email ID in field "edit-cc-email"
    And I fill in "edit-cc-mobile-number-mobile" with "571898767"
    And I select an element having class ".cc-action"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "571898767"
    And I select "Dahran" from "edit-billing-address-address-billing-area-parent"
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I select "ad danah al janubiyah" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-address-line1" with "Street B"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "Building C"
    When I fill in "edit-billing-address-address-billing-address-line2" with "1"
    And I accept terms and conditions
    And I press "place order"
    And I wait for the page to load


