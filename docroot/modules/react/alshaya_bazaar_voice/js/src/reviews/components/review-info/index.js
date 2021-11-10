import React from 'react';
import ReviewAttributes from '../review-attributes';
import ReviewTooltip from '../review-tooltip';
import ConditionalView from '../../../common/components/conditional-view';
import IndividualReviewSlider from '../individual-review-slider';
import IndividualReviewStar from '../individual-review-star';
import { getDate } from '../../../../../../js/utilities/dateUtility';
import { getLanguageCode } from '../../../utilities/api/request';
import getStringMessage from '../../../../../../js/utilities/strings';

const ReviewInformation = ({
  reviewInformationData,
  reviewTooltipInfo,
  isNewPdpLayout,
  showLocationFilter,
}) => {
  let newPdp = isNewPdpLayout;
  newPdp = (newPdp === undefined) ? false : newPdp;

  if (reviewInformationData !== undefined) {
    const date = getDate(reviewInformationData.SubmissionTime, getLanguageCode());
    return (
      <div className="review-detail-left">
        <div className="review-user-details">
          <div className="review-tooltip">
            <span className="user-detail-nickname">{reviewInformationData.UserNickname}</span>

            <ConditionalView condition={(window.innerWidth < 768) || newPdp}>
              <div className="review-detail-mobile">
                <span id={`${reviewInformationData.Id}-review-date`} className="review-date">{`${date}`}</span>

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

          <ConditionalView condition={(window.innerWidth > 767) && (!newPdp)}>
            <div className="user-detail-location">{reviewInformationData.UserLocation}</div>
          </ConditionalView>

        </div>

        <ConditionalView condition={(window.innerWidth > 767) && (!newPdp)}>
          <div className="horizontal-border" />
        </ConditionalView>

        <ConditionalView condition={reviewInformationData.Badges}>
          {Object.keys(reviewInformationData.Badges).map((key) => (
            <div className="badges-container" key={key}>
              <ConditionalView condition={key.includes('top')}>
                <div className={`${key.replace(/[0-9]/g, '')}-contributor`}>
                  <span>{`${getStringMessage('top')}${key.replace('top', ' ')}${' '}${getStringMessage('contributor')}`}</span>
                </div>
              </ConditionalView>
              <ConditionalView condition={!key.includes('top')}>
                <div className={key.replace(' ', '-')}>
                  <span>{getStringMessage(key)}</span>
                </div>
              </ConditionalView>
            </div>
          ))}
        </ConditionalView>
        <ReviewAttributes
          contextDataValues={reviewInformationData.ContextDataValues}
          contextDataValuesOrder={reviewInformationData.ContextDataValuesOrder}
          showLocationFilter={showLocationFilter}
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
