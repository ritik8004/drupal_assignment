import React from 'react';
import { getLanguageCode } from '../../../../utilities/api/request';

const ClosedReviewSubmit = ({ destination }) => (
  <div className="button-wrapper">
    <a
      id="closed-review-submit"
      className="write-review-button"
      href={`/${getLanguageCode()}/user/login?destination=${destination}`}
    >
      {Drupal.t('Write a review')}
    </a>
  </div>
);

export default ClosedReviewSubmit;
