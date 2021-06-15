@javascript @auth @codPayment @homeDelivery @vssaprod
Feature: SPC Checkout Home Delivery COD for Authenticated Users

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

  @cod @hd @desktop
  Scenario: As a Authenticated User, I should be able to checkout using COD
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:first" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "div.spc-address-list-member-overlay .address-list-content .spc-add-new-address-btn" element
    And I wait 10 seconds
    And I wait for the page to load
    When fill in billing address with following:
      | mobile   | {mobile}        |
      | spc-area-select-selected-city | {city_option} |
      | spc-area-select-selected      | {area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element

  @cod @hd @language @desktop
  Scenario: As a Authenticated User, I should be able to checkout using COD in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
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
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:first" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "div.spc-address-list-member-overlay .address-list-content .spc-add-new-address-btn" element
    And I wait 10 seconds
    And I wait for the page to load
    When fill in billing address with following:
      | mobile   | {mobile}        |
      | spc-area-select-selected-city | {language_city_option} |
      | spc-area-select-selected      | {language_area_option} |
      | address_line1                 | {street}      |
      | dependent_locality            | {building}    |
      | locality                      | {locality}    |
      | address_line2                 | {floor}       |
      | sorting_code                  | {landmark}    |
      | postal_code                   | {postal_code} |
    Then I click jQuery "#address-form-action #save-address" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element

  @cod @hd @language @mobile
  Scenario: As a Authenticated User, I should be able to checkout using COD in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "#block-alshayareactcartminicartblock #cart_notification div.matchback-cart-notification-close" element
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:first" element on page
    And I wait for AJAX to finish
    Then the "delivery-method-home_delivery" checkbox should be checked
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-address-form-edit-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "div.spc-address-list-member-overlay .address-list-content .spc-add-new-address-btn" element
    And I wait 10 seconds
    And I wait for the page to load
    And I fill in the following:
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
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element

  @cod @hd @mobile
  Scenario: As a Authenticated User, I should be able to checkout using COD (mobile)
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 10 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "#block-alshayareactcartminicartblock #cart_notification div.matchback-cart-notification-close" element
    And I wait 10 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:first" element on page
    And I wait 10 seconds
    Then the "delivery-method-home_delivery" checkbox should be checked
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-address-form-edit-link" element
    And I wait 10 seconds
    And I wait for the page to load
    Then I click on "div.spc-address-list-member-overlay .address-list-content .spc-add-new-address-btn" element
    And I wait 10 seconds
    And I wait for the page to load
    And I fill in the following:
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
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    And I click jQuery "#block-content #spc-checkout #spc-payment-methods .payment-method-cashondelivery" element on page
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
