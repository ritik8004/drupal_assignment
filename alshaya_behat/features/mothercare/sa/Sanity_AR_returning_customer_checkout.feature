@javascript
Feature: To verify a returning customer is able to
  able to checkout with HD and C&C

  Scenario: As a returning customer
  I should be able to place an order for HD - COD and Cybersource
    Given I am on a configurable product
    When I wait for the page to load
    When I press "add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "trupti@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "password@1"
    When I press "تسجيل الدخول"
    And I wait for the page to load
    When I follow "خدمة التوصيل للمنزل"
    And I wait for the page to load
    When I select address for Arabic
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for the page to load
    When I press "تابع للدفع"
    And I wait for the page to load
    And I accept terms and conditions
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish


  Scenario: As a returning customer
  I should be able to place an order for CC - Cybersource
    Given I am on a configurable product
    When I wait for the page to load
    When I press "add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "trupti@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "password@1"
    When I press "تسجيل الدخول"
    And I wait for the page to load
    When I follow "اختر واستلم"
    And I wait for the page to load
    When I select a store on arabic for SA
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I fill in "edit-billing-address-address-billing-given-name" with "شويتا"
    And I fill in "edit-billing-address-address-billing-family-name" with "شارما"
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "571789654"
    And I select "أحد رفيدة" from "edit-billing-address-address-billing-area-parent"
    And I wait for AJAX to finish
    When I select "أحد رفيدة" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-address-line1" with "الشارع ب"
    And I fill in "edit-billing-address-address-billing-dependent-locality" with "بناء C"
    When I fill in "edit-billing-address-address-billing-address-line2" with "2"
    Then I should see "أؤكد أنني قرأت وفهمت"



