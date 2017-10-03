@1735 @javascript @manual
Feature: Search feature

  Scenario: As a Guest user
    I should be able to search products
    Given I am on homepage
    When I fill in "edit-keywords" with "baby carrier"
    And I press "Search"
    Then I should see Search results page for "baby carrier"

  @arabic
  Scenario: As a Guest user
  I should be able to search products
    Given I am on homepage
    And I follow "عربية"
    When I fill in "edit-keywords" with "arabic"
    And I press "Search"
    Then I should see Search results page in Arabic for "arabic"

  Scenario: As an authenticated user
  I should be able to search products
    Given I am logged in as an authenticated user "shweta+2@axelerant.com" with password "Alshaya123$"
    When I fill in "edit-keywords" with "black"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "black"

  @arabic
  Scenario: As an authenticated user
  I should be able to search products
    Given I am logged in as an authenticated user "shweta+2@axelerant.com" with password "Alshaya123$"
    And I follow "عربية"
    When I fill in "edit-keywords" with "arabic"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "arabic"

  Scenario: As an user
    I should be prompted with a correct message
    when my search yields no results
    Given I am on homepage
    When I fill in "edit-keywords" with "randomtext"
    And I press "Search"
    Then I should see "Your search did not return any results."

  @arabic
  Scenario: As an user
  I should be prompted with a correct message
  when my search yields no results
    Given I am on homepage
    And I follow "عربية"
    When I fill in "edit-keywords" with "randomtext"
    And I press "Search"
    Then I should see "لا يوجد نتائج لبحثك"

  Scenario: As a Guest
  I should be able to search for a product
  and add it to the cart
    Given I am on homepage
    When I fill in "edit-keywords" with "baby"
    And I press "Search"
    And I wait for AJAX to finish
    When I select a product in stock
    And I wait for AJAX to finish
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    When I follow "checkout as guest"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Shweta"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Sharma"
    When I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
    When I select "Abbasiya" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    When I press "deliver to this address"
    And I wait for AJAX to finish
    When I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    When I accept terms and conditions
    And I press "place order"
    When I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Shweta Sharma "

  @arabic
  Scenario: As a Guest
  I should be able to search for a product
  and add it to the cart on Arabic site
    Given I am on homepage
    And I follow "عربية"
    When I fill in "edit-keywords" with "لباس"
    And I press "Search"
    And I wait for AJAX to finish
    When I select a product in stock
    And I wait for the page to load
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I go to "/ar/cart"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "إتمام عملية الشراء كزبون زائر"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "شويتا"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "شارما"
    When I fill in "edit-guest-delivery-home-address-shipping-organization" with "shweta@axelerant.com"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "97004455"
    When I select "العباسية" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
    When I press "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    When I accept terms and conditions
    And I press "سجل الطلبية"
    When I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، شويتا شارما"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على shweta@axelerant.com"

  Scenario: As a Guest user
  I should be able to sort search results
  in ascending, descending order
    Given I am on homepage
    When I fill in "edit-keywords" with "black"
    And I press "Search"
    And I wait for the page to load
    When I select "Name A to Z" from "edit-sort-bef-combine"
    And I wait 10 seconds
    Then I should see results sorted in ascending order
    When I select "Name Z to A" from "edit-sort-bef-combine"
    And I wait 10 seconds
    Then I should see results sorted in descending order
    When I select "Price High to Low" from "edit-sort-bef-combine"
    And I wait 10 seconds
    Then I should see results sorted in descending price order
    When I select "Price Low to High" from "edit-sort-bef-combine"
    And I wait 10 seconds
    Then I should see results sorted in ascending price order
