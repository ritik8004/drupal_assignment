@javascript @checkout @arabic @mmcpa-1930 @manual
Feature: Test various checkout scenarios for Arabic site

  Background:
    Given I am on a simple product page
    And I wait for the page to load
    When I press "add to basket"
    And I wait for AJAX to finish
    And I go to "/ar/cart"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    And I follow "إتمام عملية الشراء كزبون زائر"
    And I wait for the page to load

  @hd @cod
  Scenario: As a Guest on Arabic site
    I should be able to checkout using COD
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "شويتا"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "شارما"
    When I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
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
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، شويتا شارما"

  @hd @knet
  Scenario: As a Guest,
  I should be able to checkout using KNET
  I should be able to checkout using CD
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "شويتا"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "شارما"
    When I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
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
    And I select "Knet Test Card [KNET1]" from "bank"
    And I fill in "cardN" with "0000000001"
    And I select "8" from "Ecom_Payment_Card_ExpDate_Month"
    And I select "2020" from "Ecom_Payment_Card_ExpDate_Year"
    And I fill in "Ecom_Payment_Pin_id" with "1234"
    And I press "إرسال"
    And I press "تأكيد العملية"
    And I wait 10 seconds
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، شويتا شارما"

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
    When I fill in "edit-cc-firstname" with "شويتا"
    And I fill in "edit-cc-lastname" with "شارما"
    When I fill in "edit-cc-email" with "shweta@axelerant.com"
    And I fill in "edit-cc-mobile-number-mobile" with "97004455"
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
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، شويتا شارما"

  @knet
  Scenario: As a Guest
  I should be directed to basket page on cancelling a KNET transaction
  and User should be able to place order again
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "شويتا"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "شارما"
    When I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
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
    And I should see the button "إتمام الشراء بأمان"
    Then the url should match "/ar/cart"

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
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "شويتا"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "شارما"
    When I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
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
    When I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_number]" with "4111111111111111"
    And I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_cvv]" with "123"
    When I select "2020" from "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_exp_year]"
    And I accept terms and conditions
    When I press "سجل الطلبية"
    When I wait 10 seconds
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، شويتا شارما"

  @cc @cs
  Scenario: As a Guest
    I should be able to checkout using Click and Collect
    and Cybersource payment option on Arabic site
    When I follow "اختر واستلم"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Shuwaikh " on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    When I follow "اختر هذا المحل"
    And I wait for AJAX to finish
    When I fill in "edit-cc-firstname" with "شويتا"
    And I fill in "edit-cc-lastname" with "شارما"
    When I fill in "edit-cc-email" with "shweta@axelerant.com"
    And I fill in "edit-cc-mobile-number-mobile" with "97004455"
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
    When I wait 10 seconds
    When I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، شويتا شارما"
