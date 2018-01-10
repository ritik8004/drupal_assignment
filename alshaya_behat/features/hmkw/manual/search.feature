@mmcpa-1735 @javascript @manual
Feature: Search feature

  @eng @prod
  Scenario: As a Guest user
    I should be able to search products
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I fill in "edit-keywords" with "bikini bottoms"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "bikini bottoms"

  @arabic @prod
  Scenario: As a Guest user on Arabic site
  I should be able to search products
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I follow "عربية"
    When I wait for the page to load
    When I fill in "edit-keywords" with "بلوزة بدون أ"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page in Arabic for "بلوزة بدون أ"

  @eng @prod
  Scenario: As an authenticated user
  I should be able to search products
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    When I fill in "edit-keywords" with "bikini bottoms"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "bikini bottoms"

  @arabic @prod
  Scenario: As an authenticated user
  I should be able to search products
    Given I am logged in as an authenticated user "shweta+3@axelerant.com" with password "Alshaya123$"
    And I wait for the page to load
    And I follow "عربية"
    When I wait for the page to load
    When I fill in "edit-keywords" with "بلوزة بدون أ"
#    When I fill in "edit-keywords" with "الرضع"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "بلوزة بدون أ"
#    Then I should see Search results page in Arabic for "الرضع"

  @eng @prod
  Scenario: As an user
    I should be prompted with a correct message
    when my search yields no results
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I fill in "edit-keywords" with "randomtext"
    And I press "Search"
    And I wait for the page to load
    Then I should see "Your search did not return any results."

  @arabic @prod
  Scenario: As an user
  I should be prompted with a correct message
  when my search yields no results
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I follow "عربية"
    When I wait for the page to load
    When I fill in "edit-keywords" with "نص عشوائي"
    And I press "Search"
    And I wait for the page to load
    Then I should see "لا يوجد نتائج لبحثك"

  @eng
  Scenario: As a Guest
    I should be able to search for a product
    and add it to the cart
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I fill in "edit-keywords" with "bikini bottoms"
    And I press "Search"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to cart"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    When I follow "checkout as guest"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    When I select "Kuwait City" from "edit-guest-delivery-home-address-shipping-administrative-area"
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
    Then I should see text matching "Thank you for shopping online with us, Test Test "

  @arabic
  Scenario: As a Guest
  I should be able to search for a product
  and add it to the cart on Arabic site
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I follow "عربية"
    When I wait for the page to load
    When I fill in "edit-keywords" with "بلوزة بدون أ"
    And I press "Search"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I click the label for ".cart-link"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "إتمام عملية الشراء كزبون زائر"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    When I select "مدينة الكويت" from "edit-guest-delivery-home-address-shipping-administrative-area"
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
    And I should see text matching "ستصلك رسالة تأكيد لطلبيتك بعد Test Test"

  @prod
  Scenario: As a Guest user
    I should be able to sort search results
    in ascending, descending order
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I fill in "edit-keywords" with "bikini bottoms"
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

  @prod
  Scenario: As a Guest user
    when I type an Arabic term on English site
    then I should be redirected to to the Arabic site and vice-versa
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
#    When I fill in "edit-keywords" with "بكحتة"
    When I fill in "edit-keywords" with "بلوزة بدون أ"
    And I press "Search"
#    Then I should see Search results page in Arabic for "بكحتة"
    And I wait for the page to load
    Then I should see Search results page in Arabic for "بلوزة بدون أ"
    When I fill in "edit-keywords" with "bikini bottoms"
    And I press "Search"
    When I wait for the page to load
    Then I should see Search results page for "bikini bottoms"

  @eng @prod
  Scenario: As a Guest
  I should be able to search for a product
  and add it to the cart
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    When I fill in "edit-keywords" with "bikini bottoms"
    And I press "Search"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "Add to cart"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I press "checkout securely"
    And I wait for the page to load
    When I follow "checkout as guest"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    When I select "Kuwait City" from "edit-guest-delivery-home-address-shipping-administrative-area"
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

  @arabic @prod
  Scenario: As a Guest
  I should be able to search for a product
  and add it to the cart on Arabic site
    Given I am on homepage
    And I wait for the page to load
    When I close the popup
    And I wait for the page to load
    And I follow "عربية"
    When I wait for the page to load
    When I fill in "edit-keywords" with "بلوزة بدون أ"
    And I press "Search"
    And I wait for the page to load
    When I select a product in stock
    And I wait for the page to load
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I click the label for ".cart-link"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "إتمام عملية الشراء كزبون زائر"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
    When I select "مدينة الكويت" from "edit-guest-delivery-home-address-shipping-administrative-area"
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
