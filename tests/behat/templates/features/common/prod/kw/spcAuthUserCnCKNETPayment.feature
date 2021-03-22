@javascript @KNET @KNetPayment @clickCollect @bbwkwpprod @mckwpprod @flkwpprod @mckwprod @bbwkwprod @flkwprod
Feature: SPC Checkout Click and Collect using KNET payment method for authenticated user

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

  @cc @cnc @desktop @knet
  Scenario: As an Authenticated user, I should be able to checkout using click and collect with knet
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 5 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 5 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 30 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay #click-and-collect-list-view li:nth-child(1) .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 5 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait 10 seconds
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @cnc @language @desktop @knet
  Scenario: As an Authenticated user, I should be able to checkout using click and collect with knet
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 5 seconds
    And I wait for the page to load
    When I press "{language_add_to_cart_link}"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 5 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 30 seconds
    When I select the first autocomplete option for "{language_store_area}" on the "edit-store-location" field
    When I wait 30 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay #click-and-collect-list-view li:nth-child(1) .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 5 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait 10 seconds
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist

  @cc @cnc @mobile @knet
  Scenario: As an Authenticated user, I should be able to checkout using click and collect with knet
    When I select a product in stock on ".views-element-container.block.block-views.block-views-blockalshaya-product-list-block-1"
    And I wait 5 seconds
    And I wait for the page to load
    When I press "{add_to_cart_link}"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 5 seconds
    And I wait for the page to load
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    Then I click on "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-information .spc-checkout-empty-delivery-text" element
    And I wait 30 seconds
    When I select the first autocomplete option for "{store_area}" on the "edit-store-location" field
    When I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay #click-and-collect-list-view li:nth-child(1) .spc-store-name-wrapper" element on page
    And I wait 5 seconds
    And I wait for AJAX to finish
    When I click jQuery ".popup-overlay  .spc-address-form .spc-cnc-address-form-sidebar .spc-cnc-store-actions button" element on page
    And I wait 5 seconds
    And I fill in the following:
      | fullname | {anon_username} |
      | mobile   | {mobile}        |
    Then I click jQuery ".popup-overlay #click-and-collect-selected-store .spc-cnc-contact-form #save-address" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait 10 seconds
    Then the element "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" should exist