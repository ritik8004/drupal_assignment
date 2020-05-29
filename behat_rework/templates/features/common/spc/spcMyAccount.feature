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
    And the element "#block-page-title .c-page-title" should exist
    And the element "#block-userrecentorders" should exist
    And the element "#block-userrecentorders .subtitle" should exist
    And the element "#block-userrecentorders table.recent__orders--list" should exist

  Scenario: As an authenticated user, I should be able to see all the sections after logging in
    Then I should see the link "my account" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "orders" in "#block-alshayamyaccountlinks .my-account-nav" section
    Then I should see the link "contact details" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "address book" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "change password" in "#block-alshayamyaccountlinks .my-account-nav" section
    And the element "#block-myaccountneedhelp" should exist
    And the element "#block-content .account-content-wrapper .email" should exist

  Scenario: As an authenticated user, I should be able to update my contact details
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(3) > a"
    And I wait for the page to load
    When I fill in "field_mobile_number[0][mobile]" with "{mobile}"
    And I press "edit-submit"
    And I wait for the page to load
    Then I should see "Contact details changes have been saved."
    Then the element "div.c-hero-content div.messages__wrapper div.messages--status" should exist

  @address
  Scenario: As an authenticated user, I should be able to edit address to my address book
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait 10 seconds
    And I wait for the page to load
    When I click the label for ".view-id-address_book > .views-field-rendered-entity > div.address--edit.address--controls > a"
    And I wait 10 seconds
    When I fill in "full_name" with "{spc_full_name}"
    And I fill in "field_address[0][address][mobile_number][mobile]" with "{mobile}"
    And I select "{area_option}" from the dropdown "edit-field-address-0-address-administrative-area"
    When fill in billing address with following:
      | edit-field-address-0-address-address-line1                         | {street}      |
      | edit-field-address-0-address-dependent-locality                    | {building}    |
      | edit-field-address-0-address-locality                              | {locality}    |
      | edit-field-address-0-address-address-line2                         | {floor}       |
    And I press "op"
    When I wait for AJAX to finish
    And I wait for the page to load
    Then the element "div.c-hero-content div.messages__wrapper div.messages--status" should exist
