@manual @javascript @my_account
Feature: Test the My account section for authenticated user

  Background:
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load

  @prod
  Scenario:
    As an authenticated user
    I should be able to see all the sections
    after logging in
    Then I should see the link "my account" in ".my-account-nav" section
    And I should see the link "orders" in ".my-account-nav" section
    Then I should see the link "contact details" in ".my-account-nav" section
    And I should see the link "address book" in ".my-account-nav" section
    And I should see the link "change password" in ".my-account-nav" section
    Then the "my account" tab should be selected

  Scenario:
    As an authenticated user
    I should be able to see my most recent three orders
    on my account section
    Then I should see at most "3" recent orders listed
    And the order status should be visible for all products

  @prod
  Scenario Outline:
  As an authenticated user
  I should be able to view the Need help section
  and access the links under Need help
    When I see the text "Need help with your order?"
    Then I should see the link "Contact customer services"
    Then I should see the link "Online and in-store return policy"
    And I should see the link "Delivery Information"
    When I follow "<link>"
    And I wait for the page to load
    Then I should see "<text>"
    And the url should match "<url>"
    Examples:
      |link|text|url|
      |Contact customer services|Contact us|/contact|
      |Online and in-store return policy|Terms and Conditions of Sale|/terms-and-conditions-sale|
      |Delivery Information             |Delivery Information|/delivery-information|

  Scenario:
    As an authenticated user
    I should be able to view all my orders
    from my account page
    When I follow "View all orders"
    And I wait for the page to load
    Then the "orders" tab should be selected
    And I should see "recent orders"
    Then the url should match "/orders"
    And I should see text matching "Need help with your order?"
    Then I should see the link "Contact customer services"
    And I should see the link "Online and in-store return policy"
    Then I should see the link "Delivery Information"

  @loyalty
  Scenario: As an authenticated user
    I should be prompted to join the privilege club
    if I don't have a privilege account
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with "55004455"
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with ""
    And I press "Save"
    And I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li.my-account > a"
    And I wait for the page to load
    Then I should see "Join the club"
    And I should see "Win exciting prizes"
    Then I should see "Unlock exclusive rewards"
    Then I should see the link "Learn more"
    And I should not see "Privilege card number"

  @loyalty
  Scenario: As an authenticated user
    account details section should display Privilege card number
    along with Email address and Contact number
    When I follow "edit account details"
    And I wait for the page to load
    When I fill in "edit-field-mobile-number-0-mobile" with "55004455"
    When I click the label for "#ui-id-2 > p.title"
    When I fill in "edit-privilege-card-number" with "000135844"
    And I fill in "edit-privilege-card-number2" with "000135844"
    And I press "Save"
    And I wait for the page to load
    Then I should see "Contact details changes have been saved."
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li.my-account > a"
    And I wait for the page to load
    Then I should see "shweta+3@axelerant.com"
    Then I should see "6362 - 5440 - 0013 - 5844"
    But I should not see "Join the club"
    And I should not see "Win exciting prizes"
    Then I should not see "Unlock exclusive rewards"
    Then I should not see the link "Learn more"

  Scenario: As an authenticated user
    I should be able to see most recent 10 orders
    listed on Orders tab
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(2) > a"
    And I wait for the page to load
    Then I should see at most "10" recent orders listed on orders tab
    And the order status should be visible for all products
    When I press "show more"
    And I wait for AJAX to finish
    Then I should see at most "20" recent orders listed on orders tab

  Scenario: As an authenticated user
    I should be able to filter the listed orders
    by ID, name, SKU in combination with the Status of the order
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(2) > a"
    And I wait for the page to load
    When I fill in "edit-search" with "jersey"
    When I click the label for "#edit-submit-orders"
    And I wait for the page to load
    Then I should see at most "10" recent orders listed on orders tab
    Then I should see all "jersey" orders
    When I fill in "edit-search" with "HMKWSSE"
    And I wait 2 seconds
    When I click the label for "#edit-submit-orders"
    And I wait for the page to load
    Then I should see at most "10" recent orders listed on orders tab
    And I should see all orders for "HMKWSSE"

  Scenario: As an authenticated user
    I should be able to filter on all cancelled, dispatched and processing orders
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(2) > a"
    And I wait for the page to load
    When I select "Processing" from the dropdown
    And I wait for the page to load
    Then I should see all "Processing" orders listed on orders tab

  @prod
  Scenario: As an authenticated user
  I should be able to update my contact details
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(3) > a"
    And I wait for the page to load
    When I fill in "edit-field-first-name-0-value" with "Aadya"
    When I fill in "edit-field-last-name-0-value" with "Sharma"
    When I fill in "edit-field-mobile-number-0-mobile" with "55004466"
    And I press "Save"
    And I wait for the page to load
    Then I should see "Aadya"
    And I should not see "55004455"
    And I should see "Contact details changes have been saved."
    Then I fill in "edit-field-first-name-0-value" with "Test"
    And I fill in "edit-field-last-name-0-value" with "Test"
    When I fill in "edit-field-mobile-number-0-mobile" with "55004466"
    And I press "Save"
    
  @prod
  Scenario: As an authenticated user
    I should be able to add a new address
    to my address book
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    Then I get the total count of address blocks
    When I follow "Add new Address"
    And I wait for AJAX to finish
    When I fill in "field_address[0][address][given_name]" with "Test"
    And I fill in "field_address[0][address][family_name]" with "Test"
    When I fill in "field_address[0][address][mobile_number][mobile]" with "55004455"
    When I select "Abbasiya" from "field_address[0][address][administrative_area]"
    When I fill in "field_address[0][address][locality]" with "Block A"
    When I fill in "field_address[0][address][address_line1]" with "Street B"
    When I fill in "field_address[0][address][dependent_locality]" with "Sanyogita Apartment"
    When I fill in "field_address[0][address][address_line2]" with "5"
    And I press "add address"
    When I wait for AJAX to finish
    And I wait for the page to load
    Then I should see "Address is added successfully"
    And the new address block should be displayed on address book

  @prod
  Scenario: As an authenticated user
    I should be able to perform Cancel action on add/edit address pages
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    When I follow "Add new Address"
    And I wait for AJAX to finish
    When I follow "Cancel"
    And I wait for the page to load
    Then I should not see the text "First Name"
    When I click Edit Address
    And I wait for AJAX to finish
    When I follow "Cancel"
    And I wait for the page to load
    Then I should not see the text "First Name"

  @prod
  Scenario: As an authenticated user
    I should be able to edit an address
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    When I click Edit Address
    And I wait for AJAX to finish
    When I select "Abbasiya" from "field_address[0][address][administrative_area]"
    When I fill in "field_address[0][address][address_line2]" with "2"
    And I press "Save"
    When I wait for the page to load
    Then I should see "Address is updated successfully."

  @prod
  Scenario: As an authenticated user
    I should not be able to delete my primary address
    but I should be able to delete any other address
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    Then I should not see the delete button for primary address
    When I follow "Delete"
    And I wait for AJAX to finish
    When I press "No, take me back"
    Then I should see "Address book"
    Then I get the total count of address blocks
    When I follow "Delete"
    And I wait for AJAX to finish
    When I confirm deletion of address
    And I wait for AJAX to finish
    Then I should see "Address is deleted successfully."
    And the address block should be deleted from address book

  @prod
  Scenario: As an authenticated user
    I should be able to set my communication preferences
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(5) > a"
    And I wait for the page to load
    When I check "Email"
    And I press "save"
    And I wait for the page to load
    Then I should see "Your communication preference saved successfully."

  @prod
  Scenario: As an authenticated user
    I should see the options to change my password
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(6) > a"
    And I wait for the page to load
    Then I should see "Change Password"
    Then I should see "current password"
    And I should see "new password"
    Then I should see the button "change password"
    When I fill in "edit-pass" with ""
    And I wait 2 seconds
    Then I should see text matching "Your password must have at-least 7 characters."
    Then I should see text matching "Your password must contain at-least 1 special character."
    Then I should see text matching "Your password must contain at-least 1 numeric character."
    Then I should see text matching "Spaces are not allowed in your password."
    Then I should see text matching "The previous four passwords are not allowed."
    When I press "change password"
    Then I should see "Please enter your current password."
    And I should see "Please enter your new password."
