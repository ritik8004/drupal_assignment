@javascript @auth @clickCollect @bazaar-voice @hmkwuat @bbwkwuat @mcaeuat
Feature: SPC Checkout using Click & Collect store for Guest user using Fawry payment method

  Background:
    Given I am on "{spc_basket_page}"
    And I wait 5 seconds
    And I wait for the page to load

  @cnc @desktop
  Scenario: As a Guest user, I should be able to checkout using click and collect with Fawry payment
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
    And I wait 30 seconds
    And I wait for the page to load
    Then I should be on "/cart/login" page
    When I click the anchor link ".edit-checkout-as-guest" on page
    And I wait 10 seconds
    And I wait for the page to load