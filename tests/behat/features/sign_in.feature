@smoke @1747

Feature: Test Sign in and Forgot password features

  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials

    Given I go to "/user/login"
    When I fill in "edit-name" with "me+knet@nik4u.com"
    And I fill in "edit-pass" with "Test@123"
    And I press "sign in"
    Then I should see the link "My account"
    And I should see the link "Sign out"
    And I should see "recent orders"

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
    And I should see "أحدث الطلبيات"

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
  I should be shown error messages
  when I try to login using invalid email ID and password

    Given I am on "/user/login"
    When I fill in "edit-name" with "name@surname@gmail.com"
    And I fill in "edit-pass" with "invalidpassword"
    And I press "sign in"
    Then I should see "Unrecognized email address or password."

  @javascript @arabic
  Scenario: As a Guest user
  I should be shown error messages
  when I try to login using invalid email ID and password

    Given I am on "/user/login"
    And I follow "عربية"
    When I fill in "edit-name" with "name@surname@gmail.com"
    And I fill in "edit-pass" with "invalidpassword"
    And I press "تسجيل الدخول"
    Then I should see "لم يتم التعرف على عنوان البريد الإلكتروني أو كلمة السر"

  Scenario: As a Guest user
    I should be able to reset my password
    after providing valid Email ID

    Given I am on "/user/login"
    And I follow "Forgot password?"
    Then the page title should be "Reset your password | Mothercare Kuwait"
    And the url should match "/user/password"
    When I fill in "edit-name" with "shweta+2@axelerant.com"
    And I press "Submit"
    Then I should see "Further instructions have been sent to your email address."
    And the url should match "/user/login"
    And the page title should be "Sign in | Mothercare Kuwait"

  @arabic
  Scenario: As a Guest user
  I should be able to reset my password
  after providing valid Email ID

    Given I am on "/user/login"
    And I follow "عربية"
    And I follow "هل نسيت كلمة السر؟"
    Then the page title should be "Reset your password | مذركير الكويت"
    And the url should match "/user/password"
    When I fill in "edit-name" with "shweta+2@axelerant.com"
    And I press "إضافة"
    Then I should see "تم إرسال المزيد من التعليمات إلى عنوان بريدك الإلكتروني"
    And the url should match "/user/login"
    And the page title should be "تسجيل الدخول | مذركير الكويت"

  Scenario: As a Guest user
    An error message should be displayed
    when user tries to reset password for an invalid Email ID

    Given I am on "/user/login"
    And I follow "Forgot password?"
    When I fill in "edit-name" with "noemail@gmail.com"
    And I press "Submit"
    Then I should see " is not recognized as a username or an email address."

  @arabic
  Scenario: As a Guest user
  An error message should be displayed
  when user tries to reset password for an invalid Email ID

    Given I am on "/user/login"
    And I follow "عربية"
    And I follow "هل نسيت كلمة السر؟"
    When I fill in "edit-name" with "noemail@gmail.com"
    And I press "إضافة"
    Then I should see "كاسم مستخدم أو عنوان بريد إلكتروني "
    And I should see " لم يتم التعرف على"





