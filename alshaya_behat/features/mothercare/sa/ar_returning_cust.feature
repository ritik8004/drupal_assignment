@javascript
Feature: Test various checkout scenarios as returning customer

  Background:
    Given I am on a configurable product
    When I wait for the page to load
    When I press "add to basket"
    And I wait for AJAX to finish
    When I go to "/ar/cart"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    And I fill in "edit-checkout-login-name" with "trupti@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "password@1"
    When I press "تسجيل الدخول"
    And I wait for the page to load

  Scenario: As a returning customer
  I should be able to place an order for HD - COD on Arabic site
    Given I follow "توصيل إلى هذا العنوان"
    And I wait for the page to load
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see "رقم طلبيتك هو"

  Scenario: I should be able to place an order by HD- CS
    As a returning customer
    Given I follow "توصيل إلى هذا العنوان"
    And I wait for the page to load
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    When I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see "رقم طلبيتك هو"

  Scenario: I should be able to place C&C - Cybersource order
    as a returning customer
    Given I follow "اختر واستلم"
    And I wait for the page to load
    When I select the first autocomplete option for "King Fahd Road, Jeddah Saudi Arabia" on the "edit-store-location" field
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
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "571123456"
    And I select "الجبيل" from "edit-guest-delivery-home-address-shipping-area-parent"
    And I wait for AJAX to finish
    When I select "الدخل المحدود" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
    When I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    When I wait 10 seconds
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see "رقم طلبيتك هو"