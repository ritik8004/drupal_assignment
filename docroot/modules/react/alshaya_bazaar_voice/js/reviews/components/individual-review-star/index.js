import React from 'react';
import DisplayStar from '../../../rating/components/stars/DisplayStar';

const IndividualReviewStar = ({
  customerValue,
}) => {
  if (customerValue === null) {
    return null;
  }
  return (
    <>
      {Object.keys(customerValue).map((item) => (((customerValue[item].DisplayType) === 'NORMAL') === true
        ? (
          <div key={item} className="secondary-star-container">
            <div className="overall-label">
              {customerValue[item].Label}
              {':'}
            </div>
            <DisplayStar
              starPercentage={
                (customerValue[item].Value > 0)
                  ? customerValue[item].Value
                  : customerValue[item].AverageRating
              }
            />
            <div className="overall-rating">
              {
                (customerValue[item].Value > 0)
                  ? customerValue[item].Value
                  : customerValue[item].AverageRating
              }
            </div>
          </div>
        )
        : null))}
    </>
  );
};

export default IndividualReviewStar;
