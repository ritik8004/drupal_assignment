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

  @tc
  Scenario: As a Guest on Arabic site
  I should see the error message when terms and condition unchecked
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "554044555"
    And I select "الفجيرة" from "edit-guest-delivery-home-address-shipping-area-parent"
    And I wait for AJAX to finish
    And I select "الغيل" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I scroll to x "884" y "674" coordinates of page
    And I press "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I wait for AJAX to finish
    And I scroll to x "884" y "674" coordinates of page
 # By default terms and condition is unchecked.
    When I press "سجل الطلبية"
    And I wait for the page to load
    Then I should see text matching "يرجى الموافقة على الشروط والأحكام"