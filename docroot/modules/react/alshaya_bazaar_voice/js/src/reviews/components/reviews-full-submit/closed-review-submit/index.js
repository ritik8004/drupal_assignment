import React from 'react';
import { getLanguageCode } from '../../../../utilities/api/request';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { setStorageInfo } from '../../../../utilities/storage';

const ClosedReviewSubmit = ({
  destination,
}) => {
  // Enable write review popup onload.
  setStorageInfo(true, 'openPopup');
  return (
    <div className="button-wrapper">
      <a
        id="closed-review-submit"
        className="write-review-button"
        href={`/${getLanguageCode()}/user/login?destination=${destination}&openPopup=true`}
      >
        {getStringMessage('write_a_review')}
      </a>
    </div>
  );
};

export default ClosedReviewSubmit;
