@javascript @auth @checkoutPayment @homeDelivery @silver_card_user @bpsauat
Feature: SPC Checkout for Advantage/Blue card feature for Authenticated user

  @silver_card
  Scenario: As an Authenticated user, I should be able to use Silver card discount on the products
    Given I am logged in as an authenticated user "{spc_silver_card_email}" with password "{spc_advantage_card_password}"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_basket_page}"
    And I wait for the page to load
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    And I should see an ".spc-main .spc-content .spc-checkout-section-title" element
    And I fill in an element having class ".spc-promo-code-block .block-content #promo-code" with "{spc_silver_card}"
    And I wait 5 seconds
    And I wait for AJAX to finish
    And I click on "#promo-action-button" element
    And I wait 10 seconds
    Then the promo code should be applied
    And I should see an "#promo-remove-button" element
    And I should see an ".total-line-item .discount-total" element
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an ".delivery-information-preview .delivery-name" element
    And I should see an ".total-line-item .discount-total" element
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    And I should save the order details in the file
    Then I should see "{order_confirm_text}"
    Then I should see "{spc_silver_card_email}"
    Then I should see "{order_detail}"

  @silver_card @language @desktop
  Scenario: As an Authenticated user, I should be able to use Silver card discount on the products in second language
    Given I am logged in as an authenticated user "{spc_silver_card_email}" with password "{spc_advantage_card_password}"
    And I wait 10 seconds
    Then I should be on "/user" page
    When I am on "{spc_basket_page}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    And I wait for AJAX to finish
    When I select a product in stock on ".c-products__item"
    And I wait 10 seconds
    And I wait for the page to load
    And I click on Add-to-cart button
    And I wait 10 seconds
    And I wait for the page to load
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait 30 seconds
    And I wait for the page to load
    And I should see an ".spc-main .spc-content .spc-checkout-section-title" element
    And I fill in an element having class ".spc-promo-code-block .block-content #promo-code" with "{spc_silver_card}"
    And I wait 5 seconds
    And I wait for AJAX to finish
    And I click on "#promo-action-button" element
    And I wait 10 seconds
    Then the promo code should be applied
    And I should see an "#promo-remove-button" element
    And I should see an ".total-line-item .discount-total" element
    When I click on "#block-content #spc-cart .spc-sidebar .spc-order-summary-block a.checkout-link" element
    And I wait 30 seconds
    And I wait for the page to load
    And I click jQuery "#spc-checkout .spc-main .spc-content .spc-checkout-delivery-methods .home-delivery" element on page
    And I wait 10 seconds
    And I wait for AJAX to finish
    And I select the home delivery address
    And I should see an ".delivery-information-preview .delivery-name" element
    And I should see an ".total-line-item .discount-total" element
    And I scroll to the ".spc-delivery-shipping-methods .shipping-method" element
    Then I select the Checkout payment method
    And I wait for AJAX to finish
    Then the checkout payment checkbox should be checked
    Then I fill checkout card details having class ".spc-type-cc-number input" with "{spc_checkout_card}"
    And I fill checkout card details having class ".spc-type-expiry input" with "{spc_checkout_expiry}"
    And I fill checkout card details having class ".spc-type-cvv input" with "{spc_checkout_cvv}"
    And I wait 10 seconds
    And I click the anchor link "#spc-checkout .spc-main .spc-content div.checkout-link.submit a.checkout-link" on page
    And I wait 50 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    And I should save the order details in the file
    Then I should see "{spc_silver_card_email}"
