@javascript
Feature: Test the PLP page

  Background:
    Given I am on "{spc_product_listing_page}"
    And I wait 5 seconds
    And I wait for the page to load

#  Scenario: As a Guest, I should be able to see the header and the footer
#    Then I should be able to see the header
#    And I should see the title and count of items
#    Then I should be able to see the footer
##    When I follow "Load More"
##    And I wait for AJAX to finish
##    Then more items should get loaded
#    Then I should see "{sort_filter}"
#    Then I should see "{price_filter}"
#    Then I should see "{color_filter}"
#    Then I should see "{brand_filter}"
#    Then I should see "{collection_filter}"
#    Then I should see "{promotional_filter}"
#    Then I should see "{filter}"
#    Then I should see "{size_filter}"
#
#  Scenario: As a Guest, I should be able to sort in ascending and descending order the list
##    When I select "Name A to Z" from the filter "#edit-sort-bef-combine--2--wrapper"
##    And I wait for AJAX to finish
##    Then I should see results sorted in ascending order
##    When I select "Name Z to A" from the filter "#edit-sort-bef-combine--2--wrapper"
##    And I wait for AJAX to finish
##    Then I should see results sorted in descending order
#    When I select "Price High to Low" from the filter "#edit-sort-bef-combine--2--wrapper"
#    And I wait for AJAX to finish
#    Then I should see results sorted in descending price order
#    When I select "Price Low to High" from the filter "#edit-sort-bef-combine--2--wrapper"
#    And I wait for AJAX to finish
#    Then I should see results sorted in ascending price order
#
#  Scenario: As a Guest, I should be able to select a product in stock and complete the checkout journey
#    When I select a product from a product category
#    And I wait 20 seconds
#    And I remove promo panel
#    When I select a color for the product
#    And I wait for AJAX to finish
#    And I wait 10 seconds
#    When I select a size for the product
#    And I wait 10 seconds
#    And I wait for AJAX to finish
#    When I press "add to bag"
#    And I wait for AJAX to finish
#    And I wait 5 seconds
#    When I go to "/cart"
#    And I wait 10 seconds
#    And I wait for the page to load
#    And I press "checkout securely"
#    And I wait 10 seconds
#    And I wait for AJAX to finish
#    When I click the element with id "edit-checkout-guest-checkout-as-guest" on page
#    And I wait 10 seconds
#    And I wait for the page to load
#    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "554044555"
#    And I select "Dubai" from "edit-guest-delivery-home-address-shipping-area-parent"
#    And I wait for AJAX to finish
#    And I select "Abu Hail" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
#    And I press "deliver to this address"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait 15 seconds
#    And I press "proceed to payment"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cashondelivery"
#    And I wait 5 seconds
#    And I wait for AJAX to finish
#    And I accept terms and conditions
#    And I press "place order"
#    And I wait 10 seconds
#    And I wait for the page to load
#    Then I should see text matching "Thank you for shopping online with us, Test Test"
#    And I should see text matching "Your order number is "
#
#  Scenario: As a Guest, I should be able to see the header and the footer on Arabic site
#    When I follow "العربية"
#    And I wait 10 seconds
#    Then I scroll to top
#    Then I should be able to see the header in Arabic
#    And I should see the title and count of items
#    Then I should be able to see the footer in Arabic
##    When I click the label for "#block-views-block-alshaya-product-list-block-1 > div > div > ul > li > a"
##    And I wait for AJAX to finish
##    Then more items should get loaded
#    Then I should see "{ar_sort_filter}"
#    Then I should see "{ar_price_filter}"
#    Then I should see "{ar_color_filter}"
#    Then I should see "{ar_brand_filter}"
#    Then I should see "{ar_collection_filter}"
#    Then I should see "{ar_promotional_filter}"
#    Then I should see "{ar_filter}"
#    Then I should see "{ar_size_filter}"
#
#  Scenario: As a Guest on Arabic site, I should be able to select a product in stock and complete the checkout journey
#    When I follow "العربية"
#    And I wait 20 seconds
#    When I select a product from a product category
#    And I wait 10 seconds
#    And I remove promo panel
#    When I select a color for the product
#    And I wait for AJAX to finish
#    And I wait 10 seconds
#    When I select a size for the product
#    And I wait 15 seconds
#    When I press "أضيفي إلى سلة التسوق"
#    And I wait 15 seconds
#    Then I click on "#block-cartminiblock a.cart-link" element
#    And I wait 15 seconds
#    And I press "إتمام الشراء بأمان"
#    And I wait 35 seconds
#    When I click the element with id "edit-checkout-guest-checkout-as-guest" on page
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "555004455"
#    And I select "أبو ظبي" from "edit-guest-delivery-home-address-shipping-area-parent"
#    And I wait for AJAX to finish
#    When I select "شركة أبو ظبي للإعلام" from "edit-guest-delivery-home-address-shipping-administrative-area"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
#    And I press "توصيل إلى هذا العنوان"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I press "تابع للدفع"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cashondelivery"
#    And I wait 10 seconds
#    And I wait for AJAX to finish
#    Then I scroll to the ".c-footer" element
#    And I accept terms and conditions
#    And I wait for AJAX to finish
#    When I press "سجلي الطلبية"
#    And I wait 10 seconds
#    And I wait for the page to load
#    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
#    Then I should see "رقم طلبيتك هو"
