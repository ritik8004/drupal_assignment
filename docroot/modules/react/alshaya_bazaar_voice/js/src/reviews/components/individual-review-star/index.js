import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import DisplayStar from '../../../rating/components/stars';

const IndividualReviewStar = ({
  customerValue,
  secondaryRatingsOrder,
}) => (
  <>
    <ConditionalView condition={secondaryRatingsOrder.length > 0}>
      {secondaryRatingsOrder.map((item) => (
        <ConditionalView key={customerValue[item].Id} condition={customerValue[item].DisplayType === 'NORMAL'}>
          <div className="secondary-star-container">
            <div className="overall-label">
              {customerValue[item].Label}
              {':'}
            </div>
            <DisplayStar
              starPercentage={
                (customerValue[item].Value > 0)
                  ? customerValue[item].Value
                  : (customerValue[item].AverageRating).toFixed(1)
              }
            />
            <div className="overall-rating">
              {
                (customerValue[item].Value > 0)
                  ? customerValue[item].Value
                  : (customerValue[item].AverageRating).toFixed(1)
              }
            </div>
          </div>
        </ConditionalView>
      ))}
    </ConditionalView>
  </>
);

export default IndividualReviewStar;
