@javascript @breadcrumb @manual @mmcpa-2382 @prod
Feature: Test breadcrumbs displayed across the site

  Scenario Outline: As a guest
    I should be able to view breadcrumbs across the site
    Given I am on "<page>"
    And I wait for the page to load
    Then the breadcrumb "<breadcrumb>" should be displayed
    Examples:
    |page|breadcrumb|
    |/ladies|Home > Ladies|
    |/ladies/new-arrivals/clothes|Home > Ladies > New Arrivals > Clothes|
    |/shoulder-bag-26|Home > Ladies > Shop By Product > Accessories > Shoulder Bag|
    |/cart                                                 |Home > Basket                                                                                            |
    |/store-finder                                         |Home > Find Stores                                                                                       |

  @arabic
  Scenario: As a guest on Arabic site
    I should be able to view breadcrumbs across the site
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
#    When I follow "عربية"
    And I wait for the page to load
    When I follow "النساء"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > النساء" should be displayed
#    When I follow "الملابس"
#    And I wait for the page to load
#    Then the breadcrumb "الصفحة الرئيسية > للنساء > شسيبلاتنمثقفصض > الملابس" should be displayed
#    When I follow "جاكيت بقبعة"
#    And I wait for the page to load
#    Then the breadcrumb "الصفحة الرئيسية > للنساء > شسيبلاتنمثقفصض > الملابس > جاكيت بقبعة" should be displayed
    When I click the label for ".cart-link"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > حقيبة التسوق" should be displayed
    When I follow "البحث عن محلاتنا"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > البحث عن المحلات" should be displayed

  Scenario: As a Guest
    I should be able to view breadcrumb on store detail page
    Given I am on "/store-finder"
    And I wait for the page to load
    When I follow "Avenues Family"
    And I wait for the page to load
    Then the breadcrumb "Home > Find Stores > Avenues Family" should be displayed
    
  @arabic,
  Scenario: As a Guest on Arabic site
  I should be able to view breadcrumb on store detail page
    Given I am on "/store-finder"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    When I follow "اتش آند ام غراند أفنيوز"
    And I wait for the page to load
    Then the breadcrumb "الصفحة الرئيسية > البحث عن المحلات > اتش آند ام غراند أفنيوز" should be displayed

  Scenario: As an authenticated user
    I should be able to view breadcrumbs on My Account section
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    Then the breadcrumb "Home > My Account" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(2) > a"
    And I wait for the page to load
    Then the breadcrumb "Home > My Account > Orders" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(3) > a"
    And I wait for the page to load
    Then the breadcrumb "Home > My Account > Contact Details" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(4) > a"
    And I wait for the page to load
    Then the breadcrumb "Home > My Account > Address Book" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(5) > a"
    And I wait for the page to load
    Then the breadcrumb "Home > My Account > Communication Preferences" should be displayed
    When I click the label for "#block-alshayamyaccountlinks > div > ul > li:nth-child(6) > a"
    And I wait for the page to load
    Then the breadcrumb "Home > My Account > Change Password" should be displayed

  @arabic
  Scenario: As an authenticated user on Arabic site
  I should be able to view breadcrumbs on My Account section
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
