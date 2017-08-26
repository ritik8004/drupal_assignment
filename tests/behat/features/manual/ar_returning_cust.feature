@javascript @checkout @arabic @manual @mmcpa-1930
Feature: Test various checkout scenarios as returning customer

  Background:
    Given I am on "/stronglax"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I go to "/ar/cart"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "shweta+2@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "Alshaya123$"
    When I press "تسجيل الدخول"
    And I wait for the page to load

  @hd @cod
  Scenario: As a returning customer
  I should be able to place an order for HD - COD on Arabic site
    Given I follow "توصيل إلى هذا العنوان"
    And I wait for the page to load
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    And I accept terms and conditions
    When I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Shweta Sharma"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see text matching "Your Privileges Card Number is: 6362544000135844"

  @hd @knet
  Scenario:  As a returning customer
  I should be able to place an order for HD - KNET
    Given I follow "توصيل إلى هذا العنوان"
    And I wait for the page to load
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    And I wait for the page to load
    And I select "Knet Test Card [KNET1]" from "bank"
    And I fill in "cardN" with "0000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin_id" with "1234"
    And I press "Submit"
    And I press "Confirm"
    And I wait 10 seconds
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Shweta Sharma"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see text matching "Your Privileges Card Number is: 6362544000135844"

  @hd @cs
  Scenario: As a returning customer
  I should be able to place an order for HD - Cybersource
    Given I follow "توصيل إلى هذا العنوان"
    And I wait for the page to load
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_number]" with "4111111111111111"
    And I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_cvv]" with "123"
    When I select "2020" from "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_exp_year]"
    And I accept terms and conditions
    When I press "سجل الطلبية"
    When I wait 10 seconds
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Shweta Sharma"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see text matching "Your Privileges Card Number is: 6362544000135844"

  @cc @knet
  Scenario: As a returning customer
  I should be able to place an order for Click and Collect - KNET
    Given I follow "اختر واستلم"
    And I wait for the page to load
    When I select the first autocomplete option for "shuwaikh" on the "edit-store-location" field
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
    And I press "Submit"
    And I press "Confirm"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Shweta Sharma"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see text matching "Your Privileges Card Number is: 6362544000135844"

  @cc @cs
  Scenario: As a returning customer
  I should be able to place an order for Click and Collect - Cybersource
    Given I follow "اختر واستلم"
    And I wait for the page to load
    When I select the first autocomplete option for "shuwaikh" on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "اختر هذا المحل"
    And I wait for AJAX to finish
    When I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_number]" with "4111111111111111"
    And I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_cvv]" with "123"
    When I select "2020" from "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_exp_year]"
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
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Shweta Sharma"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see text matching "Your Privileges Card Number is: 6362544000135844"
