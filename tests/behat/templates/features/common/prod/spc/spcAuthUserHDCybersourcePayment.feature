@javascript @auth @cybersourcePayment @homeDelivery @mcaeprod @mcaepprod
Feature: SPC Checkout Home Delivery using CyberSource Payment Method for Authenticated user

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_basket_page}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I scroll to the ".region__highlighted " element
    And I wait 10 seconds

  @cc @hd @cybersource
  Scenario: As a Guest, I should be able to checkout using CC
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    When I add in the billing address with following:
      | mobile   | {mobile}        |
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
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
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @desktop @cybersource
  Scenario: As a Guest, I should be able to checkout using COD in second language
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I scroll to the ".region__highlighted " element
    And I wait 10 seconds
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    When I add in the billing address with following:
      | mobile   | {mobile}        |
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
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
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @mobile @cybersource
  Scenario: As a Guest, I should be able to checkout using COD in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    When I add in the billing address with following:
      | mobile   | {mobile}        |
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
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
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @hd @billing @cybersource @cc
  Scenario: As a Guest, I should be able to checkout using COD with different billing and shipping address
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    When I add in the billing address with following:
      | mobile   | {mobile}        |
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
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
    When I add in the billing address with following:
      | fullname                      | {anon_username} |
      | mobile                        | {billing_mobile}        |
      | spc-area-select-selected-city | {billing_city_option} |
      | spc-area-select-selected      | {billing_area_option} |
      | address_line1                 | {billing_street}      |
      | dependent_locality            | {billing_building}    |
      | locality                      | {billing_locality}    |
      | address_line2                 | {billing_floor}       |
      | sorting_code                  | {billing_landmark}    |
      | postal_code                   | {billing_postal_code} |

    And I wait 20 seconds
    And I wait for the page to load
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @desktop @cybersource
  Scenario: As a Guest, I should be able to checkout using COD in second language
    When I follow "{language_link}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I scroll to the ".region__highlighted " element
    And I wait 10 seconds
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    When I add in the billing address with following:
      | mobile   | {mobile}        |
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
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
    When I add in the billing address with following:
      | mobile                        | {billing_mobile}      |
      | spc-area-select-selected-city | {billing_city_option} |
      | spc-area-select-selected      | {billing_area_option} |
      | address_line1                 | {billing_street}      |
      | dependent_locality            | {billing_building}    |
      | locality                      | {billing_locality}    |
      | address_line2                 | {billing_floor}       |
      | sorting_code                  | {billing_landmark}    |
      | postal_code                   | {billing_postal_code} |
    And I wait 20 seconds
    And I wait for the page to load
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @hd @language @mobile @cybersource
  Scenario: As a Guest, I should be able to checkout using COD in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    When I add in the billing address with following:
      | mobile                        | {mobile}      |
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
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
    When I add in the billing address with following:
      | fullname                      | {anon_username}       |
      | mobile                        | {billing_mobile}      |
      | spc-area-select-selected-city | {billing_city_option} |
      | spc-area-select-selected      | {billing_area_option} |
      | address_line1                 | {billing_street}      |
      | dependent_locality            | {billing_building}    |
      | locality                      | {billing_locality}    |
      | address_line2                 | {billing_floor}       |
      | sorting_code                  | {billing_landmark}    |
      | postal_code                   | {billing_postal_code} |
    And I wait 20 seconds
    And I wait for the page to load
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist
