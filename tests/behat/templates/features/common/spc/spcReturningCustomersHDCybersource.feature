@javascript @returnUser @cybersourcePayment @homeDelivery
Feature: SPC Checkout Home Delivery CC for Returning Customers using Cybersource Card Payment Method

  Background:
    Given I am on "{spc_product_listing_page}"
    And I wait 10 seconds
    And I wait for the page to load

  @cc @hd @cybersource
  Scenario: As a returning customer, I should be able to checkout using CC
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
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    When I add in the billing address with following:
      | mobile                        | {mobile}      |
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    And I wait 10 seconds
    And I wait for the page to load
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods #payment-method-cybersource" element on page
    And I wait 2 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "checkout/confirmation" page

  @cc @hd @language @desktop @cybersource
  Scenario: As a returning customer, I should be able to checkout using COD in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
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
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
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
    And I wait 10 seconds
    And I wait for the page to load
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods #payment-method-cybersource" element on page
    And I wait 2 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page

  @cc @hd @language @mobile @cybersource
  Scenario: As a returning customer, I should be able to checkout using COD in second language
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
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_returning_user_email}"
    And I fill in "edit-pass" with "{spc_returning_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
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
    And I wait 10 seconds
    And I wait for the page to load
    And I wait for AJAX to finish
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods #payment-method-cybersource" element on page
    And I wait 2 seconds
    Then the "payment-method-cybersource" checkbox should be checked
    And I fill in an element having class ".payment-method-cybersource .spc-type-cc-number input" with "{spc_cybersource_card}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-expiry input" with "{spc_cybersource_expiry}"
    And I fill in an element having class ".payment-method-cybersource .spc-type-cvv input" with "{spc_cybersource_cvv}"
    And I scroll to the "#spc-payment-methods" element
    And  I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 10 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
