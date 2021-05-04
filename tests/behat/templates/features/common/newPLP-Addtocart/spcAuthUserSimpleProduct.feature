@javascript @plp-addtocart @smoke @auth @bpaeuat
Feature: Testing new PLP-Add to cart functionality for Authenticated user

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
    Given I am on "/shop-eye-lashes-eye-brows-accessories/"
    And I press "Add" button
    And I wait for AJAX to finish
    Then the element ".qty-text-wrapper" should exist
    When I click on ".qty-sel-btn--up" element
    And the quantity " " should be " "
    When I click on "qty-sel-btn--down" element
    Then the quantity "qty-text-wrapper" should be "decreased"
    When I click on "#block-alshayareactcartminicartblock a.cart-link" element
    And I wait for AJAX to finish


   @language
  Scenario: As an Authenticated User, I should be able to add products to cart on product listing page
     When I follow "{language_link}"
     And I wait for the page to load
     And I wait for AJAX to finish
     Given I am on "/shop-eye-lashes-eye-brows-accessories/"
     And I press "إضافة" button
     And I wait for AJAX to finish
     Then the element ".qty-text-wrapper" should exist
     When I click on ".qty-sel-btn--up" element
     And the quantity " " should be " "
     When I click on "qty-sel-btn--down" element
     Then the quantity "qty-text-wrapper" should be "decreased"
     When I click on "#block-alshayareactcartminicartblock a.cart-link" element
     And I wait for AJAX to finish





