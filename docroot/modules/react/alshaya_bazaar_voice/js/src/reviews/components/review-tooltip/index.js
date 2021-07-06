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
        {
          (age !== '' && gender !== '')
            ? (
              <div className="user-info">
                <UserPersonalDetails
                  userNickname={reviewTooltipData.UserNickname}
                  userAge={age}
                  userGender={gender}
                />
              </div>
            )
            : null
         }
        <div className="user-review-wrapper">
          <UserReviewsDetails
            totalReviewCount={reviewRelatedCount.TotalReviewCount}
            helpfulVoteCount={reviewRelatedCount.HelpfulVoteCount}
          />
        </div>
      </div>
    );
  }
  return (null);
};

export default ReviewTooltip;
