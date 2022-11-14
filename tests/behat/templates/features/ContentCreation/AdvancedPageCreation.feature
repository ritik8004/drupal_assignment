@javascript @content @advanced-page @vsaeuat
Feature: To verify the Advanced page content creation on the site.

  Background: Login as admin user.
    Given I am logged in as an authenticated user "{spc_admin_user_email}" with password "{spc_admin_user_password}"
    And I wait for element "#block-page-title"

  Scenario: Verify user should be able to create Advanced Page content.
    And I visit "/node/add/advanced_page"
    And I fill in "Title" with "[Test] Automation Advanced Page"
    And I click on "#edit-field-slider-add-more-add-more-button-banner" element
    And I wait for element ".ajax-new-content .paragraph-type-top"
    And I attach the file "image1.jpeg" to "files[field_slider_0_subform_field_banner_0]"
    And I wait for element "div.image-preview"
    And I fill in "field_slider[0][subform][field_banner][0][alt]" with "AltText"
    And I press "edit-submit"
    And I wait for element "#block-breadcrumbs"
    And I edit the page
    And I fill in "Title" with "[Test] Automation Advanced Page edited"
    #-Draft to Pending Review moderation state
    And I select "Pending review" from "Change to"
    And I press "edit-submit"
    Then I should see "Advanced Page [Test] Automation Advanced Page edited has been updated."
    And I click on ".messages .placeholder a" element
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

  @language
  Scenario: Verify user should be able to create Advanced Page content on Arabic site
    And I click jQuery "#block-languageswitcher .ar a" element on page
    And I click on "#block-branding .site-brand-home a" element
    And I click on ".toolbar-menu-administration > .toolbar-menu > li:nth-child(2)" element
    And I wait for element "#block-adminimal-theme-page-title"
    And I click on "#block-adminimal-theme-local-actions li a" element
    And I wait for element "h1.page-title"
    And I click on "#block-adminimal-theme-content li:nth-child(1) a" element
    And I wait for element "#block-adminimal-theme-page-title"
    And I fill in "العنوان" with "[Test-Arabic] Automation Advanced Page"
    And I click on "#edit-field-slider-add-more-add-more-button-banner" element
    And I wait for element ".ajax-new-content .paragraph-type-top"
    And I attach the file "image1.jpeg" to "files[field_slider_0_subform_field_banner_0]"
    And I wait for element "div.image-preview"
    And I fill in "field_slider[0][subform][field_banner][0][alt]" with "AltText"
    And I press "edit-submit"
    And I wait for element "#block-breadcrumbs"
    And I click jQuery "#block-local-tasks ul li a[href$= 'edit']" element on page
    And I fill in "العنوان" with "[Test-Arabic] Automation Advanced Page edited"
    #-Draft to Pending Review moderation state
    And I select "Pending review" from "Change to"
    And I press "edit-submit"
    Then I should see "Advanced Page [Test-Arabic] Automation Advanced Page"
    And I click on ".messages .placeholder a" element
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
    Then I should see "Advanced Page [Test-Arabic] Automation Advanced Page edited has been updated."
    And I click on "#block-local-tasks ul li a[href$=delete]" element
    And I wait for element "#node-advanced-page-delete-form"
    And I press "edit-submit"
    Then I should see "The Advanced Page [Test-Arabic] Automation Advanced Page edited has been deleted."
    