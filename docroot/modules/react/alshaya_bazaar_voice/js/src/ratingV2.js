import React from 'react';
import ReactDOM from 'react-dom';
import Rating from './rating/components/rating';

(function ratingsV2($, Drupal, drupalSettings) {
  Drupal.behaviors.alshayaRatingsV2 = { // eslint-disable-line no-param-reassign
    attach: function reviewsV2Attach() {
      if ($('.entity--type-node').not('[data-sku *= "#"]').length === 0) {
        return;
      }
      const ratings = $('#reviews-rating');
      if (ratings.hasClass('processed')) {
        return;
      }

      ratings.addClass('processed');

      ReactDOM.render(
        <Rating />,
        document.getElementById('reviews-rating'),
      );

      Drupal.attachBehaviors(document, drupalSettings);
    },
  };
}(jQuery, Drupal, drupalSettings));
