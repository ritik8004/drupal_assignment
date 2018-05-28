@javascript @checkout @arabic @manual @ar_checkout @mmcpa-1930
Feature: As an authenticated user
  I should be able to checkout
  using various payment options on Arabic site

  Background:
    When I am logged in as an authenticated user "trupti@axelerant.com" with password "password@1"
    And I wait for the page to load
    Then I should see the link "My account"
    Given I am on a configurable product
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I follow "العربية"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load

  @hd @cod
  Scenario: As an authenticated user
  I should be able to checkout using Home delivery
  and pay by Cash-on-delivery on Arabic site
    When I follow "خدمة التوصيل للمنزل"
    And I wait for AJAX to finish
    When I follow "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    When I accept terms and conditions
    And I press "سجل الطلبية"
    When I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see "رقم طلبيتك هو"

  @hd @knet
  Scenario: As an authenticated user
    I should be able to checkout using Home delivery
    and pay by KNET on Arabic site
    When I follow "خدمة التوصيل للمنزل"
    And I wait for AJAX to finish
    When I follow "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    When I accept terms and conditions
    And I press "سجل الطلبية"
    When I wait for the page to load
    And I select "Knet Test Card [KNET1]" from "bank"
    When I fill in "cardN" with "0000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    When I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin_id" with "1234"
    And I press "إرسال"
    And I press "تأكيد العملية"
    And I wait 10 seconds
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see "رقم طلبيتك هو"

  @hd @cs
  Scenario: As an authenticated user
    I should be able to checkout using Home delivery
    and pay by Cybersource on Arabic site
    When I follow "خدمة التوصيل للمنزل"
    And I wait for AJAX to finish
    When I follow "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I accept terms and conditions
    And I press "سجل الطلبية"
    When I wait 10 seconds
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see "رقم طلبيتك هو"

  @cc @knet
  Scenario: As an authenticated user
    I should be able to checkout using Click and Collect
    and pay by KNET on Arabic site
    When I follow "اختر واستلم"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Shuwaikh" on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "اختر هذا المحل"
    And I wait for AJAX to finish
    When I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    When I fill in "edit-billing-address-address-billing-given-name" with "شويتا"
    And I fill in "edit-billing-address-address-billing-family-name" with "شارما"
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "97004455"
    And I select "العباسية" from "edit-billing-address-address-billing-administrative-area"
    When I fill in "edit-billing-address-address-billing-locality" with "كتلة A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "الشارع ب"
    When I fill in "edit-billing-address-address-billing-dependent-locality" with "بناء C"
    When I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    And I wait for the page to load
    And I select "Knet Test Card [KNET1]" from "bank"
    And I fill in "cardN" with "0000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin" with "1234"
    And I press "إرسال"
    And I press "تأكيد العملية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see "رقم طلبيتك هو"

  @cc @cs
  Scenario: As an authenticated user
  I should be able to checkout using Click and Collect
  and pay by Cybersource on Arabic site
    When I follow "اختر واستلم"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Shuwaikh" on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "اختر هذا المحل"
    And I wait for AJAX to finish
    When I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I fill in "edit-billing-address-address-billing-given-name" with "شويتا"
    And I fill in "edit-billing-address-address-billing-family-name" with "شارما"
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "97004455"
    And I select "العباسية" from "edit-billing-address-address-billing-administrative-area"
    When I fill in "edit-billing-address-address-billing-locality" with "كتلة A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "الشارع ب"
    When I fill in "edit-billing-address-address-billing-dependent-locality" with "بناء C"
    When I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    When I wait 10 seconds
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see "رقم طلبيتك هو"
