@javascript
Feature: Test various checkout scenarios for Arabic site

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
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I wait for the page to load

  Scenario: As a Guest on Arabic site
  I should be able to checkout using COD
    And I enter arabic address for Saudi Arabia
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

  Scenario: As a Guest
    I should be able to checkout using HD
    and Cybersource payment option on Arabic site
    When I follow "خدمة التوصيل للمنزل"
    And I wait for the page to load
    And I scroll to x "200" y "500" coordinates of page
    And I enter arabic address for Saudi Arabia
    When I press "تابع للدفع"
    And I wait for the page to load
#    When I select a payment option "payment_method_title_cybersource"
    And I scroll to x "100" y "400" coordinates of page
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I accept terms and conditions
    When I press "سجل الطلبية"
    When I wait 10 seconds
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    Then I should see "رقم طلبيتك هو"