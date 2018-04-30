@javascript
Feature: To verify Signin and Breadcrumbs
  for arabic site

  Scenario: As an authenticated user,
  I should be able to sign in after providing valid credentials and
  reset my password
    Given I go to "/user/login"
    And I follow "عربية"
    When I fill in "edit-name" with "anjali.nikumb@acquia.com"
    And I fill in "edit-pass" with "password@1"
    And I press "تسجيل الدخول"
    Then I should see the link "حسابي"
    And I should see the link "تسجيل الخروج"
    And I should see "أحدث الطلبيات"
    When I follow "تسجيل الخروج"
    And I wait for AJAX to finish
    Then I should see "تسجيل الدخول"
    And the url should match "/user/login"
    And I follow "هل نسيت كلمة السر؟"
    And the url should match "/user/password"
    When I fill in "edit-name" with "anjali.nikumb@acquia.com"
    And I press "إرسال"
    Then I should see "تم إرسال المزيد من التعليمات إلى عنوان بريدك الإلكتروني"
    And the url should match "/user/login"


  Scenario: As a guest user,
  I should not be able to sign in or reset password
  with invalid credentials
    Given I go to "user/login"
    And I follow "عربية"
    When I press "تسجيل الدخول"
    And I wait for AJAX to finish
    Then I should see "يرجى إدخال عنوان البريد الإلكتروني"
    And I should see "يرجى إدخال كلمة السر"
    And I follow "هل نسيت كلمة السر؟"
    When I fill in "edit-name" with "noemail@gmail.com"
    And I press "إرسال"
    Then I should see "كاسم مستخدم أو عنوان بريد إلكتروني "
    And I should see " لم يتم التعرف على"


  Scenario: As an authenticated user on Arabic site
  I should be able to view breadcrumbs on My account section
    Given I am logged in as an authenticated user "anjali.nikumb@acquia.com" with password "password@1"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > حسابي" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(2) > a"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > حسابي > الطلبيات" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(3) > a"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > حسابي > تفاصيل الاتصال" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > حسابي > سجل العناوين" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(5) > a"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > حسابي > تغيير كلمة السر" should be displayed
