import React from 'react';
import DisplayStar from '../../../rating/components/stars/DisplayStar';

const IndividualReviewStar = ({
  customerValue,
}) => {
  if (customerValue === null) {
    return null;
  }
  const IndividualReviewDisplay = Object.keys(customerValue).map((item) => {
    if (customerValue[item].DisplayType === 'NORMAL' === true) {
      return ([
        <div key={item} className="secondary-star-container">
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
        </div>,
      ]);
    }
    return '';
  }, {});
  if (IndividualReviewDisplay && IndividualReviewDisplay.length > 0) {
    return IndividualReviewDisplay;
  }
  return (null);
};

export default IndividualReviewStar;
