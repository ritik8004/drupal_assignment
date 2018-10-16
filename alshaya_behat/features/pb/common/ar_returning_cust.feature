@javascript
Feature: Test various checkout scenarios as returning customer

  Background:
    Given I am on a configured product
    And I wait for the page to load
    When I press "add to cart"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    And I remove promo panel
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    Then I fill in "edit-checkout-login-name" with "trupti@axelerant.com"
    And I fill in "edit-checkout-login-pass" with "password@1"
    When I press "تسجيل الدخول"
    And I wait for the page to load

  Scenario: As a returning customer
  I should be able to place an order for HD - COD on Arabic site
    When I follow "خدمة التوصيل للمنزل"
    And I wait for the page to load
    And I scroll to x "100" y "400" coordinates of page
    When I select address for Arabic
    And I wait for the page to load
    And I remove promo panel from delivery page
    And I wait for AJAX to finish
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I wait for the page to load
    And I scroll to x "200" y "900" coordinates of page
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

  Scenario: As a returning customer
  I should be able to place an order for HD - Cybersource
    When I follow "خدمة التوصيل للمنزل"
    And I wait for the page to load
    And I scroll to x "100" y "400" coordinates of page
    When I select address for Arabic
    And I wait for the page to load
    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
    And I remove promo panel from delivery page
    And I wait for AJAX to finish
    And I scroll to x "100" y "1000" coordinates of page
    When I press "تابع للدفع"
    And I wait for the page to load
#    When I select a payment option "payment_method_title_cybersource"
    And I scroll to x "100" y "400" coordinates of page
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I accept terms and conditions
    And I wait for the page to load
    When I press "سجل الطلبية"
    And I wait 10 seconds
    When I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على "
    Then I should see "رقم طلبيتك هو"