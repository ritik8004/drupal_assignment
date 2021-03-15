import React from 'react';
import { getLanguageCode } from '../../../../utilities/api/request';
import getStringMessage from '../../../../../../../js/utilities/strings';

const ClosedReviewSubmit = ({ destination }) => (
  <div className="button-wrapper">
    <a
      id="closed-review-submit"
      className="write-review-button"
      href={`/${getLanguageCode()}/user/login?destination=${destination}`}
    >
      {getStringMessage('write_a_review')}
    </a>
  </div>
);

export default ClosedReviewSubmit;
