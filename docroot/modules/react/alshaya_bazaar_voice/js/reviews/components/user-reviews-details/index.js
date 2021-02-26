import React from 'react';

const UserReviewsDetails = ({
  totalReviewCount,
  helpfulVoteCount,
}) => {
  if (totalReviewCount !== undefined) {
    return (
      <div className="user-reviews-details">
        <div className="review-count">
          <div className="label">{Drupal.t('Review')}</div>
          <div className="value">{totalReviewCount}</div>
        </div>
        <div className="review-vote">
          <div className="label">{Drupal.t('Vote')}</div>
          <div className="value">{helpfulVoteCount}</div>
        </div>
      </div>
    );
  }
  return (null);
};

export default UserReviewsDetails;
