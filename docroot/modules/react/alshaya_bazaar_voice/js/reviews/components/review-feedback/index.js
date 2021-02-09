import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';

const ReviewFeedback = () => (
  <div className="review-feedback-vote">
    <span className="feedback-label">{Drupal.t('Was this review helpful?')}</span>
    <span className="feedback-positive">
      <a href="#">
        <span className="feedback-option-label">{Drupal.t('yes')}</span>
        <span className="feedback-count">(6)</span>
      </a>
    </span>
    <span className="feedback-negative">
      <a href="#">
        <span className="feedback-option-label">{Drupal.t('no')}</span>
        <span className="feedback-count">(6)</span>
      </a>
    </span>

    <ConditionalView condition={window.innerWidth > 767}>
      <span className="feedback-report">
        <a href="#">
          <span className="feedback-option-label">{Drupal.t('report')}</span>
        </a>
      </span>
    </ConditionalView>

  </div>
);

export default ReviewFeedback;
