@javascript @account @guest @contact-us @mujikwuat @coskwuat @mujisauat @cosaeuat @coskwuat @mujiaeuat @pbkkwuat @pbksauat @pbkaeuat @bpaeuat @tbseguat @bpkwuat @bpsauat @pbsauat @aeoaeuat @aeokwuat @aeosauat @westelmaeuat @westelmsauat @westelmkwuat @bpaeqa @tbskwuat @bbwsauat @mcsaqa @flsauat @hmaeuat @vskwqa @vsaeqa @flkwuat @hmkwqa @mckwuat @vsaeuat @vssauat @bbwkwuat @bbwaeuat @hmkwuat @hmsauat @mcsauat @mcaeuat @flaeuat @pbkwuat @pbsauat @pbaeuat
Feature: Verify the Contact Us page on the site as a guest user

  Background:
    When I go to "/contact"
    And I wait for element "#block-page-title h1.c-page-title"
    Then I should see an "#block-page-title h1.c-page-title" element

  @desktop @smoke
  Scenario: Verify contact us form is filled successfully and validation message shows up for required fields
    Then I click on "#edit-submit" element
    Then I should see an "#edit-first-name-error" element
    Then I should see an "#edit-last-name-error" element
    Then I should see an "#edit-email-error" element
    Then I should see an "#edit-feedback-error" element
    Then I should see an "#edit-message-error" element
    And I click jQuery "input[name='select_your_preference_of_channel_of_communication'][value='Mobile']" element on page
    And I wait for AJAX to finish
    When I click on "#edit-submit" element
    Then I should see an "#edit-first-name-error" element
    Then I should see an "#edit-last-name-error" element
    Then I should see an "#edit-mobile-number-mobile-error" element
    Then I should see an "#edit-email-error" element
    Then I should see an "#edit-feedback-error" element
    Then I should see an "#edit-message-error" element
    And I fill in "first_name" with "Test"
    And I fill in "last_name" with "User"
    And I fill in "mobile_number[mobile]" with "88855555"
    And I fill in "email" with "testuser@gmail.com"
    And I select "online_shopping" from "#edit-feedback" select2 field
    Then I should see an "#select2-edit-type-container" element
    And I select "inquiry" from "#edit-type" select2 field
    Then I should see an "#select2-edit-reason2-container" element
    And I select "others" from "#edit-reason2" select2 field
    And I fill in "order_number" with "140090"
    And I fill in "message" with "Client feedback"
    Then I click on "#edit-submit" element
    Then I should see an "div.form-item--error-message" element
    And I fill in "mobile_number[mobile]" with "55667788"
    And I wait for the page to load
    Then I click on "#edit-submit" element
    Then I should see a ".webform-confirmation__message" element on page

  @desktop @smoke
  Scenario: Verify contact us form is filled successfully for Email by the user without mobile number
    When I fill in "first_name" with "Test"
    And I fill in "last_name" with "User"
    And I fill in "email" with "testuser123@gmail.com"
    And I select "online_shopping" from "#edit-feedback" select2 field
    Then I should see an "#select2-edit-type-container" element
    And I select "complaint" from "#edit-type" select2 field
    Then I should see an "#select2-edit-reason2-container" element
    And I select "order_related" from "#edit-reason1" select2 field
    And I fill in "order_number" with "140090"
    And I fill in "message" with "Client feedback"
    Then I click on "#edit-submit" element
    And I wait for the page to load
    Then I should see a ".webform-confirmation__message" element on page

  @desktop
  Scenario: Verify contact us form is filled successfully for Email by the user for Feedback Inquiry
    When I fill in "first_name" with "Test"
    And I fill in "last_name" with "User"
    And I fill in "email" with "testuser456@gmail.com"
    And I select "feeback_inquiry" from "#edit-feedback" select2 field
    Then I should see an "#select2-edit-type-container" element
    And I select "complaint" from "#edit-type" select2 field
    Then I should see an "#select2-edit-reason2-container" element
    And I select "alshaya_card" from "#edit-reason3" select2 field
    And I fill in "order_number" with "140090"
    And I fill in "message" with "Client feedback"
    Then I click on "#edit-submit" element
    And I wait for the page to load
    Then I should see a ".webform-confirmation__message" element on page

  @desktop
  Scenario: Verify contact us form is filled successfully for Mobile by the user
    When I click jQuery "input[name='select_your_preference_of_channel_of_communication'][value='Mobile']" element on page
    And I wait for AJAX to finish
    When I fill in "first_name" with "Test"
    And I fill in "last_name" with "User"
    And I fill in "mobile_number[mobile]" with "55667788"
    And I fill in "email" with "testuser123@gmail.com"
    And I select "online_shopping" from "#edit-feedback" select2 field
    Then I should see an "#select2-edit-type-container" element
    And I select "complaint" from "#edit-type" select2 field
    Then I should see an "#select2-edit-reason2-container" element
    And I select "missing_items" from "#edit-reason1" select2 field
    And I fill in "order_number" with "140090"
    And I fill in "message" with "Client feedback"
    Then I click on "#edit-submit" element
    And I wait for the page to load
    Then I should see a ".webform-confirmation__message" element on page

  @desktop
  Scenario: Verify contact us form is filled successfully for Mobile by the user for Feedback Inquiry
    When I click jQuery "input[name='select_your_preference_of_channel_of_communication'][value='Mobile']" element on page
    And I wait for AJAX to finish
    And I fill in "first_name" with "Test"
    And I fill in "last_name" with "User"
    And I fill in "mobile_number[mobile]" with "55667788"
    And I fill in "email" with "testuser456@gmail.com"
    And I select "online_shopping" from "#edit-feedback" select2 field
    Then I should see an "#select2-edit-type-container" element
    And I select "inquiry" from "#edit-type" select2 field
    Then I should see an "#select2-edit-reason2-container" element
    And I select "exchange_refund_policy" from "#edit-reason4" select2 field
    And I fill in "order_number" with "140090"
    And I fill in "message" with "Client feedback"
    Then I click on "#edit-submit" element
    And I wait for the page to load
    Then I should see a ".webform-confirmation__message" element on page

  @language @contact-us @desktop
  Scenario: Verify contact us form is filled successfully and validation message shows up for required fields for arabic
    When I follow "{language_link}"
    And I wait for the page to load
    When I click on "#edit-submit" element
    Then I should see an "#edit-first-name-error" element
    Then I should see an "#edit-last-name-error" element
    Then I should see an "#edit-email-error" element
    Then I should see an "#edit-feedback-error" element
    Then I should see an "#edit-message-error" element
    And I click jQuery "input[name='select_your_preference_of_channel_of_communication'][value='Mobile']" element on page
    And I wait for AJAX to finish
    When I click on "#edit-submit" element
    Then I should see an "#edit-first-name-error" element
    Then I should see an "#edit-last-name-error" element
    Then I should see an "#edit-mobile-number-mobile-error" element
    Then I should see an "#edit-email-error" element
    Then I should see an "#edit-feedback-error" element
    Then I should see an "#edit-message-error" element
    And I fill in "first_name" with "Test"
    And I fill in "last_name" with "User"
    And I fill in "mobile_number[mobile]" with "88855555"
    And I fill in "email" with "testuser@gmail.com"
    And I select "online_shopping" from "#edit-feedback" select2 field
    Then I should see an "#select2-edit-type-container" element
    And I select "inquiry" from "#edit-type" select2 field
    Then I should see an "#select2-edit-reason2-container" element
    And I select "others" from "#edit-reason2" select2 field
    And I fill in "order_number" with "140090"
    And I fill in "message" with "Client feedback"
    Then I click on "#edit-submit" element
    Then I should see an "div.form-item--error-message" element
    And I fill in "mobile_number[mobile]" with "55667788"
    And I wait for the page to load
    Then I click on "#edit-submit" element
    Then I should see a ".webform-confirmation__message" element on page

  @mobile @contact-us
  Scenario: Verify contact us form is filled successfully and validation message shows up for required fields for mobile device
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    And I click on "#edit-submit" element
    Then I should see an "#edit-first-name-error" element
    Then I should see an "#edit-last-name-error" element
    Then I should see an "#edit-email-error" element
    Then I should see an "#edit-feedback-error" element
    Then I should see an "#edit-message-error" element
    And I click jQuery "input[name='select_your_preference_of_channel_of_communication'][value='Mobile']" element on page
    And I wait for AJAX to finish
    When I click on "#edit-submit" element
    Then I should see an "#edit-first-name-error" element
    Then I should see an "#edit-last-name-error" element
    Then I should see an "#edit-mobile-number-mobile-error" element
    Then I should see an "#edit-email-error" element
    Then I should see an "#edit-feedback-error" element
    Then I should see an "#edit-message-error" element
    And I fill in "first_name" with "Test"
    And I fill in "last_name" with "User"
    And I fill in "mobile_number[mobile]" with "88855555"
    And I fill in "email" with "testuser@gmail.com"
    And I select "online_shopping" from "#edit-feedback" select2 field
    Then I should see an "#edit-type" element
    And I select "inquiry" from "#edit-type" select2 field
    Then I should see an "#edit-reason2" element
    And I select "others" from "#edit-reason2" select2 field
    And I fill in "order_number" with "140090"
    And I fill in "message" with "Client feedback"
    Then I click on "#edit-submit" element
    Then I should see an "div.form-item--error-message" element
    And I fill in "mobile_number[mobile]" with "55667788"
    And I wait for the page to load
    Then I click on "#edit-submit" element
    Then I should see a ".webform-confirmation__message" element on page

  @mobile
  Scenario: Verify contact us form is filled successfully for Email by the user without mobile number
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    And I fill in "first_name" with "Test"
    And I fill in "last_name" with "User"
    And I fill in "email" with "testuser123@gmail.com"
    And I select "online_shopping" from "#edit-feedback" select2 field
    Then I should see an "#select2-edit-type-container" element
    And I select "complaint" from "#edit-type" select2 field
    Then I should see an "#select2-edit-reason2-container" element
    And I select "wrong_delivery" from "#edit-reason1" select2 field
    And I fill in "order_number" with "140090"
    And I fill in "message" with "Client feedback"
    Then I click on "#edit-submit" element
    And I wait for the page to load
    Then I should see a ".webform-confirmation__message" element on page

  @mobile
  Scenario: Verify contact us form is filled successfully for Email by the user for Feedback Inquiry
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    When I fill in "first_name" with "Test"
    And I fill in "last_name" with "User"
    And I fill in "email" with "testuser456@gmail.com"
    And I select "feeback_inquiry" from "#edit-feedback" select2 field
    Then I should see an "#select2-edit-type-container" element
    And I select "inquiry" from "#edit-type" select2 field
    Then I should see an "#select2-edit-reason2-container" element
    And I select "alshaya_privileges_club" from "#edit-reason4" select2 field
    And I fill in "order_number" with "140090"
    And I fill in "message" with "Client feedback"
    Then I click on "#edit-submit" element
    And I wait for the page to load
    Then I should see a ".webform-confirmation__message" element on page

  @mobile
  Scenario: Verify contact us form is filled successfully for Mobile by the user
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    And I click jQuery "input[name='select_your_preference_of_channel_of_communication'][value='Mobile']" element on page
    And I wait for AJAX to finish
    When I fill in "first_name" with "Test"
    And I fill in "last_name" with "User"
    And I fill in "mobile_number[mobile]" with "55667788"
    And I fill in "email" with "testuser123@gmail.com"
    And I select "online_shopping" from "#edit-feedback" select2 field
    Then I should see an "#select2-edit-type-container" element
    And I select "complaint" from "#edit-type" select2 field
    Then I should see an "#select2-edit-reason2-container" element
    And I select "refund_not_received" from "#edit-reason1" select2 field
    And I fill in "order_number" with "140090"
    And I fill in "message" with "Client feedback"
    Then I click on "#edit-submit" element
    And I wait for the page to load
    Then I should see a ".webform-confirmation__message" element on page
