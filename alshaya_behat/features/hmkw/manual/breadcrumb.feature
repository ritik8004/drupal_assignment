@javascript @breadcrumb @manual @mmcpa-2382
Feature: Test breadcrumbs displayed across the site

  Scenario Outline: As a guest
    I should be able to view breadcrumbs across the site
    Given I am on "<page>"
    And I wait for the page to load
    Then the breadcrumb "<breadcrumb>" should be displayed
    Examples:
    |page|breadcrumb|
    |/ladies|home > Ladies|
    |/ladies/dresses|home > Ladies > Dresses|
    |/ladies/new-arrivals/clothes|home > Ladies > New Arrivals > Clothes|
    |/small-shoulder-bag|home > Ladies > Shop By Product > Accessories > Small Shoulder Bag|
    |/cart                                                 |home > Basket                                                                                            |
    |/store-finder                                         |home > Find Stores                                                                                       |
    |/ar/ملابس-الرضع                                       |الصفحة الرئيسية > ملابس الرضع                                                                            |
    |/ar/للأطفال-منذ-الولادة-وحتى-18-شهراً/ملابس-الرضع     |الصفحة الرئيسية > ملابس الرضع > للأطفال (منذ الولادة وحتى 18 شهراً)                                      |
    |/ar/جديدنا-من-ملابس-الأطفال/للأطفال-منذ-الولادة-وحتى-18-شهراً/ملابس-الرضع|الصفحة الرئيسية > ملابس الرضع > للأطفال (منذ الولادة وحتى 18 شهراً) > جديدنا من: ملابس الأطفال|
    |/ar/طقم-تي-شيرت-برسمة-disney-mickey-mouse-وشورت-جينز                     |الصفحة الرئيسية > ملابس الرضع > للأطفال (منذ الولادة وحتى 18 شهراً) > للأولاد > طقم تي-شيرت برسمة disney mickey mouse وشورت جينز|
    |/ar/vk-promo-001                                                         |الصفحة الرئيسية > vk promo 001 < toys                                                                                                  |
    |/ar/cart                                                                 |الرئيسية > حقيبة التسوق                                                                                                         |
    |/ar/store-finder                                                         |الصفحة الرئيسية > البحث عن المحلات                                                                                              |

  Scenario: As a Guest
    I should be able to view breadcrumb on store detail page
    Given I am on "/store-finder"
    And I wait for the page to load
    When I follow "M.H. Alshaya Building"
    And I wait for the page to load
    Then the breadcrumb "home > find stores > m.h. alshaya building" should be displayed
    
  @arabic
  Scenario: As a Guest on Arabic site
  I should be able to view breadcrumb on store detail page
    Given I am on "/ar/store-finder"
    And I wait for the page to load
    When I follow "سوق شرق"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > البحث عن المحلات > سوق شرق" should be displayed

  Scenario: As an authenticated user
    I should be able to view breadcrumbs on My account section
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
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
    Then the breadcrumb "home > my account > communication preferences" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(6) > a"
    And I wait for the page to load
    Then the breadcrumb "home > my account > change password" should be displayed

  @arabic
  Scenario: As an authenticated user on Arabic site
  I should be able to view breadcrumbs on My account section
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
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
    Then the breadcrumb "الصفحة الرئيسية > حسابي > تفضيلات التواصل" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(6) > a"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > حسابي > تغيير كلمة السر" should be displayed
