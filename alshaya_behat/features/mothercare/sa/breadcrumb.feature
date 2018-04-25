@javascript
Feature: Test breadcrumbs displayed across the site

  Scenario Outline: As a guest
    I should be able to view breadcrumbs across the site
    Given I am on "<page>"
    And I wait for the page to load
    Then the breadcrumb "<breadcrumb>" should be displayed
    Examples:
    |page|breadcrumb|
    |/baby-clothing-0|home > baby clothing|
    |/baby-clothing/baby-newborn-18-months|home > baby clothing > baby (newborn - 18m)|
    |/baby-clothing/baby-newborn-18-months/bodysuits|home > baby clothing > baby (newborn - 18m) > bodysuits|
    |/my-first-sleepsuits-3-pack           |home > baby clothing > baby (newborn - 18m) > sleepsuits & pyjamas > my first sleepsuits - 3 pack|
    |/cart                                                 |home > basket                                                                                            |
    |/store-finder                                         |home > find stores                                                                                       |

  Scenario:  As a guest on Arabic site
  I should be able to view breadcrumbs across the site
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I wait for the page to load
    When I follow "ملابس الرضع"
    And I wait for the page to load
    When I follow "صندوق هدية جوارب للأولاد"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > ملابس الرضع > الأطفال (منذ الولادة وحتى 18 شهراً) > الجوارب والكولونات > صندوق هدية جوارب للأولاد" should be displayed
    When I click "الجوارب والكولونات" "link" in the region ".breadcrumb"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > ملابس الرضع > الأطفال (منذ الولادة وحتى 18 شهراً) > الجوارب والكولونات" should be displayed
    When I click "الأطفال (منذ الولادة وحتى 18 شهراً)" "link" in the region ".breadcrumb"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > ملابس الرضع > الأطفال (منذ الولادة وحتى 18 شهراً)" should be displayed
    When I click "ملابس الرضع" "link" in the region ".breadcrumb"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > ملابس الرضع" should be displayed
    When I click the label for ".cart-link"
    And I wait for the page to load
    Then the breadcrumb "الرئيسية > سلة التسوق" should be displayed
    When I follow "البحث عن محلاتنا"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > البحث عن المحلات" should be displayed

  Scenario: As a Guest
    I should be able to view breadcrumb on store detail page
    Given I am on "/store-finder"
    And I wait for the page to load
    When I follow "977 Nadah Plaza"
    And I wait for the page to load
    Then the breadcrumb "home > find stores > 977 nadah plaza" should be displayed

  Scenario: As a Guest on Arabic site
  I should be able to view breadcrumb on store detail page
    Given I am on "/store-finder"
    And I wait for the page to load
    When I follow "العربية"
    And I wait for the page to load
    When I follow "سوق شرق"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > البحث عن المحلات > سوق شرق" should be displayed

  Scenario: As an authenticated user
    I should be able to view breadcrumbs on My account section
    Given I am logged in as an authenticated user "trupti@axelerant.com" with password "password@1"
    And I wait for the page to load
    Then the breadcrumb "home > my account" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(2) > a"
    And I wait for the page to load
    Then the breadcrumb "home > my account > orders" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(3) > a"
    And I wait for the page to load
    Then the breadcrumb "home > my account > contact details" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    Then the breadcrumb "home > my account > address book" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(5) > a"
    And I wait for the page to load
    Then the breadcrumb "home > my account > change password" should be displayed

  Scenario: As an authenticated user on Arabic site
  I should be able to view breadcrumbs on My account section
    Given I am logged in as an authenticated user "trupti@axelerant.com" with password "password@1"
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