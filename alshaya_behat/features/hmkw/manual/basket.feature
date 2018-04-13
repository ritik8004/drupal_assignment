@javascript @manual @mmcpa-2325 @prod
Feature: Test basket page

  Background:
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
#    Given I am on a simple product page
#    And I wait for the page to load
#    When I press "Add to cart"
#    And I wait for AJAX to finish
    When I am on a configurable product
    And I wait for the page to load
    When I press "add to basket"
    And I wait for AJAX to finish

  Scenario:  As a Guest
    I should be able to see the products added to basket
    and the header and footer
    When I go to "/cart"
    And I wait for the page to load
#    Then I should see the link for simple product
    Then I should be able to see the header
    And I should see the link for configurable product
    Then I should see the button "checkout securely"
    And I should see "Basket ("
    Then I should see "Product"
    And I should see "Quantity"
    Then I should see "Unit Price"
    And I should see "Subtotal"
    Then I should see "Available delivery options"
    Then I should see "Order Total"
    And I should see "(Before Delivery)"
#    And I should see "(inclusive of VAT)"
    Then I should see the link "continue shopping" in ".edit-actions.form-actions.js-form-wrapper.form-wrapper" section
    And I should see "Add a promotional code"
#    And I should see "(all products inclusive of VAT)"
    And I should be able to see the footer
    When I click the label for "#edit-continue-shopping-mobile"
    And I wait for the page to load
    Then the url should match "/en"

  Scenario: As a Guest
    I should be able to add more quantity
    and remove products from the basket
    When I go to "/cart"
    And I wait for the page to load
    When I select 2 from dropdown
    And I wait for AJAX to finish
    Then I should see the price doubled for the product
    When I follow "Remove"
    And I wait for the page to load
    Then I should see "The product has been removed from your basket."

  Scenario: As a Guest
    I should be able to see tooltips
    for both Home Deliver and Click and Collect
    When I go to "/cart"
    And I wait for the page to load
    When I hover over tooltip "p.home-delivery.tooltip--head"
    And I wait 2 seconds
    Then I should see "Home delivery in 1-2 days. Free on orders over KWD 25."
    When I hover over tooltip "p.click-collect.tooltip--head"
    And I wait 2 seconds
    Then I should see "Collect the order in store from 1-2 days"

#  @loyalty
#  Scenario: As a Guest
#    I should be warned about privelege card number mismatch
#    When I go to "/cart"
#    And I wait for the page to load
#    When I click the label for "#details-privilege-card-wrapper > div"
#    Then I should see "Earn one draw entry for every KD 5 spent online"
#    When I fill in "privilege_card_number" with "0-0013-5844"
#    And I wait for AJAX to finish
#    And I fill in "privilege_card_number2" with "0-0013-5845"
#    And I wait for AJAX to finish
#    Then I should see "Specified PRIVILEGES CLUB card numbers do not match."

  @arabic
  Scenario: As a Guest on arabic site
  I should be able to see the products added to basket
  and the header and footer
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    Then I should be able to see the header in Arabic
#    Then I should see the link for simple product in Arabic
    And I should see the link for configurable product in Arabic
    Then I should see the button "إتمام الشراء بأمان"
    And I should see "حقيبة التسوق ("
    Then I should see "المنتج"
    And I should see "الكمية"
    Then I should see "سعر القطعة"
    And I should see "المبلغ"
    Then I should see "خيارات التوصيل"
    Then I should see "المبلغ الإجمالي للطلبية "
#    And I should see "(قبل التوصيل)"
    Then I should see the link "تابع التسوق" in ".edit-actions.form-actions.js-form-wrapper.form-wrapper" section
    And I should see "هل لديك رمز عرض ؟"
    And I should be able to see the footer in Arabic
    When I click the label for "#edit-continue-shopping-mobile"
    And I wait for the page to load
    Then the url should match "/ar"

  @arabic
  Scenario: As a Guest on arabic site
  I should be able to see tooltips
  for both Home Deliver and Click and Collect
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    When I hover over tooltip "p.home-delivery.tooltip--head"
    And I wait 2 seconds
    Then I should see ".خدمة التوصيل للمنازل خلال 1-2 أيام. التوصيل مجاني للطلبيات التي تزيد عن 25 د.ك."
    When I hover over tooltip "p.click-collect.tooltip--head"
    And I wait 2 seconds
    Then I should see "استلم طلبيتك من المحل خلال 1-2 أيام"


#  @arabic @loyalty
#  Scenario: As a Guest
#  I should be warned about privelege card number mismatch
#    When I go to "/cart"
#    And I wait for the page to load
#    When I follow "عربية"
#    And I wait for the page to load
#    When I click the label for "#details-privilege-card-wrapper > div"
#    Then I should see "إحصل على فرصة دخول السحب عند كل عملية شراء بقيمة 5 د.ك. عبر الموقع الالكتروني"
#    When I fill in "privilege_card_number" with "0-0013-5844"
#    And I wait for AJAX to finish
#    And I fill in "privilege_card_number2" with "0-0013-5845"
#    And I wait for AJAX to finish
#    Then I should see "أرقام نادي الامتيازات التي أدخلتها غير متطابقة."
