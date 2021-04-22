import React from 'react';

const ReviewAttributes = ({
  contextDataValues,
  contextDataValuesOrder,
}) => {
  if (contextDataValuesOrder.length > 0) {
    return (
      <div className="review-attributes">
        <div className="review-attributes-wrapper">
          {contextDataValuesOrder.map((item) => (
            <div className="review-attributes-details" key={contextDataValues[item].Id}>
              <span className="attribute-name">{`${contextDataValues[item].DimensionLabel}: `}</span>
              <span className="attribute-value">{contextDataValues[item].ValueLabel}</span>
            </div>
          ))}
        </div>
      </div>
    );
  }
  return (null);
};

export default ReviewAttributes;
