@smoke @1732 @1717

Feature: Homepage
  I should be able to load the homepage
  With and without Javascript

  Scenario: As a Guest user
  I should be able to view the header and the footer

    Given I am on homepage
    Then I should be able to see the header
    And I should be able to see the footer
    And the page title should be "Home | Mothercare Kuwait"

  @arabic
  Scenario: On Arabic site,
  As a Guest user
  I should be able to view the header and the footer

    Given I am on homepage
    When I follow "عربية"
    Then I should be able to see the header in Arabic
    And I should be able to see the footer in Arabic
    And the page title should be "الرئيسية | مذركير الكويت"

  @javascript
  Scenario: As a Guest user
  I should be able to subscribe with Mothercare

    Given I am on homepage
    When I subscribe using a valid Email ID
    And I press "sign up"
    And I wait for AJAX to finish
    Then I should see the following success messages:
    | Thank you for your subscription. |

  @javascript @arabic
  Scenario: As a Guest user
  I should be able to subscribe with Mothercare

    Given I am on homepage
    And I follow "عربية"
    When I subscribe using a valid Email ID
    And I press "سجل الآن"
    And I wait for AJAX to finish
    Then I should see "شكرا لاشتراكك."

  @javascript
  Scenario: As a Guest user
  I should be displayed a warning message
  If I try to subscribe with subscribed Email ID

    Given I am on homepage
    When I fill in "edit-email" with "me+knet@nik4u.com"
    And I press "sign up"
    And I wait for AJAX to finish
    Then I should see "This email address is already subscribed."

  @javascript @arabic
  Scenario: As a Guest user
  I should be displayed a warning message
  If I try to subscribe with subscribed Email ID

    Given I am on homepage
    And I follow "عربية"
    When I fill in "edit-email" with "me+knet@nik4u.com"
    And I press "سجل الآن"
    And I wait for AJAX to finish
    Then I should see "عنوان البريد الإلكتروني هذا مشترك من قبل."
