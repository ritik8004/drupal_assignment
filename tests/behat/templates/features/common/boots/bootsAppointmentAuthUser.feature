@javascript @appointment @desktop @auth
Feature: Test Boots functionality for Authenticated User

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{boots_user_email}"
    And I fill in "edit-pass" with "{boots_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{boots_appointment_page}"
    And I wait for the page to load

  Scenario: User selects beauty Appointment
    Then I should not see an ".appointment-type-list-inner-wrapper" element
    When I click on ".appointment-type-wrapper li.appointment-category.BeautyandSkin" element
    And I wait 10 seconds
    Then I should see an ".appointment-type-list-inner-wrapper" element
    Then I click on ".appointment-select" element
    When I click the element ".appointmentSelect__menu #react-select-2-option-0" on page
    And I wait for AJAX to finish
    Then the element ".appointment-companion-wrapper .appointment-booking-section-title" should exist
    And the element ".appointment-companion-select" should exist
    Then the element ".appointment-for-you-wrapper .appointment-booking-section-title" should exist
    And the element ".appointment-for-you-container" should exist
    Then I click on ".appointment-companion-wrapper .appointment-select" element
    When I click the element ".appointment-companion-wrapper .appointmentSelect__menu #react-select-3-option-2" on page
    Then I scroll to the "#react-select-2-input" element
    Then I click on ".appointment-flow-action .appointment-type-button" element
    And I wait for AJAX to finish
    Then the element "#appointment-booking ul.appointment-steps li.select-store" having attribute "class" should contain "active"
    And I wait 10 seconds
    Then I select the first autocomplete option for "{boots_store_location}" on the "store_location" field
    And I wait 15 seconds
    Then I click jQuery "#appointment-map-store-list-view ul li[data-index=0] .appointment-store-name-wrapper" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I click jQuery "#appointment-select-store-submit-btn" element on page
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.select-time-slot" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I book first available slot
    And I wait for AJAX to finish
    Then I click jQuery ".appointment-flow-action button" element on page
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.select-login-guest" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I click on ".appointment-without-account button.appointment-checkout-button" element
    And I wait for AJAX to finish
    Then the element "#appointment-booking ul.appointment-steps li.customer-details" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I fill in "firstName" with "{boots_auth_firstname}"
    Then I fill in "lastName" with "{boots_auth_lastname}"
    When I click the element ".user-details-wrapper  .appointment-type-date .dob-input-wrapper .date-custom-input" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I fill in "email" with "{boots_auth_email}"
    Then I fill in "mobile" with "{customer_mobile}"
    And I wait 10 seconds
    Then I fill in "bootscompanion1name" with "{customer_CompanionfirstName}"
    Then I fill in "bootscompanion1lastname" with "{customer_CompanionlastName}"
    When I click the element "#appointment-booking div.customer-details-wrapper form div.companion-details-wrapper div.companion-details-questions div.bootscompanion1-details div.user-details-wrapper div.item.user-dob div.dob-input-wrapper span" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking div.companion-details-item.bootscompanion1-details .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I scroll to the ".companion-details-wrapper .companion-details-item.bootscompanion2-details" element
    And I wait for AJAX to finish
    Then I fill in "bootscompanion2name" with "{customer_Companion2firstName}"
    Then I fill in "bootscompanion2lastname" with "{customer_Companion2lastName}"
    When I click the element "#appointment-booking div.customer-details-wrapper form div.companion-details-wrapper div.companion-details-questions div.bootscompanion2-details div.user-details-wrapper div.item.user-dob div.dob-input-wrapper span" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking div.companion-details-item.bootscompanion2-details .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I click on ".appointment-flow-action button" element
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.confirmation" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I should see "{boots_auth_firstname} {boots_auth_lastname}"
    Then I should see "{customer_CompanionfirstName} {customer_CompanionlastName}"
    Then I should see "{customer_Companion2firstName} {customer_Companion2lastName}"

  Scenario: User selects Health and Pharmacy Appointment
    Then I should not see an ".appointment-type-list-inner-wrapper" element
    When I click on ".appointment-type-wrapper li.appointment-category.HealthandPharmacy" element
    And I wait 10 seconds
    Then I should see an ".appointment-type-list-inner-wrapper" element
    Then I click on ".appointment-select" element
    When I click the element ".appointmentSelect__menu #react-select-2-option-0" on page
    And I wait for AJAX to finish
    Then the element ".appointment-companion-wrapper .appointment-booking-section-title" should exist
    And the element ".appointment-companion-select" should exist
    Then the element ".appointment-for-you-wrapper .appointment-booking-section-title" should exist
    And the element ".appointment-for-you-container" should exist
    Then I click on ".appointment-companion-wrapper .appointment-select" element
    When I click the element ".appointment-companion-wrapper .appointmentSelect__menu #react-select-3-option-2" on page
    Then I scroll to the "#react-select-2-input" element
    Then I click on ".appointment-flow-action .appointment-type-button" element
    And I wait for AJAX to finish
    Then the element "#appointment-booking ul.appointment-steps li.select-store" having attribute "class" should contain "active"
    And I wait 10 seconds
    Then I select the first autocomplete option for "{boots_store_location}" on the "store_location" field
    And I wait 15 seconds
    Then I click jQuery "#appointment-map-store-list-view ul li[data-index=0] .appointment-store-name-wrapper" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I click jQuery "#appointment-select-store-submit-btn" element on page
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.select-time-slot" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I book first available slot
    And I wait for AJAX to finish
    Then I click jQuery ".appointment-flow-action button" element on page
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.select-login-guest" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I click on ".appointment-without-account button.appointment-checkout-button" element
    And I wait for AJAX to finish
    Then the element "#appointment-booking ul.appointment-steps li.customer-details" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I fill in "firstName" with "{boots_auth_firstname}"
    Then I fill in "lastName" with "{boots_auth_lastname}"
    When I click the element ".user-details-wrapper  .appointment-type-date .dob-input-wrapper .date-custom-input" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I fill in "email" with "{boots_auth_email}"
    Then I fill in "mobile" with "{customer_mobile}"
    And I wait 10 seconds
    Then I fill in "bootscompanion1name" with "{customer_CompanionfirstName}"
    Then I fill in "bootscompanion1lastname" with "{customer_CompanionlastName}"
    When I click the element "#appointment-booking div.customer-details-wrapper form div.companion-details-wrapper div.companion-details-questions div.bootscompanion1-details div.user-details-wrapper div.item.user-dob div.dob-input-wrapper span" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking div.companion-details-item.bootscompanion1-details .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I scroll to the ".companion-details-wrapper .companion-details-item.bootscompanion2-details" element
    And I wait for AJAX to finish
    Then I fill in "bootscompanion2name" with "{customer_Companion2firstName}"
    Then I fill in "bootscompanion2lastname" with "{customer_Companion2lastName}"
    When I click the element "#appointment-booking div.customer-details-wrapper form div.companion-details-wrapper div.companion-details-questions div.bootscompanion2-details div.user-details-wrapper div.item.user-dob div.dob-input-wrapper span" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking div.companion-details-item.bootscompanion2-details .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I click on ".appointment-flow-action button" element
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.confirmation" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I should see "{boots_auth_firstname} {boots_auth_lastname}"
    Then I should see "{customer_CompanionfirstName} {customer_CompanionlastName}"
    Then I should see "{customer_Companion2firstName} {customer_Companion2lastName}"

  @language
  Scenario: User selects beauty Appointment
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should not see an ".appointment-type-list-inner-wrapper" element
    When I click on ".appointment-type-wrapper li.appointment-category.BeautyandSkin" element
    And I wait 10 seconds
    Then I should see an ".appointment-type-list-inner-wrapper" element
    Then I click on ".appointment-select" element
    When I click the element ".appointmentSelect__menu #react-select-2-option-0" on page
    And I wait for AJAX to finish
    Then the element ".appointment-companion-wrapper .appointment-booking-section-title" should exist
    And the element ".appointment-companion-select" should exist
    Then the element ".appointment-for-you-wrapper .appointment-booking-section-title" should exist
    And the element ".appointment-for-you-container" should exist
    Then I click on ".appointment-companion-wrapper .appointment-select" element
    When I click the element ".appointment-companion-wrapper .appointmentSelect__menu #react-select-3-option-2" on page
    Then I scroll to the "#react-select-2-input" element
    Then I click on ".appointment-flow-action .appointment-type-button" element
    And I wait for AJAX to finish
    Then the element "#appointment-booking ul.appointment-steps li.select-store" having attribute "class" should contain "active"
    And I wait 10 seconds
    Then I select the first autocomplete option for "{boots_ar_store_location}" on the "store_location" field
    And I wait 15 seconds
    Then I click jQuery "#appointment-map-store-list-view ul li[data-index=0] .appointment-store-name-wrapper" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I click jQuery "#appointment-select-store-submit-btn" element on page
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.select-time-slot" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I book first available slot
    And I wait for AJAX to finish
    Then I click jQuery ".appointment-flow-action button" element on page
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.select-login-guest" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I click on ".appointment-without-account button.appointment-checkout-button" element
    And I wait for AJAX to finish
    Then the element "#appointment-booking ul.appointment-steps li.customer-details" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I fill in "firstName" with "{boots_auth_firstname}"
    Then I fill in "lastName" with "{boots_auth_lastname}"
    When I click the element ".user-details-wrapper  .appointment-type-date .dob-input-wrapper .date-custom-input" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I fill in "email" with "{boots_auth_email}"
    Then I fill in "mobile" with "{customer_mobile}"
    And I wait 10 seconds
    Then I fill in "bootscompanion1name" with "{customer_CompanionfirstName}"
    Then I fill in "bootscompanion1lastname" with "{customer_CompanionlastName}"
    When I click the element "#appointment-booking div.customer-details-wrapper form div.companion-details-wrapper div.companion-details-questions div.bootscompanion1-details div.user-details-wrapper div.item.user-dob div.dob-input-wrapper span" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking div.companion-details-item.bootscompanion1-details .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I scroll to the ".companion-details-wrapper .companion-details-item.bootscompanion2-details" element
    And I wait for AJAX to finish
    Then I fill in "bootscompanion2name" with "{customer_Companion2firstName}"
    Then I fill in "bootscompanion2lastname" with "{customer_Companion2lastName}"
    When I click the element "#appointment-booking div.customer-details-wrapper form div.companion-details-wrapper div.companion-details-questions div.bootscompanion2-details div.user-details-wrapper div.item.user-dob div.dob-input-wrapper span" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking div.companion-details-item.bootscompanion2-details .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I click on ".appointment-flow-action button" element
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.confirmation" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I should see "{boots_auth_firstname} {boots_auth_lastname}"
    Then I should see "{customer_CompanionfirstName} {customer_CompanionlastName}"
    Then I should see "{customer_Companion2firstName} {customer_Companion2lastName}"

  @language
  Scenario: User selects Health and Pharmacy Appointment
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I should not see an ".appointment-type-list-inner-wrapper" element
    When I click on ".appointment-type-wrapper li.appointment-category.HealthandPharmacy" element
    And I wait 10 seconds
    Then I should see an ".appointment-type-list-inner-wrapper" element
    Then I click on ".appointment-select" element
    When I click the element ".appointmentSelect__menu #react-select-2-option-0" on page
    And I wait for AJAX to finish
    Then the element ".appointment-companion-wrapper .appointment-booking-section-title" should exist
    And the element ".appointment-companion-select" should exist
    Then the element ".appointment-for-you-wrapper .appointment-booking-section-title" should exist
    And the element ".appointment-for-you-container" should exist
    Then I click on ".appointment-companion-wrapper .appointment-select" element
    When I click the element ".appointment-companion-wrapper .appointmentSelect__menu #react-select-3-option-2" on page
    Then I scroll to the "#react-select-2-input" element
    Then I click on ".appointment-flow-action .appointment-type-button" element
    And I wait for AJAX to finish
    Then the element "#appointment-booking ul.appointment-steps li.select-store" having attribute "class" should contain "active"
    And I wait 10 seconds
    Then I select the first autocomplete option for "{boots_ar_store_location}" on the "store_location" field
    And I wait 15 seconds
    Then I click jQuery "#appointment-map-store-list-view ul li[data-index=0] .appointment-store-name-wrapper" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    Then I click jQuery "#appointment-select-store-submit-btn" element on page
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.select-time-slot" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I book first available slot
    And I wait for AJAX to finish
    Then I click jQuery ".appointment-flow-action button" element on page
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.select-login-guest" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I click on ".appointment-without-account button.appointment-checkout-button" element
    And I wait for AJAX to finish
    Then the element "#appointment-booking ul.appointment-steps li.customer-details" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I fill in "firstName" with "{boots_auth_firstname}"
    Then I fill in "lastName" with "{boots_auth_lastname}"
    When I click the element ".user-details-wrapper  .appointment-type-date .dob-input-wrapper .date-custom-input" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I fill in "email" with "{boots_auth_email}"
    Then I fill in "mobile" with "{customer_mobile}"
    And I wait 10 seconds
    Then I fill in "bootscompanion1name" with "{customer_CompanionfirstName}"
    Then I fill in "bootscompanion1lastname" with "{customer_CompanionlastName}"
    When I click the element "#appointment-booking div.customer-details-wrapper form div.companion-details-wrapper div.companion-details-questions div.bootscompanion1-details div.user-details-wrapper div.item.user-dob div.dob-input-wrapper span" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking div.companion-details-item.bootscompanion1-details .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I scroll to the ".companion-details-wrapper .companion-details-item.bootscompanion2-details" element
    And I wait for AJAX to finish
    Then I fill in "bootscompanion2name" with "{customer_Companion2firstName}"
    Then I fill in "bootscompanion2lastname" with "{customer_Companion2lastName}"
    When I click the element "#appointment-booking div.customer-details-wrapper form div.companion-details-wrapper div.companion-details-questions div.bootscompanion2-details div.user-details-wrapper div.item.user-dob div.dob-input-wrapper span" on page
    And I wait for AJAX to finish
    When I click the element "#appointment-booking div.companion-details-item.bootscompanion2-details .item.user-dob div.react-datepicker__day.react-datepicker__day--today" on page
    And I wait for AJAX to finish
    Then I click on ".appointment-flow-action button" element
    And I wait 10 seconds
    Then the element "#appointment-booking ul.appointment-steps li.confirmation" having attribute "class" should contain "active"
    And I wait for AJAX to finish
    Then I should see "{boots_auth_firstname} {boots_auth_lastname}"
    Then I should see "{customer_CompanionfirstName} {customer_CompanionlastName}"
    Then I should see "{customer_Companion2firstName} {customer_Companion2lastName}"
