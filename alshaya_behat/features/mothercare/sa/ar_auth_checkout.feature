@javascript
Feature: As an authenticated user
  I should be able to checkout
  using various payment options on Arabic site

  Background:
    When I am logged in as an authenticated user "trupti@axelerant" with password "Alshaya123$"
    And I wait for the page to load
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

  Scenario: As an authenticated user
  I should be able to checkout using Home delivery
  and pay by Cash-on-delivery on Arabic site
    Given I follow "توصيل إلى هذا العنوان"
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

  Scenario: As an authenticated user
    I should be able to checkout using Home delivery
    and pay by Cybersource on Arabic site
    Given I follow "توصيل إلى هذا العنوان"
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

  Scenario: As an authenticated user
  I should be able to checkout using Click and Collect
  and pay by Cybersource on Arabic site
    Given I follow "اختر واستلم"
    And I wait for AJAX to finish
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