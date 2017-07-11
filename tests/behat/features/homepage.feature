@homepage @smoke @1732 @1717
Feature: Homepage
  I should be able to load the homepage
  With and without Javascript

  @javascript
  Scenario: Load a page with Javascript
    Given I am on "/"
    Then I should see the text "Sign in"

  Scenario: Load a page without Javascript
    Given I am on "/"
    Then I should see the text "Sign in"

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

  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials

    Given I go to "/user/login"
    When I fill in "edit-name" with "me+knet@nik4u.com"
    And I fill in "edit-pass" with "Test@123"
    And I press "sign in"
    Then I should see the link "My account"
    And I should see the link "Sign out"

  @arabic
  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials

    Given I go to "/user/login"
    And I follow "عربية"
    When I fill in "edit-name" with "me+knet@nik4u.com"
    And I fill in "edit-pass" with "Test@123"
    And I press "تسجيل الدخول"
    Then I should see the link "حسابي"
    And I should see the link "تسجيل الخروج"

  @javascript
  Scenario: As a Guest user
  I should be prompted with warning messages
  when I try to sign in without submitting any credentials

    Given I go to "user/login"
    When I press "sign in"
    And I wait for AJAX to finish
    Then I should see "Email address is required."
    And I should see "Password is required."

  @javascript @arabic
  Scenario: As a Guest user
  I should be prompted with warning messages
  when I try to sign in without submitting any credentials

    Given I go to "user/login"
    And I follow "عربية"
    When I press "تسجيل الدخول"
    And I wait for AJAX to finish
    Then I should see "يرجى إدخال عنوان البريد الإلكتروني"
    And I should see "يرجى إدخال كلمة السر"

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

