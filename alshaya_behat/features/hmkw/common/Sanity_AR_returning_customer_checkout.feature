@javascript
Feature: To verify a returning customer is able to
  able to checkout with HD and C&C

  Scenario: As a returning customer
  I should be able to place an order for HD - COD, KNET and Cybersource
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
    Then I fill in "edit-checkout-login-name" with "trupti@axelerant"
    And I fill in "edit-checkout-login-pass" with "Password@1"
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
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    And I accept terms and conditions
    Then I should see "أؤكد أنني قرأت وفهمت"
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I accept terms and conditions
    Then I should see "أؤكد أنني قرأت وفهمت"
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    And I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    And I wait for the page to load
    And I press "CancelAction_id"

  Scenario: As a returning customer
  I should be able to place an order for CC - KNET & Cybersource
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
    Then I fill in "edit-checkout-login-name" with "kanchan.patil+test@qed42.com"
    And I fill in "edit-checkout-login-pass" with "Password@1"
    When I press "تسجيل الدخول"
    And I wait for the page to load
    And I follow "الاستلام من محلاتنا"
    And I wait for the page to load
    When I select a store on arabic
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
    When I accept terms and conditions
    Then I should see "أؤكد أنني قرأت وفهمت"
    When I select a payment option "payment_method_title_knet"
    And I wait for AJAX to finish
    When I fill in "edit-billing-address-address-billing-given-name" with "شويتا"
    And I fill in "edit-billing-address-address-billing-family-name" with "شارما"
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "55004455"
    And I select "العباسية" from "edit-billing-address-address-billing-administrative-area"
    When I fill in "edit-billing-address-address-billing-locality" with "كتلة A"
    And I fill in "edit-billing-address-address-billing-address-line1" with "الشارع ب"
    When I fill in "edit-billing-address-address-billing-dependent-locality" with "بناء C"
    When I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    And I wait for the page to load
    And I press "CancelAction_id"



