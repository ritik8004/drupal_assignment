import React from 'react';
import ReviewAttributes from '../review-attributes';
import ReviewTooltip from '../review-tooltip';
import ConditionalView from '../../../common/components/conditional-view';
import IndividualReviewSlider from '../individual-review-slider';
import IndividualReviewStar from '../individual-review-star';
import { getDate } from '../../../../../../js/utilities/dateUtility';
import { getLanguageCode } from '../../../utilities/api/request';

const ReviewInformation = ({
  reviewInformationData,
  reviewTooltipInfo,
}) => {
  if (reviewInformationData !== undefined) {
    const date = getDate(reviewInformationData.SubmissionTime, getLanguageCode());
    return (
      <div className="review-detail-left">
        <div className="review-user-details">
          <div className="review-tooltip">
            <span className="user-detail-nickname">{reviewInformationData.UserNickname}</span>

            <ConditionalView condition={window.innerWidth < 768}>
              <div className="review-detail-mobile">
                <span className="review-date">{`${date}`}</span>

                <ConditionalView condition={reviewInformationData.UserLocation !== null}>
                  <span className="user-detail-location">{reviewInformationData.UserLocation}</span>
                </ConditionalView>

              </div>
            </ConditionalView>

            <ReviewTooltip
              reviewTooltipData={reviewInformationData}
              reviewRelatedCount={reviewTooltipInfo}
              reviewContextData={reviewInformationData.ContextDataValues}
            />
          </div>

          <ConditionalView condition={window.innerWidth > 767}>
            <div className="user-detail-location">{reviewInformationData.UserLocation}</div>
          </ConditionalView>

        </div>

        <ConditionalView condition={window.innerWidth > 767}>
          <div className="horizontal-border" />
        </ConditionalView>

        <ReviewAttributes
          contextDataValues={reviewInformationData.ContextDataValues}
          contextDataValuesOrder={reviewInformationData.ContextDataValuesOrder}
        />

        <IndividualReviewSlider
          sliderData={reviewInformationData.SecondaryRatings}
          secondaryRatingsOrder={reviewInformationData.SecondaryRatingsOrder}
        />

        <IndividualReviewStar
          customerValue={reviewInformationData.SecondaryRatings}
          secondaryRatingsOrder={reviewInformationData.SecondaryRatingsOrder}
        />

      </div>
    );
  }
  return (null);
};

export default ReviewInformation;
