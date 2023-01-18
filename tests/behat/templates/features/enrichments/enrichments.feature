@javascript @enrichments @desktop @rcs @v3 @coskwlocal @nbkwlocal @aykwlocal @aykwlando @nbkwuat
Feature: Enrichments
  In order to customise the look and feel of the page
  As a editor
  I want enrich products and categories

  Background:
    Given I am logged in as an authenticated user "{spc_admin_user_email}" with password "{spc_admin_user_password}"

  @term @category
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

  @node @product
  Scenario: Test enrichments for products
    Given I visit "/search"
    And I wait for element "#hits .view-content"
    And I click on "#hits .view-content > div:first-child a" element
    # Delete any previous enrichment.
    And I make sure there are no enrichments
    # Check that the tabs have the correct links.
    Then I should see the link "Translate" in "#block-local-tasks" section

    # Add enrichment.
    Then I follow "Enrich"
    And I fill in "Title" with "Enriched node"
    And I press "Save"
    Then the "#block-page-title h1" element should contain "Enriched node"
    Then I should see the link "Translate" in "#block-local-tasks" section

    # Edit the enrichment.
    Then I follow "Edit the enrichment"
    Then I should see value "Enriched node" for element "#edit-name-0-value"
    And I fill in "Title" with "Enriched node updated"
    And I press "Save"
    Then the "#block-page-title h1" element should contain "Enriched node updated"

    # Delete the enrichment.
    Then I follow "Delete the enrichment"
    And I press "Delete"
    Then the "#block-page-title h1" element should not contain "Enriched"
