@javascript @auth @bazaar-voice @hmkwuat @bbwkwuat @bbwaeuat
Feature: SPC to verify ratings on Bazaar Voice for Guest user

  Background:
    Given I am on "{spc_pdp_page}"
    And I wait for element "#block-page-title"

  @desktop
  Scenario: As a Guest user, I should be able to write a review for a Product
    Then the element ".content__title_wrapper" should exist
    And the element "#reviews-rating" should exist
    And the element "div.bazaarvoice-strings" should exist
    And I scroll to the "#reviews-section" element
    And the element ".rating-wrapper .overall-summary-title" should exist
    And the element ".rating-wrapper .overall-summary" should exist
    And the element ".rating-wrapper .overall-summary .average-rating" should exist
    And the element ".rating-wrapper .overall-summary .histogram-data" should exist
    And the element "#reviews-section div.histogram-wrapper .inline-star:first-child" should exist
    And the element ".sorting-filter-wrapper" should exist
    And I wait 15 seconds
    And I click on "a#closed-review-submit" element
    And I wait 15 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_new_registered_user_email}"
    And I fill in "edit-pass" with "{spc_new_registered_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And the element ".write-review-form" should exist
    And the element "#rating .star-counter" should exist
    And I click jQuery "#rating-error > label:nth-child(1) > span" element on page
    And I wait 5 seconds
    And I fill in "title" with "My Review"
    And I wait 5 seconds
    And I fill in "reviewtext" with "Product Quality is a good, overall great product. Very smooth and presentable. Great fabric Product Quality is a good, overall great product."
    And I click jQuery "#rating_Quality-error > label:nth-child(1) > span" element on page
    And I wait 5 seconds
    And I fill in "usernickname" with "TestUser1234"
    And I wait 5 seconds
    And I scroll to the "#preview-write-review" element
    And I click jQuery "#preview-write-review" element on page
    And I wait 5 seconds
    Then the element "#post-review-message" should exist

  @language
  Scenario: As a Guest user, I should be able to write a review for a Product in second language
    When I follow "{language_link}"
    And I wait for the page to load
    Then the element ".content__title_wrapper" should exist
    And the element "#reviews-rating" should exist
    And the element "div.bazaarvoice-strings" should exist
    And I scroll to the "#reviews-section" element
    And the element ".rating-wrapper .overall-summary-title" should exist
    And I wait 15 seconds
    And I click on "a#closed-review-submit" element
    And I wait 15 seconds
    And I wait for AJAX to finish
    And I wait for the page to load
    Then I fill in "edit-name" with "{spc_auth_user_email}"
    And I fill in "edit-pass" with "{spc_auth_user_password}"
    Then I press "edit-submit"
    And I wait 10 seconds
    And the element ".write-review-form" should exist
    And the element "#rating .star-counter" should exist
    And I click jQuery "#rating-error > label:nth-child(1) > span" element on page
    And I wait 5 seconds
    And I fill in "title" with "تقييمي"
    And I wait 5 seconds
    And I fill in "reviewtext" with "جودة المنتج هي منتج جيد وجيد بشكل عام. سلس جدا ورائع. نسيج رائع جودة المنتج هو منتج جيد ورائع بشكل عام."
    And I click jQuery "#rating_Quality-error > label:nth-child(1) > span" element on page
    And I wait 5 seconds
    And I fill in "usernickname" with "TestUser1234"
    And I wait 5 seconds
    And I scroll to the "#preview-write-review" element
    And I click jQuery "#preview-write-review" element on page
    And I wait 5 seconds
    Then the element "#post-review-message" should exist
