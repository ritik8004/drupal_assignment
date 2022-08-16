@javascript @plp-addtocart @smoke @auth @bpaeqa @mckwqa @tbskwprod @tbsegprod @vssaprod @vskwprod
Feature: Testing new PLP-Add to cart functionality for Authenticated user on config product

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait for the page to load
    Then I should be on "/user" page

  @desktop @plp-addtocart
  Scenario: As an Authenticated User, I should be able to add configurable products to cart
    Given I am on "{spc_plp_add_to_cart}"
    And I wait for the page to load
    And I click jQuery ".c-products__item.views-row button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I should see an "#configurable-drawer" element
    And I should see an ".configurable-product-form-wrapper .product-details-wrapper .pdp-link" element
    And I scroll to the ".config-form-addtobag-button" element
    And I click jQuery ".config-form-addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 20 seconds
    Then the element "#block-alshayareactcartminicartblock .cart-link-total" should exist
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page

  @desktop @plp-addtocart @language
  Scenario: As an Authenticated User, I should be able to add configurable products to cart for second language
    Given I am on "{spc_plp_add_to_cart}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    And I click jQuery ".c-products__item.views-row button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I should see an "#configurable-drawer" element
    And I should see an ".configurable-product-form-wrapper .product-details-wrapper .pdp-link" element
    And I scroll to the ".config-form-addtobag-button" element
    And I click jQuery ".config-form-addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 20 seconds
    Then the element "#block-alshayareactcartminicartblock .cart-link-total" should exist
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page

  @mobile @plp-addtocart
  Scenario: As an Authenticated User, I should be able to add configurable products to cart for mobile
    Given I am on "{spc_plp_add_to_cart}"
    And I wait for the page to load
    And I click jQuery ".c-products__item.views-row button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 10 seconds
    Then I should see an "#configurable-drawer" element
    And I should see an ".configurable-product-form-wrapper .product-details-wrapper .pdp-link" element
    And I scroll to the ".config-form-addtobag-button" element
    And I click jQuery ".config-form-addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 20 seconds
    Then the element "#block-alshayareactcartminicartblock .cart-link-total" should exist
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page
