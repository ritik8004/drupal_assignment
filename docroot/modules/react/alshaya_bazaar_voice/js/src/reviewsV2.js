import React from 'react';
import ReactDOM from 'react-dom';
import ReviewSummary from './reviews/components/review-summary';

(function reviewsV2($, Drupal, drupalSettings) {
  Drupal.behaviors.alshayaReviewsV2 = { // eslint-disable-line no-param-reassign
    attach: function reviewsV2Attach() {
      if ($('.entity--type-node').not('[data-sku *= "#"]').length === 0) {
        return;
      }
      const reviews = $('#reviews-section');
      if (reviews.hasClass('processed')) {
        return;
      }

      reviews.addClass('processed');

      ReactDOM.render(
        <ReviewSummary />,
        document.getElementById('reviews-section'),
      );

      Drupal.attachBehaviors(document, drupalSettings);
    },
  };
}(jQuery, Drupal, drupalSettings));
