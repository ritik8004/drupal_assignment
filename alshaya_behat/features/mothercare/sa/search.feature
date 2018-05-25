@javascript
Feature: Search feature


  Scenario: As a Guest user
    I should be able to search products
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I fill in "edit-keywords" with "baby carrier"
    And I press "Search"
    Then I should see Search results page for "baby carrier"

  Scenario: As a Guest user
  I should be able to search products
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I wait for the page to load
    When I fill in "edit-keywords" with "جوارب"
    And I press "Search"
    Then I should see Search results page in Arabic for "جوارب"

  Scenario: As an authenticated user
  I should be able to search products
    Given I am logged in as an authenticated user "anjali.nikumb@acquia.com" with password "password@1"
    And I wait for the page to load
    When I fill in "edit-keywords" with "socks"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "socks"

  Scenario: As an authenticated user
  I should be able to search products
    Given I am logged in as an authenticated user "anjali.nikumb@acquia.com" with password "password@1"
    And I wait for the page to load
    And I follow "عربية"
    When I wait for the page to load
    When I fill in "edit-keywords" with "Arb. انت"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "Arb. انت"

  Scenario: As an user
    I should be prompted with a correct message
    when my search yields no results
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I fill in "edit-keywords" with "randomtext"
    And I press "Search"
    Then I should see "Your search did not return any results."

  Scenario: As an user
  I should be prompted with a correct message
  when my search yields no results
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I wait for the page to load
    When I fill in "edit-keywords" with "نص عشوائي"
    And I press "Search"
    Then I should see "لا يوجد نتائج لبحثك"


  Scenario: As a Guest
    I should be able to search for a product
    and add it to the cart
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I fill in "edit-keywords" with "socks"
    And I press "Search"
    And I wait for AJAX to finish
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "571012345"
    And I select "Dahran" from "edit-guest-delivery-home-address-shipping-area-parent"
    And I wait for AJAX to finish
    And I select "ad danah ash shamaliyah" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
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
    Then I should see text matching "Thank you for shopping online with us, Test Test"


  Scenario: As a Guest user
  I should be able to sort search results
  in ascending, descending order
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I fill in "edit-keywords" with "black"
    And I press "Search"
    And I wait for the page to load
    When I select "Name A to Z" from the dropdown
    And I wait for AJAX to finish
    Then I should see results sorted in ascending order
    When I select "Name Z to A" from the dropdown
    And I wait for AJAX to finish
    Then I should see results sorted in descending order
    When I select "Price Low to High" from the dropdown
    And I wait for AJAX to finish
    Then I should see results sorted in ascending price order
    When I select "Price High to Low" from the dropdown
    And I wait for AJAX to finish
    Then I should see results sorted in descending price order


  Scenario: As a Guest user
  when I type an Arabic term on English site
  then I should be redirected to to the Arabic site and vice-versa
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I fill in "edit-keywords" with "Arb. انت"
    And I press "Search"
    Then I should see Search results page in Arabic for "Arb. انت"
    When I fill in "edit-keywords" with "baby"
    And I press "Search"
    When I wait for the page to load
    Then I should see Search results page for "baby"

  Scenario: As a Guest
  I should be able to search for a product
  and add it to the cart on Arabic site
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait 2 seconds
    When I wait for the page to load
    When I fill in "edit-keywords" with "جوارب"
    And I press "Search"
    And I wait for AJAX to finish
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "571123456"
    And I select "الجبيل" from "edit-guest-delivery-home-address-shipping-area-parent"
    And I wait for AJAX to finish
    When I select "الدخل المحدود" from "edit-guest-delivery-home-address-shipping-administrative-area"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
    When I press "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    When I accept terms and conditions
    And I press "سجل الطلبية"
    When I wait for the page to load
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد قليل على"