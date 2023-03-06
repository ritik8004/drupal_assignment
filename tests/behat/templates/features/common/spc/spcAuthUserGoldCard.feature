@javascript @auth @checkoutPayment @homeDelivery @gold_card_user @bpsauat
Feature: SPC Checkout for Advantage/Blue card feature for Authenticated user

  @gold_card
  Scenario: As an Authenticated user, I should be able to use Gold card discount on the products
    Given I am logged in as an authenticated user "{spc_gold_card_email}" with password "{spc_advantage_card_password}"
    And I wait for element "#block-page-title"
    When I go to in stock category page
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And I should see an ".spc-main .spc-content .spc-checkout-section-title" element
    And I fill in an element having class ".spc-promo-code-block .block-content #promo-code" with "{spc_gold_card}"
    And I wait for AJAX to finish
    And I click on "#promo-action-button" element
    And I wait for AJAX to finish
    And I wait for element "#promo-remove-button.active"
    Then the promo code should be applied
    And I should see an "#promo-remove-button" element
    And I should see an ".total-line-item .discount-total" element
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an ".delivery-information-preview .delivery-name" element
    And I should see an ".total-line-item .discount-total" element
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_gold_card_email}"
    Then I should see "{order_detail}"

  @gold_card @language @desktop
  Scenario: As an Authenticated user, I should be able to use Gold card discount on the products in second language
    Given I am logged in as an authenticated user "{spc_gold_card_email}" with password "{spc_advantage_card_password}"
    And I wait for element "#block-page-title"
    When I go to in stock category page
    And I wait for element "#block-page-title"
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait for element "#block-content"
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    #-Cart Notification popup animation time
    And I wait 3 seconds
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".checkout-link.submit"
    And I should see an ".spc-main .spc-content .spc-checkout-section-title" element
    And I fill in an element having class ".spc-promo-code-block .block-content #promo-code" with "{spc_gold_card}"
    And I wait for AJAX to finish
    And I click on "#promo-action-button" element
    And I wait for AJAX to finish
    And I wait for element "#promo-remove-button.active"
    Then the promo code should be applied
    And I should see an "#promo-remove-button" element
    And I should see an ".total-line-item .discount-total" element
    When I follow "continue to checkout"
    And I wait for the page to load
    And I wait for element "#delivery-method-home_delivery"
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an ".delivery-information-preview .delivery-name" element
    And I should see an ".total-line-item .discount-total" element
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for element "input#payment-method-checkout_com_upapi[checked]"
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I click the anchor link ".checkout-link.submit a" on page
    And I wait for element "#spc-checkout-confirmation"
    And I should save the order details in the file
    Then I should see "{spc_gold_card_email}"
