@javascript
Feature: SPC Checkout Home Delivery CC for Authenticated Users

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I go to in stock category page
    And I wait 10 seconds
    And I wait for the page to load

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
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    When fill in billing address with following:
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery #payment-method-cybersource" element on page
    And I wait 10 seconds
    And I fill in an element having class ".spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "checkout/confirmation" page

  @cc @hd @language @desktop @cybersource
  Scenario: As a Guest, I should be able to checkout using COD in second language
    When I follow "{language_link}"
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
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery #payment-method-cybersource" element on page
    And I wait 5 seconds
    And I fill in an element having class ".spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page

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
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods #delivery-method-home_delivery" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    When fill in billing address with following:
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    And I fill in the following:
      | fullname | {anon_username} |
      | email    | {anon_email}    |
      | mobile   | {mobile}        |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 20 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery #payment-method-cybersource" element on page
    And I wait 5 seconds
    And I fill in an element having class ".spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
