@javascript @loyalty @manual @mmcpa-2352
Feature: Test privilege card features for Guest

  Scenario: As a Guest
    no PC number should be displayed on Order Confirmation page
    when there is no value on basket page and loyalty block details should appear
    Given I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    When follow "checkout as guest"
    And I wait for the page to load
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
    And I should not see text matching "Your Privileges Card Number is:"
    Then I should see "Join the club"
    And I should see "Win exciting prizes"
    Then I should see "Unlock exclusive rewards"
    And I should see "Be the first to know"
    Then I should see the link "Learn more"

  Scenario: As a Guest
    PC number from the basket should be displayed on Order confirmation page
    Given I am on a simple product page
    And I wait for the page to load
    When I press "Add to basket"
    And I wait for AJAX to finish
    Then I go to "/cart"
    And I wait for the page to load
    When I click the label for "#ui-id-4"
    And I wait 2 seconds
    When I fill in "edit-privilege-card-number" with "015118942"
    And I fill in "edit-privilege-card-number2" with "015118942"
    When I press "checkout securely"
    And I wait for the page to load
    When  I follow "checkout as guest"
    And I wait for the page to load
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
    Then I should see text matching "Your Privileges Card Number is: 6362 - 5440 - 1511 - 8942"
