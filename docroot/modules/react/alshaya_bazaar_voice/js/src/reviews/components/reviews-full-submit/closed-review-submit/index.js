import React from 'react';
import { getLanguageCode } from '../../../../utilities/api/request';

const ClosedReviewSubmit = ({ destination }) => (
  <div>
    <a
      id="closed-review-submit"
      className="closed-review-submit"
      href={`/${getLanguageCode()}/user/login?destination=${destination}`}
    >
      {Drupal.t('Write a review')}
    </a>
  </div>
);

export default ClosedReviewSubmit;
