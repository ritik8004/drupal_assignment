import React from 'react';
import { getLanguageCode } from '../../../../utilities/api/request';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { setStorageInfo } from '../../../../utilities/storage';

const ClosedReviewSubmit = ({
  destination,
}) => {
  function onClick() {
    // When user is getting redirected to the login page, at that point we set
    // this to true.
    // After the user logs in, sees the popup and closes it, then this value
    // will be false.
    setStorageInfo(true, 'openPopup');
  }

  return (
    <div className="button-wrapper">
      <a
        id="closed-review-submit"
        className="write-review-button"
        href={`/${getLanguageCode()}/user/login?destination=${destination}&openPopup=true`}
        onClick={() => { onClick(); }}
      >
        {getStringMessage('write_a_review')}
      </a>
    </div>
  );
};

export default ClosedReviewSubmit;
