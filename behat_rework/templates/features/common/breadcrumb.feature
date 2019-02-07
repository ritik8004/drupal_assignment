@javascript
Feature: To verify breadcrumb functionality

  Scenario Outline: As a guest
  I should be able to view breadcrumbs across the site
    Given I am on "<page>"
    And I wait for the page to load
    Then the breadcrumb "<breadcrumb>" should be displayed
  Examples:
    {var_breadcrumb_examples}
