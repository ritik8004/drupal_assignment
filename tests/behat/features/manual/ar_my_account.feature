@manual @javascript @arabic @mmcpa-2178
Feature: Test the My account section for authenticated user

  Background:
    Given I am logged in as an authenticated user "shweta+2@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load

  Scenario:
    As an authenticated user
    I should be able to see all the sections
    after logging in
    Then I should see the link "حسابي" in ".my-account-nav" section
    And I should see the link "الطلبيات" in ".my-account-nav" section
    Then I should see the link "تفاصيل الاتصال" in ".my-account-nav" section
    And I should see the link "سجل العناوين" in ".my-account-nav" section
    Then I should see the link "تفضيلات التواصل" in ".my-account-nav" section
    And I should see the link "تغيير كلمة السر" in ".my-account-nav" section
    And I should see the link "عرض كل الطلبيات"

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
    And the url should match "<url>"

    Examples:
      |link|text|url|
      |خدمة الزبائ|Contact us|/contact|
      |سياسية الاسترجاع اونلاين وفي المحلات|Contact us|/contact|
      |معلومات التوصيل|Contact us|/contact|

  Scenario:
  As an authenticated user
  I should be able to view all my orders
  from my account page
    When I follow "عرض كل الطلبيات"
    And I wait for the page to load
    Then the "الطلبيات" tab should be selected
    And I should see "أحدث الطلبيات"
    Then the url should match "/orders"
    And I should see text matching "هل تحتاج إلى مساعدة في طلبيتك؟"
    Then I should see the link "خدمة الزبائن"
    Then I should see the link "سياسية الاسترجاع اونلاين وفي المحلات"
    And I should see the link "معلومات التوصيل"

  Scenario: As an authenticated user
  I should be prompted to join the privilege club
  if I don't have a privilege account
    When I follow "تعديل معلومات الحساب"
    And I wait for the page to load
    When I fill in "edit-field-privilege-card-number-0-value" with ""
    And I press "حفظ"
    And I wait for the page to load
    Then I should see "تم حفظ التغييرات."
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li.my-account > a"
    And I wait for the page to load
    Then I should see "نادي الامتيازات"
    And I should see "اربح جوائز مدهشة"
    Then I should see "احصل على مكافآت حصرية"
    And I should see "كُن أول من يعلم"
    Then I should see the link "مزيد من المعلومات"
    And I should not see "رقم بطاقة نادي الامتيازات"

  Scenario: As an authenticated user
  account details section should display Privilege card number
  along with Email address and Contact number
    When I follow "تعديل معلومات الحساب"
    And I wait for the page to load
    When I fill in "edit-field-privilege-card-number-0-value" with "000135844"
    And I press "حفظ"
    And I wait for the page to load
    Then I should see "تم حفظ التغييرات."
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li.my-account > a"
    And I wait for the page to load
    Then I should see "shweta+2@axelerant.com"
    And I should see "+965 - 9700 - 4455"
    Then I should see "6362 - 5440 - 0013 - 5844"
    And I should not see "اربح جوائز مدهشة"
    Then I should not see "احصل على مكافآت حصرية"
    And I should not see "كُن أول من يعلم"
    Then I should not see the link "مزيد من المعلومات"
    And I should see "رقم بطاقة نادي الامتيازات"

  Scenario: As an authenticated user
  I should be able to filter the listed orders
  by ID, name, SKU in combination with the Status of the order
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(2) > a"
    And I wait for the page to load
    When I fill in "edit-search" with "Arb. انت"
    When I click the label for "#edit-submit-orders"
    And I wait for the page to load
    Then I should see all "Arb. انت" orders
    When I fill in "edit-search" with "MCKWRCE"
    And I wait 2 seconds
    When I click the label for "#edit-submit-orders"
    And I wait for the page to load
    And I should see all orders for "MCKWRCE"

  Scenario: As an authenticated user
  I should be able to filter on all cancelled, dispatched and processing orders
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(2) > a"
    And I wait for the page to load
    When I select Cancelled from the status dropdown
    And I wait for the page to load
    Then I should see all "المعالجة" orders listed on orders tab
    When I select Dispatched from the status dropdown
    And I wait for the page to load
    Then I should see all "تم الإلغاء" orders listed on orders tab
    When I select Processing from the status dropdown
    And I wait for the page to load
    Then I should see all "قيد التوصيل " orders listed on orders tab

  Scenario: As an authenticated user
  I should be able to update my contact details
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(3) > a"
    And I wait for the page to load
    When I fill in "edit-field-first-name-0-value" with "علية"
    When I fill in "edit-field-last-name-0-value" with "خان"
    When I fill in "edit-field-mobile-number-0-mobile" with "55774438"
    And I press "حفظ"
    And I wait for the page to load
    Then I should see "علية"
    And I should see "خان"
    And I should not see "Shweta"
    And I should not see "Sharma"
    Then I fill in "edit-field-first-name-0-value" with "Shweta"
    And I fill in "edit-field-last-name-0-value" with "Sharma"
    Then I fill in "edit-field-mobile-number-0-mobile" with "97004455"
    And I press "حفظ"

  Scenario: As an authenticated user
  I should be able to add a new address
  to my address book
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    Then I get the total count of address blocks
    When I follow "إضافة العنوان جديد"
    And I wait for AJAX to finish
    When I fill in "field_address[0][address][given_name]" with "علية"
    And I fill in "field_address[0][address][family_name]" with "خان"
    When I fill in "field_address[0][address][mobile_number][mobile]" with "97004455"
    When I select a value from Area dropdown
    When I fill in "field_address[0][address][locality]" with "بلوك A"
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
  I should be able to set my communication preferences
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(5) > a"
    And I wait for the page to load
    Then I should see "تفضيلات التواصل"
    And I should see "يرجى اختيار وسيلة التواصل المناسبة "
    When I check the ".form-item-communication-preference-email > label" checkbox
    When I check the ".form-item-communication-preference-mobile > label" checkbox
    And I press "حفظ"
    When I wait for the page to load
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
    Then I should see text matching "لم يتم قبول المحاولات الأربعة السابقة"
    When I press "تغيير كلمة السر"
    Then I should see "يرجى إدخال كلمة السر الحالية"
    And I should see "يرجى إدخال كلمة السر الجديدة"
