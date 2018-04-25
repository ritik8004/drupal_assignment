@javascript
Feature: to verify search functionality , basket and checkout

  Scenario: On Arabic site as a Guest user
  I should be able to search, verify filter,footer, header and sort on results page
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I follow "العربية"
    And I wait for the page to load
    When I fill in "edit-keywords" with "تي-شيرت برسمة"
    And I press "Search"
    And I wait for the page to load
    Then I should see "تي-شيرت برسمة"
    When I click the label for "#block-content > div > div > ul > li > a"
    And I wait for AJAX to finish
    Then more items should get loaded
    Then I should see "حدّد اختيارك"
    Then I should see "اللون"
    And I should see "السعر"
    Then I should see "المقاس"
    And I should be able to see the header in Arabic
    And I should be able to see the footer in Arabic

  Scenario: On arabic site as an authenticated user
  I should be able to search product, add product to basket
  and verify the fields on basket
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I follow "عربية"
    When I wait for the page to load
    When I fill in "edit-keywords" with "تي-شيرت برسمة"
    And I press "Search"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I click the label for ".cart-link"
    And I wait for the page to load
    Then I should see "المنتج"
    And I should see "الكمية"
    Then I should see "سعر القطعة"
    And I should see "المبلغ"
    Then I should see "خيارات التوصيل"
    Then I should see "المبلغ الإجمالي للطلبية "
    And I should be able to see the footer in Arabic
#    And I should be able to see the header in Arabic
    When I click the label for "#edit-continue-shopping-mobile"
    And I wait for the page to load
    Then the url should match "/ar"

  Scenario: On Arabic site as an authenticated user
  I should be able to search for a product
  and add it to the cart, select Home Delivery and see COD, KNET and Cybersource
  Payment methods
    Given I am logged in as an authenticated user "anjali.nikumb@acquia.com" with password "password@1"
    And I wait for the page to load
    When I follow "عربية"
    When I wait for the page to load
    When I fill in "edit-keywords" with "تي-شيرت برسمة"
    And I press "Search"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    When I click the label for ".cart-link"
    And I wait for the page to load
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "خدمة التوصيل للمنزل"
    And I wait for the page to load
    When I select address for Arabic
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for the page to load
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    Then I should see "أؤكد أنني قرأت وفهمت"
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    Then I should see "أؤكد أنني قرأت وفهمت"
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
#    When I accept terms and conditions
#    And I press "سجل الطلبية"
#    When I wait for the page to load
#    And I press "CancelAction_id"


  Scenario: On arabic site as an authenticated user
  I should be able to search for a product
  and add it to the cart, select Click & Collect and see  KNET and Cybersource
  Payment methods
    Given I am logged in as an authenticated user "anjali.nikumb@acquia.com" with password "password@1"
    And I wait for the page to load
    When I follow "عربية"
    When I wait for the page to load
    When I fill in "edit-keywords" with "تي-شيرت برسمة"
    And I press "Search"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I click the label for ".cart-link"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "اختر واستلم"
    And I wait for AJAX to finish
    When I select a store on arabic
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I fill in "edit-billing-address-address-billing-given-name" with "شويتا"
    And I fill in "edit-billing-address-address-billing-family-name" with "شارما"
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
    And I select "العباسية" from "edit-billing-address-address-billing-administrative-area"
    When I fill in "edit-billing-address-address-billing-locality" with "كتلة A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "الشارع ب"
    When I fill in "edit-billing-address-address-billing-dependent-locality" with "بناء C"
    Then I should see "أؤكد أنني قرأت وفهمت"
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    When I fill in "edit-billing-address-address-billing-given-name" with "Test"
    And I fill in "edit-billing-address-address-billing-family-name" with "Test"
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
    And I select "العباسية" from "edit-billing-address-address-billing-administrative-area"
    When I fill in "edit-billing-address-address-billing-locality" with "كتلة A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "الشارع ب"
    When I fill in "edit-billing-address-address-billing-dependent-locality" with "بناء C"
#    When I accept terms and conditions
##    And I wait for the page to load
#    When I press "سجل الطلبية"
#    And I wait for the page to load
#    And I press "CancelAction_id"
