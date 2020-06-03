@javascript @account
Feature: Test the My Account functionality

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_new_user_email}"
    And I fill in "edit-pass" with "{spc_new_user_password}"
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

  @address
  Scenario: As an authenticated user, I should be able to address to my address book
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait 10 seconds
    And I wait for the page to load
    When I fill in "full_name" with "{spc_full_name}"
    And I fill in "field_address[0][address][mobile_number][mobile]" with "{mobile}"
    And I select "{area_option}" from the dropdown "edit-field-address-0-address-administrative-area"
    When fill in billing address with following:
      | edit-field-address-0-address-address-line1                         | {street}      |
      | edit-field-address-0-address-dependent-locality                    | {building}    |
      | edit-field-address-0-address-locality                              | {locality}    |
      | edit-field-address-0-address-address-line2                         | {floor}       |
    And I press "edit-set-default"
    When I wait for AJAX to finish
    And I wait for the page to load
    Then the element "div.c-hero-content div.messages__wrapper div.messages--status" should exist
