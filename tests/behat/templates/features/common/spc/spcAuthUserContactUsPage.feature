@javascript @account @smoke @auth @mujikwuat
Feature: Verify the contactus page on the site

  Background:
    When I am on user contact us page
    And I wait for the page to load
    Then I should see an "#block-page-title h1.c-page-title" element

  @contact-us
  Scenario: Verify contact us form is filled successfully and validation message shows up for required fields
    When I click on "#edit-submit" element
    Then I should see an "#edit-first-name-error" element
    Then I should see an "#edit-last-name-error" element
    Then I should see an "#edit-email-error" element
    Then I should see an "#edit-feedback-error" element
    Then I should see an "#edit-message-error" element
    And I click jQuery "input[name='select_your_preference_of_channel_of_communication'][value='Mobile']" element on page
    And I wait for AJAX to finish
    Then I should see an "#edit-first-name-error" element
    Then I should see an "#edit-last-name-error" element
    Then I should see an "#edit-mobile-number-mobile-error" element
    Then I should see an "#edit-email-error" element
    Then I should see an "#edit-feedback-error" element
    Then I should see an "#edit-message-error" element
    And I fill in "first_name" with "Test"
    And I fill in "last_name" with "User"
    And I fill in "mobile_number[mobile]" with "55667788"
    And I fill in "email" with "testuser@gmail.com"
    And I select "Online Shopping" from "#edit-feedback" select2 field
    And I wait for AJAX to finish
    Then I should see an "#select2-edit-type-container" element
    And I select "" from "<string>" select2 field
    And I wait for the page to load
    And I fill in "order_number" with "140090"
    And I fill in "message" with "Client feedback"
    Then I click on "#edit-submit" element


