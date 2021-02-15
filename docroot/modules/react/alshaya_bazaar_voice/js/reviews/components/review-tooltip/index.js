import React from 'react';

const ReviewTooltip = ({
  reviewTooltipData,
  reviewRelatedCount,
  reviewContextData,
}) => {
  if (reviewTooltipData !== undefined) {
    return (
      <div className="user-review-info">
        <div className="user-info">
          <div className="user-nickname">{reviewTooltipData.UserNickname}</div>
          <div className="user-location">{reviewTooltipData.UserLocation}</div>
        </div>
        <div className="user-review-wrapper">
          <div className="user-reviews-details">
            <div className="review-count">
              <div className="label">{Drupal.t('Review')}</div>
              <div className="value">{reviewRelatedCount.TotalReviewCount}</div>
            </div>
            <div className="review-vote">
              <div className="label">{Drupal.t('Vote')}</div>
              <div className="value">{reviewRelatedCount.HelpfulVoteCount}</div>
            </div>
          </div>
          <div className="user-personal-details">
            <div className="user-attributes">
              <span className="user-name">{`${reviewTooltipData.UserNickname}:`}</span>
              <span className="user-attribute-value">{reviewContextData.Age.Value}</span>
            </div>
            {(reviewContextData.Gender !== undefined) ? (
              <div className="user-attributes">
                <span className="user-name">{`${reviewContextData.Gender.DimensionLabel}: `}</span>
                <span className="user-attribute-value">{reviewContextData.Gender.Value}</span>
              </div>
            ) : null}
          </div>
        </div>
      </div>
    );
  }
  return (null);
};

export default ReviewTooltip;
