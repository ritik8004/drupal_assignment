@javascript @content @advanced-page @vsaeuat
Feature: To verify the Advanced page content creation on the site.

  Background: Login as admin user.
    Given I am logged in as an authenticated user "{spc_admin_user_email}" with password "{spc_admin_user_password}"
    And I wait for element "#block-page-title"
    And I visit "/node/add/advanced_page"

  Scenario: Verify user should be able to create Advanced Page content.
    And I fill in "Title" with "[Test] Automation Advanced Page"
    And I click on "#edit-field-slider-add-more-add-more-button-banner" element
    And I wait for element ".ajax-new-content .paragraph-type-top"
    And I upload "image1.jpeg" image in "files[field_slider_0_subform_field_banner_0]" image field
    And I fill in "field_slider[0][subform][field_banner][0][alt]" with "AltText"
    And I press "edit-submit"
    And I wait for element "#block-breadcrumbs"
    And I edit the page
    And I fill in "Title" with "[Test] Automation Advanced Page edited"
    #-Draft to Pending Review moderation state
    And I select "Pending review" from "Change to"
    And I press "edit-submit"
    Then I should see "Advanced Page [Test] Automation Advanced Page edited has been updated."
    And I click on ".placeholder a" element
    And I wait for element "#block-breadcrumbs"
    And I edit the page
    #-Pending Review to Approved moderation state
    And I select "Approved" from "Change to"
    And I press "edit-submit"
    And I wait for element "#block-breadcrumbs"
    And I edit the page
    #-Approved to Published moderation state
    And I select "Published" from "Change to"
    And I press "edit-submit"
    And I wait for element "#block-breadcrumbs"
    Then I should see "Advanced Page [Test] Automation Advanced Page edited has been updated."
    And I click on "#block-local-tasks ul li a[href$=delete]" element
    And I wait for element "#node-advanced-page-delete-form"
    And I press "edit-submit"
    Then I should see "The Advanced Page [Test] Automation Advanced Page edited has been deleted."
    