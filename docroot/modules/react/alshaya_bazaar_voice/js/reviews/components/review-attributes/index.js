import React from 'react';

const ReviewAttributes = () => (
  <div className="review-attributes">
    <div className="review-attributes-wrapper">
      {/* Replace the attribute details once available, hardcoded as of now. */}
      <div className="review-attributes-details">
        <span className="attribute-name">{Drupal.t('Height:')}</span>
        <span className="attribute-value"> 55</span>
      </div>
      <div className="review-attributes-details">
        <span className="attribute-name">{Drupal.t('Weight:')}</span>
        <span className="attribute-value"> 120 lbs</span>
      </div>
    </div>
  </div>
);

export default ReviewAttributes;
