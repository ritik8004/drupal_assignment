import React from 'react';
import UserReviewsDetails from '../user-reviews-details';
import UserPersonalDetails from '../user-personal-details';

const ReviewTooltip = ({
  reviewTooltipData,
  reviewRelatedCount,
  reviewContextData,
}) => {
  if (reviewTooltipData !== undefined) {
    let age = '';
    let gender = '';
    if (reviewContextData.Age !== undefined || reviewContextData.Age_filter !== undefined) {
      age = (reviewContextData.Age !== undefined)
        ? reviewContextData.Age : reviewContextData.Age_filter;
    }
    if (reviewContextData.Gender !== undefined || reviewContextData.Gender_filter !== undefined) {
      gender = (reviewContextData.Gender !== undefined)
        ? reviewContextData.Gender : reviewContextData.Gender_filter;
    }
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
            userAge={age}
            userGender={gender}
          />
        </div>
      </div>
    );
  }
  return (null);
};

export default ReviewTooltip;
