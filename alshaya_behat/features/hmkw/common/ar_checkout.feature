@javascript @checkout @arabic @ar_checkout @mmcpa-1930 @manual
Feature: Test various checkout scenarios for Arabic site

  Background:
    Given I am on a configurable product
    And I wait for the page to load
    When I press "add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I wait for the page to load

  @hd @cod
  Scenario: As a Guest on Arabic site
  I should be able to checkout using COD
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    When I select "العباسية" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
    And I press "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    And I accept terms and conditions
    When I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    Then I should see "رقم طلبيتك هو"

  @hd @knet
  Scenario: As a Guest,
  I should be able to checkout using KNET
  I should be able to checkout using CD
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    When I select "العباسية" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
    And I press "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    And I wait for the page to load
    And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect"
    And I fill in an element having class ".paymentinput" with "0000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin_id" with "1234"
    And I press "إرسال"
    And I press "تأكيد العملية"
    And I wait 10 seconds
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    Then I should see "رقم طلبيتك هو"

  @cc @knet
  Scenario:  As a Guest
  I should be able to use click and collect option
  and pay by KNET
    When I follow "اختر واستلم"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "اختر هذا المحل"
    And I wait for AJAX to finish
    When I fill in "edit-cc-firstname" with "Test"
    And I fill in "edit-cc-lastname" with "Test"
    When I enter a valid Email ID in field "edit-cc-email"
    And I fill in "edit-cc-mobile-number-mobile" with "55004455"
    When I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    When I fill in "edit-billing-address-address-billing-given-name" with "Test"
    And I fill in "edit-billing-address-address-billing-family-name" with "Test"
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
    And I select "العباسية" from "edit-billing-address-address-billing-administrative-area"
    When I fill in "edit-billing-address-address-billing-locality" with "كتلة A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "الشارع ب"
    When I fill in "edit-billing-address-address-billing-dependent-locality" with "بناء C"
    When I fill in "edit-billing-address-address-billing-address-line2" with "2"
    When I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    And I wait for the page to load
    And I select "Knet Test Card [KNET1]" from dropdown ".paymentselect"
    And I fill in an element having class ".paymentinput" with "0000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin" with "1234"
    And I press "إرسال"
    And I press "تأكيد العملية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    Then I should see "رقم طلبيتك هو"

  @knet
  Scenario: As a Guest
  I should be directed to basket page on cancelling a KNET transaction
  and User should be able to place order again
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    When I select "العباسية" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
    And I press "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    And I wait for the page to load
    When I press "الغاء"
    And I wait for the page to load
    Then I should see "عذراً، لم نتمكن من متابعة تنفيذ طلبك. يرجى إعادة المحاولة او الاتصال بخدمة الزبائن للمساعدة."
    And the url should match "/ar/cart/checkout/payment"

  @cc
  Scenario: As a Guest
  I should be navigated to basket page
  On clicking 'back to basket' from checkout CC page
    When I follow "اختر واستلم"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "العودة إلى حقيبة التسوق"
    And I wait for the page to load
    Then the url should match "/ar/cart"
    And I should see the button "إتمام الشراء بأمان"

  @cc
  Scenario: As a Guest
  I should be able to view the number of results displayed
  Buttons to toggle between list and Map view
  and link to navigate to the basket
    When I follow "اختر واستلم"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then I should see the number of stores displayed
    And I should see the link "عرض القائمة"
    And I should see the link "عرض الخريطة"
    And I should see the link "العودة إلى حقيبة التسوق"

  @hd @cs
  Scenario: As a Guest
    I should be able to checkout using HD
    and Cybersource payment option on Arabic site
    When I follow "خدمة التوصيل للمنزل"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    When I select "العباسية" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
    And I press "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I accept terms and conditions
    When I press "سجل الطلبية"
    When I wait 10 seconds
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    Then I should see "رقم طلبيتك هو"

  @cc @cs
  Scenario: As a Guest
    I should be able to checkout using Click and Collect
    and Cybersource payment option on Arabic site
    When I follow "اختر واستلم"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 10 seconds
    When I follow "اختر هذا المحل"
    And I wait for AJAX to finish
    When I fill in "edit-cc-firstname" with "Test"
    And I fill in "edit-cc-lastname" with "Test"
    When I enter a valid Email ID in field "edit-cc-email"
    And I fill in "edit-cc-mobile-number-mobile" with "55004455"
    When I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I fill in "edit-billing-address-address-billing-given-name" with "Test"
    And I fill in "edit-billing-address-address-billing-family-name" with "Test"
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
    And I select "العباسية" from "edit-billing-address-address-billing-administrative-area"
    When I fill in "edit-billing-address-address-billing-locality" with "كتلة A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "الشارع ب"
    When I fill in "edit-billing-address-address-billing-dependent-locality" with "بناء C"
    When I accept terms and conditions
    When I wait for the page to load
    When I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    Then I should see "رقم طلبيتك هو"

  Scenario: As a Guest user on Arabic site
  I should be able to see order summary, back to basket option
  and the customer service block
    When I follow "خدمة التوصيل للمنزل"
    And I wait for the page to load
    Then I should see " التوصيل العادي للطلبيات التي تزيد على 250 د.ك"
    Then I should see the Order Summary block
    And I should see the Customer Service block
    When I follow "تعديل"
    And I wait for the page to load
    Then the url should match "/ar/cart"
    And I should see the button "إتمام الشراء بأمان"

  @cc
  Scenario: As a Guest user
  whenever I click 'back to basket' link on Map view
  I should be redirected to the basket page
    When I follow "الاستلام من محلاتنا"
    And I wait for the page to load
    When I select the first autocomplete option for "shuwaikh" on the "edit-store-location" field
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I follow "عرض الخريطة"
    Then the "عرض الخريطة" tab should be selected
    When I click the label for "#click-and-collect-map-view > div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(4) > div:nth-child(3) > div:nth-child(4) > img"
    When I wait 5 seconds
    When I click the label for "#click-and-collect-map-view > div.geolocation-common-map-container > div > div > div:nth-child(1) > div:nth-child(4) > div:nth-child(4) > div > div.gm-style-iw > div:nth-child(1) > div > div > div.store-open-hours > div > div.hours--label"
    And I wait 2 seconds
    Then I should see "الإثنين"
    And I should see "الأحد"
    When I follow "العودة إلى حقيبة التسوق"
    Then I should see the button "إتمام الشراء بأمان"
    And the url should match "/ar/cart"

  @tc
  Scenario: As a Guest on Arabic site
  I should see the error message when terms and condition unchecked
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    When I select "العباسية" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
    And I press "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
 # By default terms and condition is unchecked.
    When I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "يرجى الموافقة على سياسة الخصوصية و شروط وأحكام الشراء"