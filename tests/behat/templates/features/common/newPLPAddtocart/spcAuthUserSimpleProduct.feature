@javascript @plp-addtocart @smoke @auth @bpaeqa @mckwqa
Feature: Testing new PLP-Add to cart functionality for Authenticated user on simple product

  Background:
    Given I am on "user/login"
    And I wait 10 seconds
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait for the page to load
    Then I should be on "/user" page

  @desktop @plp-addtocart
  Scenario: As an Authenticated User, I should be able to add products to cart on product listing page
    Given I am on "{spc_plp_add_to_cart_single}"
    And I wait 20 seconds
    And I wait for the page to load
    And I click jQuery ".c-products__item:first-child button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the element ".qty-text-wrapper" should exist
    And the product quantity should be "increased"
    And the cart quantity should be "increased"
    And the product quantity should be "decreased"
    And the cart quantity should be "decreased"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page

  @language @plp-addtocart
  Scenario: As an Authenticated user, I should be able to add products to cart on product listing page for second language
    Given I am on "{spc_plp_add_to_cart_single}"
    And I wait for the page to load
    When I follow "{language_link}"
    And I wait for the page to load
    And I click jQuery ".c-products__item:first-child button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the element ".qty-text-wrapper" should exist
    And the product quantity should be "increased"
    And the cart quantity should be "increased"
    And the product quantity should be "decreased"
    And the cart quantity should be "decreased"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page

  @mobile @plp-addtocart
  Scenario: As an Authenticated User, I should be able to add products to cart on product listing page for mobile
    Given I am on "{spc_plp_add_to_cart_single}"
    And I wait 20 seconds
    And I wait for the page to load
    And I click jQuery ".c-products__item:first-child button.addtobag-button" element on page
    And I wait for AJAX to finish
    And I wait 5 seconds
    Then the element ".qty-text-wrapper" should exist
    And the product quantity should be "increased"
    And the cart quantity should be "increased"
    And the product quantity should be "decreased"
    And the cart quantity should be "decreased"
    And I wait for AJAX to finish
    And I wait 5 seconds
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait 20 seconds
    And I wait for the page to load
    Then I should be on "/cart" page
