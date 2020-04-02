@javascript
Feature: Test the My Account functionality

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_user_email}"
    And I fill in "edit-pass" with "{spc_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    Then I should be on "/user" page

  Scenario: Authenticated user should be able to login into the system
    Then I should see "My Account"
    And I should see "recent orders"
    And the element "#block-userrecentorders .no--orders" should exist
    And the element "#block-userrecentorders .subtitle" should exist
    And the element "#block-userrecentorders .edit-account" should exist
    And I should see "You have no recent orders to display."

  Scenario: As an authenticated user, I should be able to see all the sections after logging in
    Then I should see the link "my account" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "orders" in "#block-alshayamyaccountlinks .my-account-nav" section
    Then I should see the link "contact details" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "address book" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "communication preferences" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "change password" in "#block-alshayamyaccountlinks .my-account-nav" section
    And the element "#block-myaccountneedhelp" should exist
    And the element "#block-content .account-content-wrapper .email" should exist