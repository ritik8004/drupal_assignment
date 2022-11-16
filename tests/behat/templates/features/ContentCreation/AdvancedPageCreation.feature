@javascript @content @advanced-page @vsaeuat
Feature: To verify the Advanced page content creation on the site.

  Background: Login as admin user.
    Given I am logged in as an authenticated user "{spc_admin_user_email}" with password "{spc_admin_user_password}"
    And I wait for element "#block-page-title"

  Scenario Outline: Verify user should be able to create Advanced Page content
    # Switch language
    Given I click jQuery "#block-languageswitcher .<lang> a" element on page

    # Add new advanced page
    Given I visit "node/add/advanced_page"
    And I wait for element "h1"
    Then I fill in "edit-title-0-value" with "<title>"
    And I click on "#edit-field-slider-add-more-add-more-button-banner" element
    And I wait for element ".ajax-new-content .paragraph-type-top"
    Then I attach the file "image1.jpeg" to "files[field_slider_0_subform_field_banner_0]"
    And I wait for element "div.image-preview"
    And I fill in "field_slider[0][subform][field_banner][0][alt]" with "AltText"
    Then I press "Save"
    And I wait for element "#block-breadcrumbs"

    # Draft to Pending Review moderation state
    Then I edit the page
    And I fill in "edit-title-0-value" with "<title> <edited_text>"
    And I select "Pending review" from "Change to"
    When I press "edit-submit"
    Then I should see "Advanced Page <title>"

    # Pending Review to Approved moderation state
    Then I edit the page
    And I select "Approved" from "Change to"
    When I press "Save"
    Then I should see "has been updated."
    Then I edit the page

    # Approved to Published moderation state
    And I select "Published" from "Change to"
    When I press "Save"
    And I wait for element "#block-breadcrumbs"
    Then I should see "has been updated."

    # Delete content
    When I follow "Delete" in the "local_tasks" region
    And I wait for element "#node-advanced-page-delete-form"
    When I press "Delete"
    Then I should see "has been deleted."

    Examples:
      | lang | title                         | edited_text |
      | en   | Test Automation Advanced Page | edited      |
      | ar   | اختبار صفحة الأتمتة المتقدمة   | تم تحريره   |
