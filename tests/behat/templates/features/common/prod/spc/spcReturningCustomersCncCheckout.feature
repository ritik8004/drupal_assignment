@javascript @returnUser @checkoutPayment @clickCollect @coskwprod @cosaeprod @cossaprod @pbkaepprod @pbkkwpprod @mujisapprod @mujiaepprod @mujikwpprod @aeoaepprod @aeokwpprod @aeosapprod @bpaepprod @bpsapprod @bpkwpprod @vskwpprod @pbkaeprod @pbksaprod @pbkkwprod @tbsegprod @bpkwprod @bpaeprod @bpsaprod @aeoaeprod @aeokwprod @aeosaprod @vskwprod @tbskwprod @mckwprod @vssaprod @vsaeprod @bbwsaprod @bbwaeprod @hmsaprod @hmkwprod @hmaeprod @flsaprod @flaeprod @flkwprod @vssapprod @vsaepprod @bbwsapprod @bbwaepprod @bbwkwpprod @hmsapprod @hmkwpprod @hmaepprod @flsapprod @flaepprod @flkwpprod
Feature: SPC Checkout using Click & Collect store for returning customer using Checkout (2D) Card Payment Method

  Background:
    Given I am on "{spc_product_listing_page}"
    And I wait 10 seconds
    And I wait for the page to load

  @cc @cnc @checkout_com
  Scenario: As a returning customer, I should be able to checkout using click and collect with credit card
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 10 seconds
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
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I scroll to the ".spc-section-billing-address" element
    And I wait 5 seconds
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @cc @cnc @language @mobile @checkout_com
  Scenario: As a returning customer, I should be able to checkout using click and collect with credit card
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait 10 seconds
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
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .delivery-method:nth-child(3)" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I scroll to the ".spc-section-billing-address" element
    And I wait 10 seconds
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait for AJAX to finish
    And I wait 50 seconds
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element
