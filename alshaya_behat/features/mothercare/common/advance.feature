@javascript
Feature: Advanced Page test
  As an admin and webmaster I must be able to add an advance page
  and see the published page as the site visitor.

  Scenario Outline: Creating Advanced Page
    Given I am on "/user/login"
    And I fill in "name" with "<email>"
    And I fill in "pass" with "<pwd>"
    When I press "sign in"
    And I wait for AJAX to finish
    Then I should see "my account"
    And I wait 10 seconds
    Then I should see "Content"
    And I wait for AJAX to finish
    When I click "Content"
    And I wait for AJAX to finish
    Then I should see "Add content"
    And I click "Add content"
    When I click "Advanced Page"
    And I wait 10 seconds
    And I fill in "Title" with "test1"
    And I select "English" from "langcode[0][value]"
       #Adding multiple depts"
  Then I fill in the following:
    |edit-field-delivery-banner-0-subform-field-title-0-value|Title dept|
    |field_delivery_banner[0][subform][field_sub_title][0][value]|Subtitle test|
    |field_delivery_banner[0][subform][field_link][0][uri]|Home (126)|
  And I press "field_delivery_banner_1_row_3_col_delivery_banner_add_more"
  And I wait for AJAX to finish
  And I fill in the following:
    |field_delivery_banner[1][subform][field_title][0][value]|Title 2 dept|
    |field_delivery_banner[1][subform][field_sub_title][0][value]|Subtitle 2 test|
    |field_delivery_banner[1][subform][field_link][0][uri]|Home (126)|
  And I wait 10 seconds
  Then I press "Add 1 row 3 col delivery banner"
  And I wait 30 seconds
  And I fill in the following:
    |field_delivery_banner[2][subform][field_title][0][value]|Title 3 dept|
    |field_delivery_banner[2][subform][field_sub_title][0][value]|Subtitle 3 test|
    |field_delivery_banner[2][subform][field_link][0][uri]|Home (126)|
  #Banner Full width
  And I wait 20 seconds
   Then I scroll to the "#edit-field-slider" element
    And I press "Banner full width"
    And I wait 10 seconds
    Then I attach the file "image2.jpg" to "edit-field-banner-0-subform-field-banner-0-upload"
    And I wait 30 seconds
    When I fill in "field_banner[0][subform][field_banner][0][alt]" with "banner image 2"
    And I press "Promo Banner full width"
    Then I attach the file "image1.jpg" to "edit-field-promo-banner-full-width-0-subform-field-banner-0-upload"
    And I wait 30 seconds
    Then I fill in the following:
      |field_promo_banner_full_width[0][subform][field_banner][0][alt]|promo full width image 2|
      |field_promo_banner_full_width[0][subform][field_link][0][uri]|Website Terms and Conditions (196)|
      |field_promo_banner_full_width[0][subform][field_link][0][uri]|Home (126)|
   #banner
  And I wait 10 seconds
  Then I scroll to the "#edit-body-0-format--2" element
  And I press "Add Banner"
  And I wait 10 seconds
  Then I attach the file "image1.jpg" to "files[field_slider_0_subform_field_banner_0]"
  And I wait 10 seconds
  And I fill in "field_slider[0][subform][field_banner][0][alt]" with " Alternative banner image"
  And I wait 10 seconds
  Then I attach the file "image2.jpg" to "files[field_slider_0_subform_field_mobile_banner_image_0]"
  And I wait 10 seconds
  And I fill in "field_slider[0][subform][field_mobile_banner_image][0][alt]" with "Alternative Mobile"
  And I wait 30 seconds
  #adding live text
  Then I press "Add Live text"
  And I wait 30 seconds
  And I fill in the following:
    |field_slider[0][subform][field_promo_block_button][0][subform][field_button_link][0][uri]|About H&M (81)|
    |field_slider[0][subform][field_promo_block_button][0][subform][field_button_link][0][title]| Test Live  |
    |field_slider[0][subform][field_promo_block_button][0][subform][field_promo_text_1][0][value]|Promo 1 live text|
    |field_slider[0][subform][field_promo_block_button][0][subform][field_promo_text_2][0][value]| promo 2 live text|
  And I select "top_center" from "field_slider[0][subform][field_promo_block_button][0][subform][field_button_position]"
  Then I select "Black bold" from "field_slider[0][subform][field_promo_block_button][0][subform][field_promo_theme]"
  #Promo Blocks
    And I press "Promo blocks"
    Then I press "Add 1 row 1st col 2 row 2nd col"
    And I wait 30 seconds
    And I press "Add Promo Block"
    And I wait 30 seconds
    When I attach the file "image2.jpg" to "files[field_promo_blocks_0_subform_field_1st_col_promo_block_0_subform_field_banner_0]"
    And I wait 30 seconds
    And I fill in "field_promo_blocks[0][subform][field_1st_col_promo_block][0][subform][field_banner][0][alt]" with "Promo blocks 1"
    And I wait 30 seconds
    Then I fill in the following:
      |field_promo_blocks[0][subform][field_1st_col_promo_block][0][subform][field_banner][0][alt]|test SEO|
      |field_promo_blocks[0][subform][field_1st_col_promo_block][0][subform][field_link][0][uri]|Website Terms and Conditions (196)|
    And I press "field_promo_blocks_0_subform_field_2nd_col_promo_block_promo_block_add_more"
    And I wait 30 seconds
    When I attach the file "image1.jpg" to "files[field_promo_blocks_0_subform_field_2nd_col_promo_block_0_subform_field_banner_0]"
    And I wait 30 seconds
    And I fill in "field_promo_blocks[0][subform][field_2nd_col_promo_block][0][subform][field_banner][0][alt]" with "test1 SEO"
    And I wait 30 seconds
    Then I fill in the following:
      |field_promo_blocks[0][subform][field_2nd_col_promo_block][0][subform][field_banner][0][alt]|promo Seo|
      |field_promo_blocks[0][subform][field_2nd_col_promo_block][0][subform][field_link][0][uri]|Website Terms and Conditions (196)|
    And I select "Published" from "moderation_state[0][state]"
    And I press "op"
    And I wait 10 seconds

    Examples:
      |email|pwd|
      |user3+admin@example.com|AlShAyAU1admin|

  @deptcategory
  Scenario Outline: As webmaster I am able to Edit advanced Page and make it into a department page
    Given I am on "/user/login"
    And I fill in "name" with "user3+webmaster@example.com"
    And I fill in "pass" with "AlShAyAU1webmaster"
    When I press "sign in"
    And I wait for AJAX to finish
    Then I should see "my account"
    And I wait 10 seconds
    Then I should see "Content"
    And I wait for AJAX to finish
    When I click "Content"
    And I wait for AJAX to finish
    When I click "test1"
    And I click "edit"
    And I wait for AJAX to finish
    Then I check the box "field_use_as_department_page[value]"
    And I wait for AJAX to finish
    When I select "<Productcat>" from "field_product_category"
    Then I select "Published" from "moderation_state[0][state]"
    And I press "op"
    And I wait 20 seconds

    Examples:
      |Productcat|
      #hmkw
      #|Kids      |
       #mckw
      |Toys      |


  @leftmenu
  Scenario: As a webmaster I am able to define left menu for the dept page.
    Given I am on "/user/login"
    And I fill in "name" with "user3+webmaster@example.com"
    And I fill in "pass" with "AlShAyAU1webmaster"
    When I press "sign in"
    And I wait for AJAX to finish
    Then I should see "my account"
    And I wait 10 seconds
    Then I should see "Content"
    And I wait for AJAX to finish
    When I click "Content"
    And I wait for AJAX to finish
    When I click "test1"
    And I click "edit"
    And I wait for AJAX to finish
    Then I check the box "field_show_left_menu[value]"
    And I wait for AJAX to finish
    Then I select "Published" from "moderation_state[0][state]"
    And I press "op"
    And I wait 20 seconds


  @deptcategory  @leftmenu
  Scenario Outline: As a user I am able to check the left menu is enabled or not
    Given I am on "<link>"
    Then I should see "Test1" in the ".breadcrumb" element
    And I should see "<category>" in the "<class>" element
    When I select an element having class "<class>"
    Then I am on "<link1>"

    Examples:
      |link|category|class|link1|
      #hmkw
     # |/kids|Baby Girl 4-24 months|.c-sidebar-first|/kids/baby-girl-4-24-months|
      #mckw
      |/toys|Rattles & Teethers|.c-category-list__item|/toys/shop-by-department|


  @deptcategory
  Scenario Outline: As webmaster I am able to Edit advanced Page and make it into a department page
    Given I am on "/user/login"
    And I fill in "name" with "user3+webmaster@example.com"
    And I fill in "pass" with "AlShAyAU1webmaster"
    When I press "sign in"
    And I wait for AJAX to finish
    Then I should see "my account"
    And I wait 10 seconds
    Then I should see "Content"
    And I wait for AJAX to finish
    When I click "Content"
    And I wait for AJAX to finish
    When I click "test1"
    And I click "edit"
    And I wait for AJAX to finish
    Then I check the box "field_use_as_department_page[value]"
    And I wait for AJAX to finish
    When I select "<Productcat>" from "field_product_category"
    Then I select "Published" from "moderation_state[0][state]"
    And I press "op"
    And I wait 20 seconds

    Examples:
      |Productcat|
      #hmkw
      #|Kids      |
       #mckw
      |Toys      |


  @leftmenu
  Scenario: As a webmaster I am able to define left menu for the dept page.
    Given I am on "/user/login"
    And I fill in "name" with "user3+webmaster@example.com"
    And I fill in "pass" with "AlShAyAU1webmaster"
    When I press "sign in"
    And I wait for AJAX to finish
    Then I should see "my account"
    And I wait 10 seconds
    Then I should see "Content"
    And I wait for AJAX to finish
    When I click "Content"
    And I wait for AJAX to finish
    When I click "test1"
    And I click "edit"
    And I wait for AJAX to finish
    Then I check the box "field_show_left_menu[value]"
    And I wait for AJAX to finish
    Then I select "Published" from "moderation_state[0][state]"
    And I press "op"
    And I wait 20 seconds


  @deptcategory  @leftmenu
  Scenario Outline: As a user I am able to check the left menu is enabled or not
    Given I am on "<link>"
    Then I should see "Test1" in the ".breadcrumb" element
    And I should see "<category>" in the "<class>" element
    When I select an element having class "<class>"
    Then I am on "<link1>"

    Examples:
      |link|category|class|link1|
      #hmkw
     # |/kids|Baby Girl 4-24 months|.c-sidebar-first|/kids/baby-girl-4-24-months|
      #mckw
      |/toys|Rattles & Teethers|.c-category-list__item|/toys/shop-by-department|

