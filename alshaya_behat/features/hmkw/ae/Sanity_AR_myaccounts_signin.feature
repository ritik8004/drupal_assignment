@javascript
Feature: To verify my accounts functionality
  for arabic site

  Background:
    Given I am logged in as an authenticated user "trupti@axelerant.com" with password "password@1"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load

  Scenario:
  As an authenticated user
  I should be able to see all the sections
  after logging in
    Given I should see the link "حسابي" in ".my-account-nav" section
    And I should see the link "الطلبيات" in ".my-account-nav" section
    Then I should see the link "تفاصيل الاتصال" in ".my-account-nav" section
    And I should see the link "سجل العناوين" in ".my-account-nav" section
    And I should see the link "تغيير كلمة السر" in ".my-account-nav" section


  Scenario Outline:
  As an authenticated user
  I should be able to view the Need help section
  and access the links under Need help
    When I see the text "هل تحتاج إلى مساعدة في طلبيتك؟"
    Then I should see the link "خدمة الزبائن"
    Then I should see the link "سياسية الاسترجاع اونلاين وفي المحلات"
    And I should see the link "معلومات التوصيل"
    When I follow "<link>"
    And I wait for the page to load
    Then I should see "<text>"
  Examples:
    |link|text|
    |خدمة الزبائ|اتصل بنا|
    |سياسية الاسترجاع اونلاين وفي المحلات|شروط وأحكام الشراء|
    |معلومات التوصيل|معلومات التوصيل|


  Scenario: As an authenticated user
  I should be able to update my contact details
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(3) > a"
    And I wait for the page to load
    When I fill in "edit-field-first-name-0-value" with "علية"
    When I fill in "edit-field-last-name-0-value" with "خان"
    When I fill in "edit-field-mobile-number-0-mobile" with "555004466"
    And I press "حفظ"
    And I wait for the page to load
    Then I should see "تم حفظ ببيانات الاتصال"
    Then I should see "علية"
    And I should see "خان"
    Then I fill in "edit-field-first-name-0-value" with "Test"
    And I fill in "edit-field-last-name-0-value" with "Test"
    Then I fill in "edit-field-mobile-number-0-mobile" with "555004455"
    And I press "حفظ"

  Scenario: As an authenticated user
  I should be able to add a new address
  to my address book
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    Then I get the total count of address blocks
    When I follow "إضافة العنوان جديد"
    And I wait for AJAX to finish
    When I fill in "field_address[0][address][given_name]" with "Test"
    And I fill in "field_address[0][address][family_name]" with "Test"
    When I fill in "field_address[0][address][mobile_number][mobile]" with "555004455"
    When I select "أبو ظبي" from "field_address[0][address][area_parent]"
    And I wait for AJAX to finish
    And I select "نادي ومنتجع أبوظبي للغولف" from "field_address[0][address][administrative_area]"
    When I fill in "field_address[0][address][address_line1]" with "شارع B"
    When I fill in "field_address[0][address][dependent_locality]" with "شقة سانيوجيتا"
    When I fill in "field_address[0][address][address_line2]" with "5"
    And I press "أضف عنواناً"
    When I wait for AJAX to finish
    And I wait for the page to load
    Then I should see "تم إضافة العنوان بنجاح"
    And the new address block should be displayed on address book

  Scenario: As an authenticated user
  I should be able to perform Cancel action on add/edit address pages
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    When I follow "إضافة العنوان جديد"
    And I wait for AJAX to finish
    When I follow "إلغاء"
    And I wait for the page to load
    Then I should not see the text "الاسم الأول"
    When I click Edit Address
    And I wait for AJAX to finish
    When I follow "إلغاء"
    And I wait for the page to load
    Then I should not see the text "الاسم الأول"

  Scenario: As an authenticated user
  I should be able to edit an address
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    When I click Edit Address
    And I wait for AJAX to finish
    When I fill in "field_address[0][address][address_line2]" with "2"
    And I press "حفظ"
    When I wait for the page to load
    Then I should see "تم تحديث العنوان بنجاح"

  Scenario: As an authenticated user
  I should not be able to delete my primary address
  but I should be able to delete any other address
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    Then I should not see the delete button for primary address
    When I follow "حذف"
    And I wait for AJAX to finish
    When I press "الرجوع إلى الصفحة السابقة"
    Then I should see "سجل العناوين "
    Then I get the total count of address blocks
    When I follow "حذف"
    And I wait for AJAX to finish
    When I confirm deletion of address
    And I wait for AJAX to finish
    Then I should see "تم حذف العنوان بنجاح"
    And the address block should be deleted from address book

  Scenario: As an authenticated user
  I should be able to set my communication preferences on Arabic site
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(5) > a"
    And I wait for the page to load
    When I check "البريد الإلكتروني "
    And I press "حفظ"
    And I wait for the page to load
    Then I should see "تم حفظ تفضيلات الاتصال بنجاح."

  Scenario: As an authenticated user
  I should see the options to change my password
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(6) > a"
    And I wait for the page to load
    Then I should see "تغيير كلمة السر"
    Then I should see "كلمة السر الحالية"
    And I should see "كلمة السر الجديدة"
    Then I should see the button "تغيير كلمة السر"
    When I fill in "edit-pass" with ""
    And I wait 2 seconds
    Then I should see text matching "يجب أن تكون كلمة السر الخاصة بك مكونة من سبعة عناصر على الأقل"
    Then I should see text matching "يجب أن تتضمن كلمة السر الخاصة بك رمزاً واحداً على الأقل"
    Then I should see text matching "يجب أن تتضمن كلمة السر الخاصة بك رقماً واحداً على الأقل"
    Then I should see text matching "يجب أن لا تحتوي كلمة السر الخاصة بك على مسافات"
    Then I should see text matching "كلمات السر الأربع السابقة غير مسموح بها"
    When I press "تغيير كلمة السر"
    Then I should see "يرجى إدخال كلمة السر الحالية"
    And I should see "يرجى إدخال كلمة السر الجديدة"
