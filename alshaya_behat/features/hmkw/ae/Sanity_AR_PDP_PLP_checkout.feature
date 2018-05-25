@javascript
Feature: As a guest user I should be able to
  verify the PLP page ,verify fields on PDP,
  Select a product from PLP and proceed to checkout

  Background:
    Given I am on "/ladies/new-arrivals/clothes"
    And I wait for the page to load
    And I follow "عربية"
    And I wait for the page to load


  Scenario: As a Guest user
  I should be able to verify the fields on
  PLP page on arabic site
    Given I should be able to see the header in Arabic
    And I should see the title and count of items
    Then I should be able to see the footer in Arabic
    When I click the label for "#block-views-block-alshaya-product-list-block-1 > div > div > ul > li > a"
    And I wait for AJAX to finish
    Then more items should get loaded
    Then I should see "حدّد اختيارك"
    Then I should see "اللون"
    And I should see "السعر"
    Then I should see "المقاس"

  Scenario: As a Guest user I should be able to select product from
  PLP page and verify the fields on PDP page on arabic site
    Given I select a product from a product category
    And I wait for the page to load
    Then I should be able to see the header in Arabic
    Then it should display title, price and item code
    Then I should see the button "أضف إلى سلة التسوق"
    Then I should see "وصف المنتج"
    Then I should see buttons for facebook, Twitter and Pinterest
    And I should see "خيارات التوصيل"
    Then I should see "خدمة التوصيل للمنزل"
    Then I should see "خدمة التوصيل للمنزل"
    And I should see "التوصيل المجاني للطلبيات التي تزيد على 200 د.إ."
    And I should see "الاستلام من محلاتنا"
    Then I should be able to see the footer in Arabic
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I click the label for "#ui-id-2"
    Then I should see "التوصيل العادي"
    When I click the label for "#ui-id-2"
    When I click the label for "#ui-id-4"
    Then I should see "استلم من المحل مجاناً"
    And I should see "تحقق من توفر الكمية في المحلات"
    When I select the first autocomplete option for "Fujairah" on the "edit-location" field
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I should see the link for ".change-location-link"
    Then I should see "سيتي سنتر الفجيرة"
    When I click the label for ".change-location-link"
    Then I select the first autocomplete option for "Dubai" on the "store-location" field
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click the label for ".other-stores-link"
    And I wait for AJAX to finish
    Then I should see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
    And I scroll to x "0" y "0" coordinates of page
    When I click the label for ".close-inline-modal"
    Then I should not see the inline modal for ".click-collect-all-stores.inline-modal-wrapper.desc-open"
    When I click the label for ".read-more-description-link"
    And I wait for AJAX to finish
    Then I should see the inline modal for ".description-wrapper.desc-open"
    And I scroll to x "0" y "0" coordinates of page
    When I click the label for ".close"
    Then I should not see the inline modal for ".description-wrapper.desc-open"
    And I wait 10 seconds
    When I follow "دليل المقاسات"
    And I wait for AJAX to finish
    Then I should see "يرجى اختيار الفئة التي تريدها"
    When I press "Close"
    Then I should not see "يرجى اختيار الفئة التي تريدها"



  Scenario: As a Guest user on Arabic site I should be able to select product from
  PLP page, add to basket select Home Delivery and see COD, Cybersource
  as payment methods
    Given I select a product from a product category
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I wait for the page to load
    When I fill in "edit-guest-delivery-home-address-shipping-given-name" with "Test"
    And I fill in "edit-guest-delivery-home-address-shipping-family-name" with "Test"
    When I enter a valid Email ID in field "edit-guest-delivery-home-address-shipping-organization"
    And I fill in "edit-guest-delivery-home-address-shipping-mobile-number-mobile" with "555004455"
    And I select "دبي" from "edit-guest-delivery-home-address-shipping-area-parent"
    And I wait for AJAX to finish
    And I select "داون تاون دبي" from "edit-guest-delivery-home-address-shipping-administrative-area"
    And I fill in "edit-guest-delivery-home-address-shipping-address-line1" with "Street B"
    And I fill in "edit-guest-delivery-home-address-shipping-dependent-locality" with "Builing C"
    And I press "توصيل إلى هذا العنوان"
    And I wait for AJAX to finish
    When I press "تابع للدفع"
    And I wait for the page to load
    When I select a payment option "payment_method_title_cashondelivery"
    And I wait for AJAX to finish
    Then I should see "أؤكد أنني قرأت وفهمت"
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    And I accept terms and conditions
    Then I should see "أؤكد أنني قرأت وفهمت"



  Scenario: As a Guest user on Arabic site I should be able to verify fields on
    click and collect page
    Given I select a product from a product category
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I wait for the page to load
    When I follow "الاستلام من محلاتنا"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Fujairah" on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 5 seconds
    And I should see the link "عرض القائمة"
    And I should see the link "عرض الخريطة"
    And I should see the link "العودة إلى حقيبة التسوق"
    And I should see "ملخص الطلبية"
    And I should be able to see the header in Arabic
    And I should be able to see the footer in Arabic


  Scenario: As a Guest user on Arabic site I should be able to select product from
  PLP page, add to basket select Click and Collect and see  Cybersource
   as payment methods
    Given I select a product from a product category
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I wait for the page to load
    When I follow "الاستلام من محلاتنا"
    And I wait for AJAX to finish
    When I select the first autocomplete option for "Dubai " on the "edit-store-location" field
    And I wait for AJAX to finish
    When I wait 10 seconds
    When I follow "اختر هذا المحل"
    And I wait for AJAX to finish
    When I fill in "edit-cc-firstname" with "Test"
    And I fill in "edit-cc-lastname" with "Test"
    When I enter a valid Email ID in field "edit-cc-email"
    And I fill in "edit-cc-mobile-number-mobile" with "555004455"
    When I select an element having class ".cc-action"
    And I wait for AJAX to finish
    When I select a payment option "payment_method_title_cybersource"
    And I wait for AJAX to finish
    When I fill in an element having class ".cybersource-credit-card-input" with "4111111111111111"
    When I fill in an element having class ".cybersource-credit-card-cvv-input" with "123"
    When I select "2020" from dropdown ".cybersource-credit-card-exp-year-select"
    When I fill in "edit-billing-address-address-billing-given-name" with "Test"
    And I fill in "edit-billing-address-address-billing-family-name" with "Test"
    When I fill in "edit-billing-address-address-billing-mobile-number-mobile" with "555004455"
    And I select "دبي" from "edit-billing-address-address-billing-area-parent"
    And I wait for AJAX to finish
    And I select "داون تاون دبي" from "edit-billing-address-address-billing-administrative-area"
    And I fill in "edit-billing-address-address-billing-address-line1" with "الشارع ب"
    When I fill in "edit-billing-address-address-billing-dependent-locality" with "بناء C"
    When I accept terms and conditions
    Then I should see "أؤكد أنني قرأت وفهمت"


  Scenario: As a Guest user
  whenever I click 'back to basket' link on Map view
  I should be redirected to the basket page
    Given I select a product from a product category
    And I wait for the page to load
    When I select a color for the product
    And I wait for AJAX to finish
    When I select a size for the product
    And I wait for AJAX to finish
    When I press "أضف إلى سلة التسوق"
    And I wait for AJAX to finish
    When I go to "/cart"
    And I wait for the page to load
    When I follow "عربية"
    And I wait for the page to load
    When I press "إتمام الشراء بأمان"
    And I wait for the page to load
    When I follow "edit-checkout-guest-checkout-as-guest"
    And I wait for the page to load
    When I follow "الاستلام من محلاتنا"
    And I wait for the page to load
    When I select the first autocomplete option for "Dubai" on the "edit-store-location" field
    And I wait for AJAX to finish
    And I wait 10 seconds
    When I click the label for "[data-drupal-selector] .select-store:nth-of-type(1) .desktop-only"
    And I wait for AJAX to finish
    When I click the label for ".gm-style-iw .hours--label"
    And I wait 2 seconds
    Then I should see "الأحد"
    Then I should see "العودة إلى حقيبة التسوق"
