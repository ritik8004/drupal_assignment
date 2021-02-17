@javascript @guest @cybersourcePayment @homeDelivery @pbsauat @hmaeuat @vssauat @mckwuat
Feature: SPC Checkout Home Delivery using CyberSource Payment Method

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load

  @cc @hd @cybersource
  Scenario: As a Guest, I should be able to checkout using CC
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    When fill in billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods #payment-method-cybersource" element on page
    And I wait 10 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{delivery_type_text}"
    Then I should see "{delivery_type}"
    Then I should see "{payment_type_text}"
    Then I should see "{cs_payment_type}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    And I should see "{subtotal}"
    Then I should see "{order_total}"
    And I should see "{vat}"
    And I should see "{continue_shopping_text}"

  @cc @hd @language @desktop @cybersource
  Scenario: As a Guest, I should be able to checkout using COD in second language
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods #payment-method-cybersource" element on page
    And I wait 5 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{language_order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{language_order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{language_delivery_type_text}"
    Then I should see "{language_delivery_type}"
    Then I should see "{language_payment_type_text}"
    Then I should see "{language_cs_payment_type}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    And I should see "{language_subtotal}"
    Then I should see "{language_order_total}"
    And I should see "{language_vat}"
    And I should see "{language_continue_shopping_text}"

  @cc @hd @language @mobile @cybersource
  Scenario: As a Guest, I should be able to checkout using COD in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods #payment-method-cybersource" element on page
    And I wait 5 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page

  @hd @billing @cybersource @cc
  Scenario: As a Guest, I should be able to checkout using COD with different billing and shipping address
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    When fill in billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods #payment-method-cybersource" element on page
    And I wait 10 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I click jQuery ".spc-section-billing-address .spc-billing-address-wrapper .spc-billing-information .spc-billing-change" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I fill in the following:
      | fullname | {anon_username} |
      | mobile   | {billing_mobile}        |
    When fill in billing address with following:
      | spc-area-select-selected-city | {billing_city_option} |
      | spc-area-select-selected      | {billing_area_option} |
      | address_line1                 | {billing_street}      |
      | dependent_locality            | {billing_building}    |
      | locality                      | {billing_locality}    |
      | address_line2                 | {billing_floor}       |
      | sorting_code                  | {billing_landmark}    |
      | postal_code                   | {billing_postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{delivery_type_text}"
    Then I should see "{delivery_type}"
    Then I should see "{billing_text}"
    Then I should see "{billing_street}"
    Then I should see "{billing_building}"
    Then I should see "{billing_locality}"
    Then I should see "{billing_floor}"
    Then I should see "{payment_type_text}"
    Then I should see "{cs_payment_type}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    And I should see "{subtotal}"
    Then I should see "{order_total}"
    And I should see "{vat}"
    And I should see "{continue_shopping_text}"

  @cc @hd @language @desktop @cybersource
  Scenario: As a Guest, I should be able to checkout using COD in second language
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods #payment-method-cybersource" element on page
    And I wait 5 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I click jQuery ".spc-section-billing-address .spc-billing-address-wrapper .spc-billing-information .spc-billing-change" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I fill in the following:
      | fullname | {anon_username} |
      | mobile   | {billing_mobile}        |
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_billing_city_option} |
      | spc-area-select-selected      | {language_billing_area_option} |
      | address_line1                 | {billing_street}      |
      | dependent_locality            | {billing_building}    |
      | locality                      | {billing_locality}    |
      | address_line2                 | {billing_floor}       |
      | sorting_code                  | {billing_landmark}    |
      | postal_code                   | {billing_postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should see "{language_order_confirm_text}"
    Then I should see "{anon_email}"
    Then I should see "{language_order_detail}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item" should exist
    Then the element "#spc-checkout-confirmation .spc-main .spc-content .spc-order-summary-order-detail .spc-detail-content .spc-order-summary-address-item .spc-value .spc-address-name" should exist
    Then I should see "{language_delivery_type_text}"
    Then I should see "{language_delivery_type}"
    Then I should see "{language_payment_type_text}"
    Then I should see "{language_cs_payment_type}"
    Then I click jQuery "#spc-detail-open" element on page
    And I wait 2 seconds
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .spc-checkout-section-title" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-image img" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-title-price .spc-product-title a" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .product-item .spc-product-attributes" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .sub-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .total-line-item .value .price .price-amount" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .grand-total" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-currency" should exist
    And the element "#block-content .spc-main .spc-sidebar .spc-order-summary-block .block-content .totals .hero-total .value .price .price-amount" should exist
    And I should see "{language_subtotal}"
    Then I should see "{language_order_total}"
    And I should see "{language_vat}"
    And I should see "{language_continue_shopping_text}"

  @cc @hd @language @mobile @cybersource
  Scenario: As a Guest, I should be able to checkout using COD in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods #payment-method-cybersource" element on page
    And I wait 5 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I click jQuery ".spc-section-billing-address .spc-billing-address-wrapper .spc-billing-information .spc-billing-change" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I fill in the following:
      | fullname | {anon_username} |
      | mobile   | {billing_mobile}        |
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_billing_city_option} |
      | spc-area-select-selected      | {language_billing_area_option} |
      | address_line1                 | {billing_street}      |
      | dependent_locality            | {billing_building}    |
      | locality                      | {billing_locality}    |
      | address_line2                 | {billing_floor}       |
      | sorting_code                  | {billing_landmark}    |
      | postal_code                   | {billing_postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
