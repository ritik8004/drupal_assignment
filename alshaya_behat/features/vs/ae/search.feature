 @javascript
Feature: Search feature

  Scenario: As a Guest user
    I should be able to search products
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I fill in "edit-keywords" with "tops"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "tops"

  Scenario: As a Guest user
  I should be able to view filters and load more items
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I fill in "edit-keywords" with "leggings"
    And I press "Search"
    And I wait for the page to load
    When I follow "Load More"
    And I wait for AJAX to finish
    Then more items should get loaded
    Then I should see "Size"
    And I should see "Colour"
    Then I should see "Price"

  Scenario: As a Guest user on Arabic site
    I should be able to view filters and load more items
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I fill in "edit-keywords" with "من قطعتين "
    And I press "Search"
    And I wait for the page to load
    When I click the label for "#block-content > div > div > ul > li > a"
    And I wait for AJAX to finish
    Then more items should get loaded
    Then I should see "حدّد اختيارك"
    Then I should see "اللون"
    And I should see "السعر"
    Then I should see "المقاس"

  Scenario: As a Guest user on Arabic site
  I should be able to search products
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I fill in "edit-keywords" with "من قطعتين"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page in Arabic for "من قطعتين"

  Scenario: As an authenticated user
  I should be able to search products
    Given I am logged in as an authenticated user "trupti@axelerant.com" with password "password@1"
    And I wait for the page to load
    When I fill in "edit-keywords" with "tops"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "tops"

  Scenario: As an authenticated user
  I should be able to search products
    Given I am logged in as an authenticated user "trupti@axelerant.com" with password "password@1"
    And I wait for the page to load
    When I fill in "edit-keywords" with "من قطعتين"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "من قطعتين"

  Scenario: As an user
    I should be prompted with a correct message
    when my search yields no results
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I follow "English"
    And I wait for the page to load
    When I fill in "edit-keywords" with "randomtext"
    And I press "Search"
    And I wait for the page to load
    Then I should see "Your search did not return any results."

  Scenario: As an user
  I should be prompted with a correct message
  when my search yields no results
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I fill in "edit-keywords" with "من قطعتين"
    And I press "Search"
    And I wait for the page to load
    Then I should see "من قطعتين"

  Scenario: As a Guest
    I should be able to search for a product
    and add it to the cart
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I follow "English"
    And I wait for the page to load
    When I fill in "edit-keywords" with "Leggings"
    And I press "Search"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    And I remove promo panel
    When I select a size for the product
    When I select "color" attribute for the product
    And I wait for AJAX to finish
    And I select "size" attribute for the product
    And I wait for AJAX to finish
    When I press "add to basket"
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
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "554044555"
    And I select "Dubai" from "edit-guest-delivery-home-address-shipping-area-parent"
    And I wait for AJAX to finish
    And I select "Abu Hail" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I press "deliver to this address"
    And I wait for AJAX to finish
    When I press "proceed to payment"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    When I accept terms and conditions
    And I press "place order"
    When I wait for the page to load
    Then I should see text matching "Thank you for shopping online with us, Test Test"

  Scenario: As a Guest
  I should be able to search for a product
  and add it to the cart on Arabic site
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I fill in "edit-keywords" with "ليغنغز"
    And I press "Search"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    And I remove promo panel
    When I select "color" attribute for the product
    And I wait for AJAX to finish
    And I select "size" attribute for the product
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I click the label for ".cart-link"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "555004455"
    And I select "أبو ظبي" from "edit-guest-delivery-home-address-shipping-area-parent"
    And I wait for AJAX to finish
    When I select "شركة أبو ظبي للإعلام" from "edit-guest-delivery-home-address-shipping-administrative-area"
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
    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"


  Scenario: As a Guest user
    I should be able to sort search results
    in ascending, descending order
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I wait for the page to load
    When I fill in "edit-keywords" with "sweatshirt"
    And I press "Search"
    And I wait for the page to load
    When I select "Name A to Z" from the dropdown
    And I wait for the page to load
    Then I should see results sorted in ascending order
    When I select "Name Z to A" from the dropdown
    And I wait for the page to load
    Then I should see results sorted in descending order
    When I select "Price High to Low" from the dropdown
    And I wait for the page to load
    Then I should see results sorted in descending price order
    When I select "Price Low to High" from the dropdown
    And I wait for the page to load
    Then I should see results sorted in ascending price order


  Scenario: As a Guest user
    when I type an Arabic term on English site
    then I should be redirected to to the Arabic site and vice-versa
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I wait for the page to load
    When I fill in "edit-keywords" with "ليغنغز"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page in Arabic for "من قطعتين"
    When I fill in "edit-keywords" with "tops"
    And I press "Search"
    When I wait for the page to load
    Then I should see Search results page for "tops"