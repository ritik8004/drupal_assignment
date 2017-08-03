@1735 @javascript @manual
Feature: Search feature

  Scenario: As a Guest user
    I should be able to search products
    Given I am on homepage
    When I fill in "edit-keywords" with "baby carrier"
    And I press "Search"
    Then I should see Search results page for "baby carrier"

  @arabic
  Scenario: As a Guest user
  I should be able to search products
    Given I am on homepage
    And I follow "عربية"
    When I fill in "edit-keywords" with "arabic"
    And I press "Search"
    Then I should see Search results page in Arabic for "arabic"

  Scenario: As an authenticated user
  I should be able to search products
    Given I am logged in as an authenticated user "shweta+2@axelerant.com" with password "Alshaya123$"
    When I fill in "edit-keywords" with "black"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "black"

  @arabic
  Scenario: As an authenticated user
  I should be able to search products
    Given I am logged in as an authenticated user "shweta+2@axelerant.com" with password "Alshaya123$"
    And I follow "عربية"
    When I fill in "edit-keywords" with "arabic"
    And I press "Search"
    And I wait for the page to load
    Then I should see Search results page for "arabic"

  Scenario: As an user
    I should be prompted with a correct message
    when my search yields no results
    Given I am on homepage
    When I fill in "edit-keywords" with "randomtext"
    And I press "Search"
    Then I should see "Your search did not return any results."

  @arabic
  Scenario: As an user
  I should be prompted with a correct message
  when my search yields no results
    Given I am on homepage
    And I follow "عربية"
    When I fill in "edit-keywords" with "randomtext"
    And I press "Search"
    Then I should see "لا يوجد نتائج لبحثك"

  Scenario: As a Guest
    I should be able to search for a product
    and add it to the cart
    Given I am on homepage
    When I fill in "edit-keywords" with "baby carrier"
    And I press "Search"
    And I wait for AJAX to finish
    When I select a product
    And I wait for AJAX to finish
    When I press "Add to basket"
    And I wait for AJAX to finish
    When I follow "view basket"
    And I wait for AJAX to finish
    Then I should be able to view the product in the basket
