@javascript @auth @sofa-sectional @homeDelivery @westelmkwprod @westelmaeprod @westelmsaprod
Feature: SPC Checkout Home Delivery on Sofa-sectional feature for Guest user

  Background:
    When I am on "{spc_sofa_page}"
    And I wait for the page to load

  @desktop @sofa-sectional
  Scenario: As a Guest user, I should be able to check sofa-sectional feature on pdp page
    When I select a product in stock on ".c-products__item"
    And I wait 15 seconds
    And I wait for the page to load
    Then I should not see an ".sofa-section-card.sofa-selection-summary-wrapper" element
    Then I should see an ".sofa-section-select-option-wrapper" element
    Then I should see an ".sofa-section-clear-option-btn" element
    Then I should see an ".form-swatch-list-wrapper" element
    And I click on ".sofa-section-card.attribute-wrapper_configuration ul.swatch-list li:first-child" element
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".sofa-section-card.attribute-wrapper_size ul li.active" element
    Then I should see an ".sofa-section-card ul.attribute-options-list.width li.active" element
    Then I should see an ".sofa-section-card ul.attribute-options-list.depth li.active" element
    Then I should see an ".sofa-section-card ul.attribute-options-list.cushion_style li.active" element
    Then I should see an ".sofa-section-card.sofa-selection-summary-wrapper" element
    And I scroll to the ".sofa-sectional-addtobag-button" element
    And I click on ".sofa-sectional-addtobag-button" element
    And I wait 10 seconds
    And I wait for the page to load
    And I scroll to the ".sofa-section-select-option-wrapper" element
    And I click on ".sofa-section-clear-option-btn" element
    Then I should not see an ".sofa-section-card.sofa-selection-summary-wrapper" element
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    Then I should see an "#spc-cart .spc-main .spc-content div.spc-product-attributes-wrapper" element
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/checkout/confirmation" page
    And I wait for the page to load
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @sofa-sectional @language
  Scenario: As an Authenticated user, I should be able to check sofa-sectional feature on pdp page in second language
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 15 seconds
    And I wait for the page to load
    Then I should not see an ".sofa-section-card.sofa-selection-summary-wrapper" element
    Then I should see an ".sofa-section-select-option-wrapper" element
    Then I should see an ".sofa-section-clear-option-btn" element
    Then I should see an ".form-swatch-list-wrapper" element
    And I click on ".sofa-section-card.attribute-wrapper_configuration ul.swatch-list li:first-child" element
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".sofa-section-card.attribute-wrapper_size ul li.active" element
    Then I should see an "" element
    Then I should see an ".sofa-section-card ul.attribute-options-list.width li.active" element
    Then I should see an ".sofa-section-card ul.attribute-options-list.depth li.active" element
    Then I should see an ".sofa-section-card ul.attribute-options-list.cushion_style li.active" element
    Then I should see an ".sofa-section-card.sofa-selection-summary-wrapper" element
    And I scroll to the ".sofa-sectional-addtobag-button" element
    And I click on ".sofa-sectional-addtobag-button" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    Then I should see an "#spc-cart .spc-main .spc-content div.spc-product-attributes-wrapper" element
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/{language_short}/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I should be on "/{language_short}/checkout/confirmation" page
    And I wait for the page to load
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element

  @mobile @sofa-sectional
  Scenario: As a Guest, I should be able to checkout using COD in second language
    When I click the anchor link ".dialog-off-canvas-main-canvas .language--switcher.mobile-only-block li.{mobile_language_class} a" on page
    And I wait 10 seconds
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    Then I should not see an ".sofa-section-card.sofa-selection-summary-wrapper" element
    Then I should see an ".sofa-section-select-option-wrapper" element
    Then I should see an ".sofa-section-clear-option-btn" element
    Then I should see an ".form-swatch-list-wrapper" element
    And I click on ".sofa-section-card.attribute-wrapper_configuration ul.swatch-list li:first-child" element
    And I wait for AJAX to finish
    And I wait 2 seconds
    Then I should see an ".sofa-section-card.attribute-wrapper_size ul li.active" element
    Then I should see an "" element
    Then I should see an ".sofa-section-card ul.attribute-options-list.width li.active" element
    Then I should see an ".sofa-section-card ul.attribute-options-list.depth li.active" element
    Then I should see an ".sofa-section-card ul.attribute-options-list.cushion_style li.active" element
    Then I should see an ".sofa-section-card.sofa-selection-summary-wrapper" element
    And I scroll to the ".sofa-sectional-addtobag-button" element
    And I click on ".sofa-sectional-addtobag-button" element
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    Then I should see an "#spc-cart .spc-main .spc-content div.spc-product-attributes-wrapper" element
    When I follow "continue to checkout"
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I click the anchor link ".checkout-link.submit" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    And I should see an ".spc-checkout-error-message-container" element
    And I should see an ".spc-checkout-error-message" element
