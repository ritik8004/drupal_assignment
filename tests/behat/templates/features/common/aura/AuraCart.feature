@aura @javascript @vsaeuat
Feature: Aura Cart
  In order to track customer AURA points
  As a user
  Validate the point displaying for the different layouts on PDP

  Scenario: To validate the Aura Loyalty content for Guest User who is yet to be enrolled in Aura
    When I am on "{spc_pdp_page}"
    And I wait for the page to load
    And I scroll to the ".edit-add-to-cart" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".spc-aura-cart-content"
    And I wait for element ".spc-aura-points-to-earn"
