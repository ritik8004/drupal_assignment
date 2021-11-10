import React from 'react';
import getStringMessage from '../../../../../../js/utilities/strings';

const UserReviewsDetails = ({
  totalReviewCount,
  helpfulVoteCount,
}) => {
  if (totalReviewCount !== undefined) {
    return (
      <>
        <div className="review-count">
          <div className="label">{getStringMessage('review')}</div>
          <div className="value">{totalReviewCount}</div>
        </div>
        <div className="review-vote">
          <div className="label">{getStringMessage('vote')}</div>
          <div className="value">{helpfulVoteCount}</div>
        </div>
      </>
    );
  }
  return (null);
};

export default UserReviewsDetails;
