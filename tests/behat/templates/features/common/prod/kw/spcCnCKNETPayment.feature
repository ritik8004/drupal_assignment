@javascript @KNET @KNetPayment @checkoutPayment @clickCollect @coskwpprod @tbskwpprod @tbskwprod @coskwprod @pbkkwpprod @westelmkwpprod @vskwpprod @bpkwprod @bbwkwpprod @mckwpprod @flkwpprod @mckwprod @flkwprod @hmkwprod @vskwprod
Feature: SPC Checkout Click and Collect using KNET payment method

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 5 seconds
    And I wait for the page to load

  @cc @cnc @desktop @knet
  Scenario: As a Guest, I should be able to checkout using click and collect with knet
    When I select a product in stock on ".c-products__item"
    And I wait 5 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 10 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 50 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    And I wait 5 seconds
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait for AJAX to finish
    And I scroll to the ".spc-section-billing-address" element
    Then I click on "#spc-checkout .spc-main .spc-content .spc-section-billing-address.cnc-flow .spc-billing-cc-panel" element
    And I wait 5 seconds
    And I wait for AJAX to finish
    And I add the billing address on checkout page
    And I wait 10 seconds
    And I wait for the page to load
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I select "Eidity [KNET]" from dropdown ".paymentselect"
    And I wait 2 seconds
    And I press "Submit"
    And I wait 2 seconds
    And I press "Cancel"
    And I wait for AJAX to finish
    And I wait 30 seconds
    Then I should be on "/checkout"
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @cc @cnc @language @desktop @knet
  Scenario: As a Guest, I should be able to checkout using click and collect with knet
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 5 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 5 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 50 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    And I wait 5 seconds
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait 10 seconds
    And I add the billing address on checkout page
    And I wait for the page to load
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I select "{language_spc_knet_option}" from dropdown ".paymentselect"
    And I wait 2 seconds
    And I press "إرسال"
    And I wait 2 seconds
    And I press "الغاء"
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @cc @cnc @mobile @knet
  Scenario: As a Guest, I should be able to checkout using click and collect with knet
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 5 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait 5 seconds
    And I wait for the page to load
    When I follow "continue to checkout"
    And I wait 50 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    And I wait 5 seconds
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    And I select the Knet payment method
    And I wait 10 seconds
    And I add the billing address on checkout page
    And I wait for the page to load
    And I click the anchor link ".checkout-link.submit" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I select "Eidity [KNET]" from dropdown ".paymentselect"
    And I wait 2 seconds
    And I press "Submit"
    And I wait 2 seconds
    And I press "Cancel"
    And I wait for AJAX to finish
    And I wait 30 seconds
    Then I should be on "/checkout"
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element
