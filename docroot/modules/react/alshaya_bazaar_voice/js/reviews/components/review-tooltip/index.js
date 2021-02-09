import React from 'react';

const ReviewTooltip = ({
  ReviewTooltipData,
  ReviewRelatedCount,
  ReviewContextData,
}) => {
  if (ReviewTooltipData !== undefined) {
    return (
      <div className="user-review-info">
        <div className="user-info">
          <div className="user-nickname">{ReviewTooltipData.UserNickname}</div>
          <div className="user-location">{ReviewTooltipData.UserLocation}</div>
        </div>
        <div className="user-review-wrapper">
          <div className="user-reviews-details">
            <div className="review-count">
              <div className="label">{Drupal.t('Review')}</div>
              <div className="value">{ReviewRelatedCount.TotalReviewCount}</div>
            </div>
            <div className="review-vote">
              <div className="label">{Drupal.t('Vote')}</div>
              <div className="value">{ReviewRelatedCount.HelpfulVoteCount}</div>
            </div>
          </div>
          <div className="user-personal-details">
            <div className="user-attributes">
              <span className="user-name">{`${ReviewTooltipData.UserNickname}:`}</span>
              <span className="user-attribute-value">{ReviewContextData.Age.Value}</span>
            </div>
            {(ReviewContextData.Gender !== undefined) ? (
              <div className="user-attributes">
                <span className="user-name">{`${ReviewContextData.Gender.DimensionLabel}: `}</span>
                <span className="user-attribute-value">{ReviewContextData.Gender.Value}</span>
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
