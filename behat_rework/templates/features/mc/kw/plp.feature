@javascript
Feature: Test the PLP page

#  Background:
#    Given I am on "{product_listing_page_url}"
#    And I wait for the page to load
#
#  @prod
#  Scenario: As a Guest, I should be able to see the header and the footer
#    Then I should be able to see the header
#    And I should see the title and count of items
##    Then I should see "Colour"
#    And I should see "Price"
#    And I should see "Brand"
##    Then I should see "Size"
##    Then I should see "Filter"
#    And I should see "Collection"
#    Then I should be able to see the footer
##    When I follow "Load More"
##    And I wait for AJAX to finish
##    Then more items should get loaded
#
#  @prod
#  Scenario: As a Guest, I should be able to sort in ascending and descending order the list
#    When I select "Name A to Z" from the filter "#edit-sort-bef-combine--wrapper"
#    And I wait for AJAX to finish
#    Then I should see results sorted in ascending order
#    When I select "Name Z to A" from the filter "#edit-sort-bef-combine--wrapper"
#    And I wait for AJAX to finish
#    Then I should see results sorted in descending order
#    When I select "Price High to Low" from the filter "#edit-sort-bef-combine--wrapper"
#    And I wait for AJAX to finish
#    Then I should see results sorted in descending price order
#    When I select "Price Low to High" from the filter "#edit-sort-bef-combine--wrapper"
#    And I wait for AJAX to finish
#    Then I should see results sorted in ascending price order
#
#  Scenario: As a Guest, I should be able to select a product in stock and complete the checkout journey
#    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
#    And I wait for the page to load
#    When I select a size for the product
#    And I wait for AJAX to finish
#    When I press "Add to basket"
#    And I wait 10 seconds
#    And I wait for AJAX to finish
#    When I go to "/cart"
#    And I wait 10 seconds
#    And I wait for the page to load
#    And I press "checkout securely"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I click the element with id "edit-checkout-guest-checkout-as-guest" on page
#    And I wait 10 seconds
#    And I wait for the page to load
#    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "guest_delivery_home[address][shipping][mobile_number][mobile]" with "55004455"
#    And I select "Sharq" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
#    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
#    And I press "deliver to this address"
#    And I wait for the page to load
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait for AJAX to finish
#    And I press "proceed to payment"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cashondelivery"
#    And I wait for AJAX to finish
#    And I accept terms and conditions
#    And I press "place order"
#    And I wait for the page to load
#    Then I should see text matching "Thank you for shopping online with us, Test Test"
#    And I should see text matching "Your order number is "
#
#  @prod
#  Scenario: As a Guest, I should be able to select a product in stock and complete the checkout journey
#    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
#    And I wait for the page to load
#    When I select a size for the product
#    And I wait for AJAX to finish
#    When I press "Add to basket"
#    And I wait for AJAX to finish
#    When I go to "/cart"
#    And I wait 10 seconds
#    And I wait for the page to load
#    And I press "checkout securely"
##    And I wait 10 seconds
#    And I wait for the page to load
#    When I click the element with id "edit-checkout-guest-checkout-as-guest" on page
#    And I wait 10 seconds
#    And I wait for the page to load
#    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
#    And I select "Abbasiya" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
#    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
#    And I press "deliver to this address"
##    And I wait 10 seconds
#    And I wait for the page to load
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
##    And I wait 10 seconds
#    And I wait for AJAX to finish
#    And I press "proceed to payment"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cashondelivery"
#    And I wait 10 seconds
#    And I wait for AJAX to finish
#    And I accept terms and conditions
#    Then I should see "I confirm that I have read and accept the"
#
#  @arabic @prod
#  Scenario: As a Guest, I should be able to see the header and the footer on Arabic site
#    When I follow "العربية"
#    And I wait for the page to load
#    Then I should be able to see the header in Arabic
#    And I should see the title and count of items
#    Then I should see "اللون"
#    And I should see "السعر"
#    And I should see "العلامة التجارية"
#    Then I should be able to see the footer in Arabic
##    When I click the label for "#block-views-block-alshaya-product-list-block-1 > div > div > ul > li > a"
##    And I wait for AJAX to finish
##    Then more items should get loaded
#
#  @arabic
#  Scenario: As a Guest on Arabic site, I should be able to select a product in stock and complete the checkout journey
#    When I follow "عربية"
#    And I wait for the page to load
#    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
#    And I wait for the page to load
#    When I select a size for the product
#    And I wait for AJAX to finish
#    When I press "أضف إلى سلة التسوق"
#    And I wait for AJAX to finish
#    Then I click on "#block-cartminiblock a.cart-link" element
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I press "إتمام الشراء بأمان"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I click the element with id "edit-checkout-guest-checkout-as-guest" on page
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
#    When I select "العباسية" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
#    And I press "توصيل إلى هذا العنوان"
##    And I wait 10 seconds
#    And I wait for AJAX to finish
#    When I press "تابع للدفع"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cashondelivery"
#    And I wait 10 seconds
#    When I press "سجل الطلبية"
#    And I wait for the page to load
#    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
#    Then I should see "رقم طلبيتك هو"
#
#  @arabic @prod
#  Scenario: As a Guest on Arabic site I should be able to select a product in stock and complete the checkout journey
#    When I follow "العربية"
##    And I wait 10 seconds
#    And I wait for the page to load
#    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
##    And I wait 10 seconds
#    And I wait for the page to load
#    When I select a size for the product
#    And I wait for AJAX to finish
#    When I press "أضف إلى سلة التسوق"
##    And I wait 10 seconds
#    And I wait for AJAX to finish
#    Then I click on "#block-cartminiblock a.cart-link" element
##    And I wait 10 seconds
#    And I wait for the page to load
#    When I press "إتمام الشراء بأمان"
##    And I wait 10 seconds
#    And I wait for the page to load
#    When I click the element with id "edit-checkout-guest-checkout-as-guest" on page
##    When I click "إتمام عملية الشراء كزبون زائر"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "55004455"
#    When I select "العباسية" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
#    And I press "توصيل إلى هذا العنوان"
##    And I wait 10 seconds
#    And I wait for AJAX to finish
#    When I press "تابع للدفع"
##    And I wait 5 seconds
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cashondelivery"
#    And I wait 10 seconds
#    And I accept terms and conditions
#    Then I should see "أؤكد أنني قرأت وفهمت"
