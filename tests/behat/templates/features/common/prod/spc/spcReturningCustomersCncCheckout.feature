@javascript @returnUser @checkoutPayment @clickCollect @cosaepprod @cossapprod @coskwpprod @tbsaepprod @tbskwpprod @coskwprod @cosaeprod @cossaprod @pbkaepprod @pbkkwpprod @mujisapprod @mujiaepprod @mujikwpprod @aeoaepprod @aeokwpprod @aeosapprod @bpaepprod @bpsapprod @bpkwpprod @vskwpprod @pbkaeprod @pbksaprod @pbkkwprod @tbsegprod @bpkwprod @bpaeprod @bpsaprod @aeoaeprod @aeokwprod @aeosaprod @vskwprod @tbskwprod @mckwprod @vssaprod @vsaeprod @bbwsaprod @bbwaeprod @hmsaprod @hmkwprod @hmaeprod @flsaprod @flaeprod @flkwprod @vssapprod @vsaepprod @bbwsapprod @bbwaepprod @bbwkwpprod @hmsapprod @hmkwpprod @hmaepprod @flsapprod @flaepprod @flkwpprod
Feature: SPC Checkout using Click & Collect store for returning customer using Checkout (2D) Card Payment Method

  Background:
    Given I am on "{spc_product_listing_page}"
    And I wait for element "#block-page-title"

  @cc @cnc @checkout_com
  Scenario: As a returning customer, I should be able to checkout using click and collect with credit card
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "continue to checkout"
    And I wait for the page to load
    Then I am logged in as an authenticated user "{spc_returning_user_email}" with password "{spc_returning_user_password}"
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I add the billing address on checkout page
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element

  @cc @cnc @language @mobile @checkout_com
  Scenario: As a returning customer, I should be able to checkout using click and collect with credit card
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    When I follow "إتمام الشراء بأمان"
    And I wait for the page to load
    Then I am logged in as an authenticated user "{spc_returning_user_email}" with password "{spc_returning_user_password}"
    And I wait for element "#delivery-method-click_and_collect"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .click-and-collect" element on page
    And I wait for AJAX to finish
    And I select the collection store
    And I scroll to the "#spc-payment-methods" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I add the billing address on checkout page
    And I wait for AJAX to finish
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element ".spc-checkout-error-message-container"
    And I should see an ".spc-checkout-error-message-container" element
    Then I should see an ".spc-checkout-error-message" element
