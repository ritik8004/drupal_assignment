import React from 'react';
import { getLanguageCode } from '../../../../utilities/api/request';
import getStringMessage from '../../../../../../../js/utilities/strings';

const ClosedReviewSubmit = ({ destination }) => (
  <div>
    <a
      id="closed-review-submit"
      className="closed-review-submit"
      href={`/${getLanguageCode()}/user/login?destination=${destination}`}
    >
      {getStringMessage('write_a_review')}
    </a>
  </div>
);

export default ClosedReviewSubmit;
