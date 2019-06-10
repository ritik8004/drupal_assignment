@manual @mmcpa-2388 @javascript @prod
Feature: Test Sign in and Forgot password features

  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I go to "/user/login"
    When I fill in "edit-name" with "kanchan.patil+uat@qed42.com"
    And I fill in "edit-pass" with "Password@1"
    And I press "sign in"
    Then I should see the link "My account"
    And I should see the link "Sign out"
    And I should see "recent orders"

  @arabic
  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials
    Given I go to "/user/login"
    And I follow "عربية"
    When I fill in "edit-name" with "kanchan.patil+uat@qed42.com"
    And I fill in "edit-pass" with "Password@1"
    And I press "تسجيل الدخول"
    Then I should see the link "حسابي"
    And I should see the link "تسجيل الخروج"
    And I should see "أحدث الطلبيات"

  Scenario: As a Guest user
  I should be prompted with warning messages
  when I try to sign in without submitting any credentials
    Given I go to "user/login"
    When I press "sign in"
    And I wait for AJAX to finish
    Then I should see "Please enter your Email address."
    And I should see "Please enter your Password."

  @arabic
  Scenario: As a Guest user
  I should be prompted with warning messages
  when I try to sign in without submitting any credentials
    Given I go to "user/login"
    And I follow "عربية"
    When I press "تسجيل الدخول"
    And I wait for AJAX to finish
    Then I should see "يرجى إدخال عنوان البريد الإلكتروني"
    And I should see "يرجى إدخال كلمة السر"

  Scenario: As a Guest user
  I should be shown error messages
  when I try to login using invalid email ID and password
    Given I am on "/user/login"
    When I fill in "edit-name" with "name@surname@gmail.com"
    And I press "sign in"
    And I wait 2 seconds
    Then I should see text matching "Email address does not contain a valid email."

  @arabic
  Scenario: As a Guest user
  I should be shown error messages
  when I try to login using invalid email ID and password
    Given I am on "/user/login"
    And I follow "عربية"
    When I fill in "edit-name" with "name@surname@gmail.com"
    And I press "تسجيل الدخول"
    And I wait 2 seconds
    Then I should see text matching "عنوان البريد الإلكتروني لا يشمل عنوان بريد إلكتروني صحيح"

  Scenario: As a Guest user
    I should be able to reset my password
    after providing valid Email ID
    Given I am on "/user/login"
    And I follow "Forgot password?"
    And the url should match "/user/password"
    When I fill in "edit-name" with "kanchan.patil+uat@qed42.com"
    And I press "Submit"
    Then I should see "Further instructions have been sent to your email address."
    And the url should match "/user/login"

  @arabic
  Scenario: As a Guest user
  I should be able to reset my password
  after providing valid Email ID
    Given I am on "/user/login"
    And I follow "عربية"
    And I follow "هل نسيت كلمة السر؟"
    And the url should match "/user/password"
    When I fill in "edit-name" with "kanchan.patil+uat@qed42.com"
    And I press "إرسال"
    Then I should see "تم إرسال المزيد من التعليمات إلى عنوان بريدك الإلكتروني"
    And the url should match "/user/login"

  Scenario: As a Guest user
    An error message should be displayed
    when user tries to reset password for an invalid Email ID
    Given I am on "/user/login"
    And I follow "Forgot password?"
    When I fill in "edit-name" with "noemail@gmail.com"
    And I press "Submit"
    When I wait for the page to load
    Then I should see " is not recognized as a username or an email address."

  @arabic
  Scenario: As a Guest user
  An error message should be displayed
  when user tries to reset password for an invalid Email ID
    Given I am on "/user/login"
    And I follow "عربية"
    And I follow "هل نسيت كلمة السر؟"
    When I fill in "edit-name" with "noemail@gmail.com"
    And I press "إرسال"
    Then I should see "كاسم مستخدم أو عنوان بريد إلكتروني "
    And I should see " لم يتم التعرف على"
