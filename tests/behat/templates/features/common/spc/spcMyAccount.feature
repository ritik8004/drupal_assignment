@javascript @smoke @pbkkwuat @mujikwuat @cosaeuat @coskwuat @mujisauat @mujiaeuat @pbksauat @pbkaeuat @bpaeuat @tbseguat @bpkwuat @bpsauat @aeoaeuat @aeokwuat @aeosauat @westelmaeuat @westelmsauat @westelmkwuat @pbsauat @hmaeuat @mckwuat @flsauat @bbwsauat @mcsauat @mcaeuat @pbaeuat @vssauat @mcsauat @bbwkwuat @mckwuat @hmkwuat @hmsauat @flkwuat @flaeuat @bbwaeuat @vsaeuat
Feature: Test the My Account functionality

  Background:
    Given I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait for element "#block-page-title"

  Scenario: Authenticated user should be able to login into the system
    And the element "#block-page-title .c-page-title" should exist
    And the element "#block-userrecentorders" should exist
    And the element "#block-userrecentorders .subtitle" should exist

  Scenario: As an authenticated user, I should be able to see all the sections after logging in
    Then I should see an "#block-alshayamyaccountlinks .my-account" element
    And I should see the link "orders" in "#block-alshayamyaccountlinks .my-account-nav" section
    Then I should see the link "contact details" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "address book" in "#block-alshayamyaccountlinks .my-account-nav" section
    And I should see the link "change password" in "#block-alshayamyaccountlinks .my-account-nav" section
    And the element "#block-myaccountneedhelp" should exist
    And the element "#block-content .account-content-wrapper .email" should exist
    Then I should see the link for ".show-all"
    And I follow "View all orders"
    And I wait for the page to load
    Then I should see an "#block-page-title" element

  Scenario: As an authenticated user, I should be able to update my contact details
    When I click the label for "#block-alshayamyaccountlinks a.my-account-contact-details"
    And I wait for the page to load
    When I fill in "field_mobile_number[0][mobile]" with "{mobile}"
    And I press "edit-submit"
    And I wait for the page to load
    Then I should see "Contact details changes have been saved."
    Then the element "div.c-hero-content div.messages__wrapper div.messages--status" should exist

  Scenario: As an authenticated user, I should be able to view the Need help section and access the links under Need help
    Then the element "#block-myaccountneedhelp" should exist
    And I should see an "div.field--type-text-with-summary ul a" element

  @address
  Scenario: As an authenticated user, I should be able to edit address to my address book
    When I click the label for "#block-alshayamyaccountlinks a.my-account-address-book"
    And I wait 10 seconds
    And I wait for the page to load
    Then I check the address-book form
    When I fill in "full_name" with "{spc_full_name}"
    And I fill in "field_address[0][address][mobile_number][mobile]" with "{mobile}"
    And I select "City" option from "field_address[0][address][area_parent]"
    And I wait for AJAX to finish
    Then I select "Area" option from "field_address[0][address][administrative_area]"
    When I scroll to the ".country-field-wrapper" element
    When fill in billing address with following:
      | field_address[0][address][address_line1]             | {street}      |
      | field_address[0][address][dependent_locality]        | {building}    |
      | field_address[0][address][locality]                  | {locality}    |
      | field_address[0][address][address_line2]             | {floor}       |
      | field_address[0][address][sorting_code]              | {landmark}    |
      | field_address[0][address][postal_code]               | {postal_code} |
    And I press "op"
    When I wait for AJAX to finish
    And I wait for the page to load
    Then the element "div.c-hero-content div.messages__wrapper div.messages--status" should exist

  @language
  Scenario: As an authenticated user, I should be able to view the Need help section and access the links under Need
  help in another language
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    Then the element "#block-myaccountneedhelp" should exist
    And the element "#block-myaccountneedhelp .field--type-text-with-summary" should exist
    And I should see the link "خدمة الزبائن"
    And I should see the link "معلومات التوصيل"

  @search-by-name @search-by-ID
  Scenario: As an authenticated user, I should be able to filter the listed orders by name in combination
  with the Status of the order.
    When I click the label for "#block-alshayamyaccountlinks a.my-account"
    And I wait 10 seconds
    And I wait for the page to load
    Then the element "#alshaya-acm-customer-order-list-search" should exist
    Then the element "#select2-edit-filter-container" should exist
    Then I fill in "edit-search" with "{spc_search_byname}"
    And I click on "button#edit-submit-orders" element
    And I wait 10 seconds
    And I wait for the page to load
    Then the element "ul.order-items li.order-item" should exist
    When I click the label for "#block-alshayamyaccountlinks a.my-account"
    And I wait 10 seconds
    And I wait for the page to load
    Then I fill in "edit-search" with "{spc_search_byID}"
    And I click on "button#edit-submit-orders" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "ul.order-items li:first-child" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see an ".order-summary-row" element
    Then I should see an "div.order-transaction" element

  @cancel
  Scenario: As an authenticated user, I should be able to perform Cancel action on add/edit address pages
    When I click the label for "#block-alshayamyaccountlinks a.my-account-address-book"
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "#block-content a" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "a.button.cancel-button" element
    And I wait 10 seconds
    Then the element "div.view-id-address_book .user__address--column div.address.default" should exist
    Then the element "div.view-id-address_book .user__address--column div.address.default .address--delete" should not exist
    Then the element "div.view-id-address_book .user__address--column div.address.default .address--edit" should exist
    Then the element "div.view-id-address_book .user__address--column:nth-child(2) div.address" should exist
    Then the element "div.view-id-address_book .user__address--column:nth-child(2) div.address .address--delete" should exist
    Then the element "div.view-id-address_book .user__address--column:nth-child(2) div.address .address--edit" should exist
    Then I click on "div.view-id-address_book .user__address--column:nth-child(2) div.address .address--delete" element
    And I wait 10 seconds
    Then I click on "form.profile-address-book-delete-form.profile-confirm-form div.form-actions a.button.dialog-cancel" element
    Then the element "div.view-id-address_book .user__address--column:nth-child(2) div.address .address--delete" should exist

  @delete
  Scenario: As an authenticated user, I should not be able to delete my primary address but should be able to delete any
  other address
    When I click the label for "#block-alshayamyaccountlinks a.my-account-address-book"
    And I wait 10 seconds
    And I wait for the page to load
    Then the element "div.view-id-address_book .user__address--column div.address.default" should exist
    Then the element "div.view-id-address_book .user__address--column div.address.default .address--delete" should not exist
    Then the element "div.view-id-address_book .user__address--column:nth-child(2) div.address" should exist
    Then the element "div.view-id-address_book .user__address--column:nth-child(2) div.address .address--delete" should exist
    Then I click on "div.view-id-address_book .user__address--column:nth-child(2) div.address .address--delete" element
    And I wait 10 seconds
    Then I click on "form.profile-address-book-delete-form.profile-confirm-form div.form-actions button" element
    When I wait for AJAX to finish
    And I wait for the page to load
    Then the element "div.c-hero-content div.messages__wrapper div.messages--status" should exist

  @change-password
  Scenario: As an authenticated user, I should see the options to change my password
     When I click the label for "#block-alshayamyaccountlinks a.my-account-change-password"
     And I wait 10 seconds
     And I wait for the page to load
     And I should see an "#block-page-title" element
     And I should see an "#edit-current-pass" element
     And I should see an "#edit-pass" element
     And I fill in an element having class "#edit-current-pass" with "Admin@123"
     And I fill in an element having class "#edit-pass" with "Admin@123"
     Then I press "edit-submit"
     And I wait for the page to load
     Then the element ".form-item--error-message" should exist

