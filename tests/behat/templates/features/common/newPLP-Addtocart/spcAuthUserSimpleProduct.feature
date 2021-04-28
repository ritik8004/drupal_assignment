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
    Given I am on "{spc_product_listing_page}"
