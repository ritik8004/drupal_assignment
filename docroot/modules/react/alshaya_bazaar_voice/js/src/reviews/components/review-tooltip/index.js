import React from 'react';
import UserReviewsDetails from '../user-reviews-details';
import UserPersonalDetails from '../user-personal-details';

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
          <UserReviewsDetails
            totalReviewCount={reviewRelatedCount.TotalReviewCount}
            helpfulVoteCount={reviewRelatedCount.HelpfulVoteCount}
          />
          <UserPersonalDetails
            userNickname={reviewTooltipData.UserNickname}
            userAgeValue={reviewContextData.Age.Value}
            userGender={reviewContextData.Gender}
          />
        </div>
      </div>
    );
  }
  return (null);
};

export default ReviewTooltip;
