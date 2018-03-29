@javascript
Feature: As an authenticated user of the site
  I should be able to have various permissions
  and perform certain actions

  Background:
    Given I am logged in as an authenticated user "bharat.webmaster@acquia.com" with password "tester@123"
    And I wait for the page to load
    Then I should see the link "My account"


  @webmaster
  Scenario: As an webmaster user
  I should be able to create and delete advance Page bbb
    When I am on "/node/add/advanced_page"
    And I wait for the page to load
    And I fill in "edit-title-0-value" with "Advanced page 1"
    And I press "edit-submit"
    Then I should be on "/advanced-page-1"
    And I follow "Delete"
    And I wait for the page to load
    And I press "edit-submit"
    Then I should see text matching "The Advanced Page Advanced page 1 has been deleted."


  @webmaster
  Scenario: As an webmaster user
  I should be able to create and delete department Page
    When I am on "/node/add/department_page"
    And I wait for the page to load
    And I fill in "edit-title-0-value" with "Deptpage 1"
    And I select "Men" from "edit-field-product-category"
    And I press "edit-submit"
    Then I should be on "/department-deptpage-1"
    And I follow "Delete"
    And I wait for the page to load
    And I press "edit-submit"
    Then I should see text matching "The Department Page Deptpage 1 has been deleted."


