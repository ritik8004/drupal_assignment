@aura @javascript @vsaeuat @vskwuat @tbskwuat
Feature: Aura Cart
  In order to track customer AURA points
  As a user
  Validate the point displaying for the different layouts on PDP

  Background:
    When I am on "{spc_pdp_page}"
    And I wait for the page to load

  @desktop
  Scenario: To validate the Aura Loyalty content for Guest User who is yet to be enrolled in Aura
    And I scroll to the ".edit-add-to-cart" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".spc-aura-cart-rewards-block"
    And I scroll to the ".spc-aura-cart-rewards-block" element
    And I wait for element ".spc-aura-cart-content"
    And I wait for element ".spc-aura-points-to-earn"
    When I click on ".spc-join-aura-link" element
    And I wait for the page to load
    And I wait for element ".aura-modal-form .close"
    And I click on ".aura-modal-form .close" element
    And I click on ".spc-aura-cart-rewards-block .spc-link-aura-link" element
    And I click on ".spc-aura-cart-rewards-block .link-card-otp-modal-overlay" element
    And I click on ".aura-modal-form .close" element

  @desktop @language
  Scenario: To validate the Aura Loyalty content for Guest User who is yet to be enrolled in Aura
    When I follow "{language_link}"
    And I wait for the page to load
    And I scroll to the ".edit-add-to-cart" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".spc-aura-cart-rewards-block"
    And I scroll to the ".spc-aura-cart-rewards-block" element
    And I wait for element ".spc-aura-cart-content"
    And I wait for element ".spc-aura-points-to-earn"
    When I click on ".spc-join-aura-link" element
    And I wait for the page to load
    And I wait for element ".aura-modal-form .close"
    And I click on ".aura-modal-form .close" element
    And I click on ".spc-aura-cart-rewards-block .spc-link-aura-link" element
    And I click on ".spc-aura-cart-rewards-block .link-card-otp-modal-overlay" element
    And I click on ".aura-modal-form .close" element

  @mobile
  Scenario: To validate the Aura Loyalty content for Guest User who is yet to be enrolled in Aura
    When I click on ".language--switcher.mobile-only-block.only-first-time li.ar a" element
    And I wait for the page to load
    And I scroll to the ".edit-add-to-cart" element
    And I click on Add-to-cart button
    And I wait for AJAX to finish
    And I wait for element ".cart-link .quantity"
    And I wait for the cart notification popup
    When I click on "#mini-cart-wrapper a.cart-link" element
    And I wait for element ".spc-aura-cart-rewards-block"
    And I scroll to the ".spc-aura-cart-rewards-block" element
    And I wait for element ".spc-aura-cart-content"
    And I wait for element ".spc-aura-points-to-earn"
    When I click on ".spc-join-aura-link" element
    And I wait for the page to load
    And I wait for element ".aura-modal-form .close"
    And I click on ".aura-modal-form .close" element
    And I click on ".spc-aura-cart-rewards-block .spc-link-aura-link" element
    And I click on ".spc-aura-cart-rewards-block .link-card-otp-modal-overlay" element
    And I click on ".aura-modal-form .close" element
