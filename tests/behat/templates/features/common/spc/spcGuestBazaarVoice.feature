@javascript @auth @bazaar-voice @hmkwuat @bbwkwuat
Feature: SPC to verify ratings on Bazaar Voice for Guest user

  Background:
    Given I am on "{spc_pdp_page}"
    And I wait 5 seconds
    And I wait for the page to load

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
    And I click jQuery ".button-wrapper a#closed-review-submit.write-review-button" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    And the element ".write-review-form" should exist
    And the element "#rating .star-counter" should exist
    And I click jQuery "#rating-error > label:nth-child(1) > span" element on page
    And I wait 5 seconds
    And I fill in "title" with "My Review"
    And I wait 5 seconds
    And I fill in "reviewtext" with "Product Quality is a good, overall great product. Very smooth and presentable. Great fabric Product Quality is a good, overall great product."
    And I click jQuery "#isrecommended-error > span:nth-child(1)" element on page
    And I wait 5 seconds
    And I fill in "usernickname" with "Test123User4"
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
    And I click jQuery ".button-wrapper a#closed-review-submit.write-review-button" element on page
    And I wait 10 seconds
    And I wait for the page to load
    And I am logged in as an authenticated user "{spc_auth_user_email}" with password "{spc_auth_user_password}"
    And I wait 10 seconds
    And the element ".write-review-form" should exist
    And the element "#rating .star-counter" should exist
    And I click jQuery "#rating-error > label:nth-child(1) > span" element on page
    And I wait 5 seconds
    And I fill in "title" with "My Review"
    And I wait 5 seconds
    And I fill in "reviewtext" with "Product Quality is a good, overall great product. Very smooth and presentable. Great fabric Product Quality is a good, overall great product."
    And I click jQuery "#isrecommended-error > span:nth-child(1)" element on page
    And I wait 5 seconds
    And I fill in "usernickname" with "Testuser1234"
    And I wait 5 seconds
    And I scroll to the "#preview-write-review" element
    And I click jQuery "#preview-write-review" element on page
    And I wait 5 seconds
    Then the element "#post-review-message" should exist
