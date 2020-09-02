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
#    Then I should see "{sort_filter}"
#    Then I should see "{price_filter}"
#    Then I should see "{color_filter}"
#    Then I should see "{brand_filter}"
#    Then I should see "{collection_filter}"
#    Then I should see "{promotional_filter}"
#    Then I should be able to see the footer
#
#  @prod
#  Scenario: As a Guest, I should be able to sort in ascending and descending order the list
#    When I select "Name A to Z" from the filter "#edit-sort-bef-combine--2--wrapper"
#    And I wait 10 seconds
#    And I wait for the page to load
#    Then I should see results sorted in ascending order
#    When I select "Name Z to A" from the filter "#edit-sort-bef-combine--2--wrapper"
#    And I wait 10 seconds
#    And I wait for the page to load
#    Then I should see results sorted in descending order
#    When I select "Price High to Low" from the filter "#edit-sort-bef-combine--2--wrapper"
#    And I wait 10 seconds
#    And I wait for the page to load
#    Then I should see results sorted in descending price order
#    When I select "Price Low to High" from the filter "#edit-sort-bef-combine--2--wrapper"
#    And I wait 10 seconds
#    And I wait for the page to load
#    Then I should see results sorted in ascending price order
#
#  Scenario: As a Guest, I should be able to select a product in stock and complete the checkout journey
#    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
#    And I wait 10 seconds
#    And I wait for the page to load
#    And I select "subset_name" attribute for the product
#    And I wait for AJAX to finish
#    When I press "add to cart"
#    And I wait 10 seconds
#    And I wait for the page to load
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
#    And I fill in "guest_delivery_home[address][shipping][mobile_number][mobile]" with "555667733"
#    And I select "Dubai" from "edit-guest-delivery-home-address-shipping-area-parent"
#    And I wait for AJAX to finish
#    And I select "Damascus Street" from "edit-guest-delivery-home-address-shipping-administrative-area"
##    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
#    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
#    And I press "deliver to this address"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait 15 seconds
#    And I wait for the page to load
#    And I press "proceed to payment"
#    And I wait 15 seconds
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cybersource"
#    And I wait 10 seconds
#    And I wait for AJAX to finish
#    Then I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_number]" with "5436031030606378"
#    And I fill in "edit-acm-payment-methods-payment-details-wrapper-payment-method-cybersource-payment-details-cc-cvv" with "257"
#    And I select "07" from "edit-acm-payment-methods-payment-details-wrapper-payment-method-cybersource-payment-details-cc-exp-month"
#    Then I should see "I confirm that I have read and accept the"
#    And I accept terms and conditions
#    And I press "place order"
#    And I wait 10 seconds
#    And I wait for the page to load
#    Then I should see text matching "Thank you for shopping online with us, Test Test"
#    And I should see text matching "Your order number is "
#
#  @prod
#  Scenario: As a Guest, I should be able to select a product in stock and complete the checkout journey
#    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
#    And I wait 10 seconds
#    And I wait for the page to load
#    And I select "subset_name" attribute for the product
#    And I wait for AJAX to finish
#    When I press "add to cart"
#    And I wait 10 seconds
#    And I wait for AJAX to finish
#    When I go to "/cart"
#    And I wait 10 seconds
#    And I wait for the page to load
#    And I press "checkout securely"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I click the element with id "edit-checkout-guest-checkout-as-guest" on page
##    When I click "checkout as guest"
#    And I wait 10 seconds
#    And I wait for the page to load
#    And I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
#    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
#    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "555667733"
#    And I select "Dubai" from "edit-guest-delivery-home-address-shipping-area-parent"
#    And I wait for AJAX to finish
#    And I select "Damascus Street" from "edit-guest-delivery-home-address-shipping-administrative-area"
##    And I select "Abbasiya" from "edit-guest-delivery-home-address-shipping-administrative-area"
##    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "Block A"
#    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
#    And I press "deliver to this address"
#    And I wait 15 seconds
#    And I wait for the page to load
#    When I check the "member_delivery_home[address][shipping_methods]" radio button with "Standard Delivery" value
#    And I wait 10 seconds
#    And I wait for AJAX to finish
#    And I press "proceed to payment"
#    And I wait 15 seconds
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cybersource"
#    And I wait 10 seconds
#    And I wait for AJAX to finish
#    Then I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_number]" with "5436031030606378"
#    And I fill in "edit-acm-payment-methods-payment-details-wrapper-payment-method-cybersource-payment-details-cc-cvv" with "257"
#    And I select "07" from "edit-acm-payment-methods-payment-details-wrapper-payment-method-cybersource-payment-details-cc-exp-month"
#    Then I should see "I confirm that I have read and accept the"
#    And I accept terms and conditions
#    And I wait for the page to load
#
#  @arabic @prod
#  Scenario: As a Guest, I should be able to see the header and the footer on Arabic site
#    When I follow "العربية"
#    And I wait for the page to load
#    Then I should be able to see the header in Arabic
#    And I should see the title and count of items
#    Then I should see "{ar_sort_filter}"
#    Then I should see "{ar_price_filter}"
#    Then I should see "{ar_color_filter}"
#    Then I should see "{ar_brand_filter}"
#    Then I should see "{ar_collection_filter}"
#    Then I should see "{ar_promotional_filter}"
#    Then I should be able to see the footer in Arabic
#
#  @arabic
#  Scenario: As a Guest on Arabic site, I should be able to select a product in stock and complete the checkout journey
#    When I follow "عربية"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
#    And I wait 10 seconds
#    And I wait for the page to load
#    And I select "subset_name" attribute for the product
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
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "555667733"
#    And I select "ام القيوين" from "edit-guest-delivery-home-address-shipping-area-parent"
#    And I wait for AJAX to finish
#    And I select "منطقة سلمى" from "edit-guest-delivery-home-address-shipping-administrative-area"
##    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
#    And I press "توصيل إلى هذا العنوان"
#    And I wait 10 seconds
#    And I wait for AJAX to finish
#    When I press "تابع للدفع"
#    And I wait 15 seconds
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cybersource"
#    And I wait for AJAX to finish
#    Then I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_number]" with "5436031030606378"
#    And I fill in "edit-acm-payment-methods-payment-details-wrapper-payment-method-cybersource-payment-details-cc-cvv" with "257"
#    And I select "07" from "edit-acm-payment-methods-payment-details-wrapper-payment-method-cybersource-payment-details-cc-exp-month"
#    Then I should see "I confirm that I have read and accept the"
#    And I accept terms and conditions
#    And I wait 10 seconds
#    When I press "سجل الطلبية"
#    And I wait 10 seconds
#    And I wait for the page to load
#    Then I should see text matching "شكراً لتسوقكم معنا عبر الموقع، Test Test"
#    Then I should see "رقم طلبيتك هو"
#
#  @arabic @prod
#  Scenario: As a Guest on Arabic site I should be able to select a product in stock and complete the checkout journey
#    When I follow "العربية"
#    And I wait for the page to load
#    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
#    And I wait for the page to load
#    And I select "subset_name" attribute for the product
#    And I wait for the page to load
#    When I press "أضف إلى سلة التسوق"
#    And I wait for the page to load
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
#    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "555667733"
#    And I select "ام القيوين" from "edit-guest-delivery-home-address-shipping-area-parent"
#    And I wait for AJAX to finish
#    And I select "منطقة سلمى" from "edit-guest-delivery-home-address-shipping-administrative-area"
##    When I select "العباسية" from "edit-guest-delivery-home-address-shipping-administrative-area"
##    And I fill in "edit-guest-delivery-home-address-shipping-locality" with "كتلة A"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "الشارع ب"
#    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "بناء C"
#    When I fill in "edit-guest-delivery-home-address-shipping-address-line2" with "2"
#    And I press "توصيل إلى هذا العنوان"
#    And I wait 15 seconds
#    And I wait for AJAX to finish
#    When I press "تابع للدفع"
#    And I wait 10 seconds
#    And I wait for the page to load
#    When I select a payment option "payment_method_title_cybersource"
#    And I wait 10 seconds
#    Then I fill in "acm_payment_methods[payment_details_wrapper][payment_method_cybersource][payment_details][cc_number]" with "5436031030606378"
#    And I fill in "edit-acm-payment-methods-payment-details-wrapper-payment-method-cybersource-payment-details-cc-cvv" with "257"
#    And I select "07" from "edit-acm-payment-methods-payment-details-wrapper-payment-method-cybersource-payment-details-cc-exp-month"
#    Then I should see "I confirm that I have read and accept the"
#    And I accept terms and conditions
#    Then I should see "أؤكد أنني قرأت وفهمت"
