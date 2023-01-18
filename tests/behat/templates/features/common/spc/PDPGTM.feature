@javascript
Feature: Test GTm tracking

  Background:
    Given I go to in stock category page
    And I wait 2 seconds

  @desktop
  Scenario: As a Guest, I should be able to see GTM tracking details on the PDP page.
    Given google tag manager id is 'GTM-NQ4JXJP'
    Given google tag manager data layer setting "currency" should be "KWD"
