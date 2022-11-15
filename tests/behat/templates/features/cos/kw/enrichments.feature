@javascript @enrichments @desktop @rcs
Feature: Enrichments
  In order to customise the look and feel of navigation
  As a editor
  I want to add enrichments to terms

  Background:
    Given I am logged in as a user with the "Admin" role

  Scenario: Test enrichments for categories
    # Click on the last item of main menu.
    When I click on "ul.menu__list.menu--one__list > li:nth-last-child(2)" element
    # Delete any previous enrichment.
    And I make sure there are no enrichments
    # Check that the tabs have the correct links.
    Then I should see the link "Translate" in "#block-local-tasks" section

    # Add enrichment.
    Then I follow "Enrich"
    And I fill in "Name" with "Enriched term"
    And I press "Save"
    Then the "#block-page-title h1" element should contain "Enriched term"
    Then I should see the link "Translate" in "#block-local-tasks" section

    # Edit the enrichment.
    Then I follow "Edit the enrichment"
    Then I should see value "Enriched term" for element "#edit-name-0-value"
    And I fill in "Name" with "Enriched term updated"
    And I press "Save"
    Then the "#block-page-title h1" element should contain "Enriched term updated"

    # Delete the enrichment.
    Then I follow "Delete the enrichment"
    And I press "Delete"
    Then the "#block-page-title h1" element should not contain "Enriched"

  @node
  Scenario: Test enrichments for nodes
    Given I visit "/search"
    And I follow "#hits .view-content > div:first-child a"
    # Delete any previous enrichment.
    And I make sure there are no enrichments
    # Check that the tabs have the correct links.
    Then I should see the link "Translate" in "#block-local-tasks" section

    # Add enrichment.
    Then I follow "Enrich"
    And I fill in "Name" with "Enriched node"
    And I press "Save"
    Then the "#block-page-title h1" element should contain "Enriched node"
    Then I should see the link "Translate" in "#block-local-tasks" section

    # Edit the enrichment.
    Then I follow "Edit the enrichment"
    Then I should see value "Enriched node" for element "#edit-name-0-value"
    And I fill in "Name" with "Enriched node updated"
    And I press "Save"
    Then the "#block-page-title h1" element should contain "Enriched node updated"

    # Delete the enrichment.
    Then I follow "Delete the enrichment"
    And I press "Delete"
    Then the "#block-page-title h1" element should not contain "Enriched"
