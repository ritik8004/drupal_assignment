import React from 'react';
import ReviewAttributes from '../review-attributes';
import ReviewTooltip from '../review-tooltip';
import ConditionalView from '../../../common/components/conditional-view';

const ReviewInformation = ({
  ReviewInformationData,
  ReviewTooltipInfo,
}) => {
  if (ReviewInformationData !== undefined) {
    const date = new Date(ReviewInformationData.SubmissionTime);
    return (
      <div className="review-detail-left">
        <div className="review-user-details">
          <div className="review-tooltip">
            <span className="user-detail-nickname">{ReviewInformationData.UserNickname}</span>

            <ConditionalView condition={window.innerWidth < 768}>
              <div className="review-detail-mobile">
                <span className="review-date">{`${date.toLocaleString('default', { month: 'short' })} ${date.getDate()}, ${date.getFullYear()}`}</span>

                <ConditionalView condition={ReviewInformationData.UserLocation !== null}>
                  <span className="user-detail-location">{ReviewInformationData.UserLocation}</span>
                </ConditionalView>

              </div>
            </ConditionalView>

            <ReviewTooltip
              ReviewTooltipData={ReviewInformationData}
              ReviewRelatedCount={ReviewTooltipInfo}
              ReviewContextData={ReviewInformationData.ContextDataValues}
            />
          </div>

          <ConditionalView condition={window.innerWidth > 767}>
            <div className="user-detail-location">{ReviewInformationData.UserLocation}</div>
          </ConditionalView>

        </div>

        <ConditionalView condition={window.innerWidth > 767}>
          <div className="horizontal-border" />
        </ConditionalView>

        <ReviewAttributes
          ReviewAttributesData={ReviewInformationData.ContextDataValues}
        />
      </div>
    );
  }
  return (null);
};

export default ReviewInformation;
