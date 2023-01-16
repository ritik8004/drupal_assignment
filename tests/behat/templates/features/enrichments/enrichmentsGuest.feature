@javascript @enrichments @guest @desktop @rcs @v3 @coskwlocal @nbkwlocal @aykwlocal @aykwlando @nbkwuat
Feature: Enrichments
  In order to customise the look and feel of the page
  As a editor
  I want enrich products and categories

  @term @category
  Scenario: Make sure guest users do not see Enrichment links in the local tasks
    # Click on the last item of main menu.
    When I click on "ul.menu__list.menu--one__list > li:nth-last-child(2)" element
    Then I should not see the link "Enrich"
    Then I should not see the link "Edit the enrichment"
    Then I should not see the link "Delete the enrichment"

  @node @product
  Scenario: Test enrichments for products
    Given I visit "/search"
    And I wait for element "#hits .view-content"
    And I click on "#hits .view-content > div:first-child a" element
    Then I should not see the link "Enrich"
    Then I should not see the link "Edit the enrichment"
    Then I should not see the link "Delete the enrichment"
