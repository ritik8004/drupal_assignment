import React from 'react';

const ReviewAttributes = ({
  reviewAttributesData,
}) => {
  if (reviewAttributesData !== undefined) {
    return (
      <div className="review-attributes">
        <div className="review-attributes-wrapper">
          {Object.keys(reviewAttributesData).map((item) => (
            <div className="review-attributes-details" key={reviewAttributesData[item].Id}>
              <span className="attribute-name">{`${reviewAttributesData[item].DimensionLabel}: `}</span>
              <span className="attribute-value">{reviewAttributesData[item].Value}</span>
            </div>
          ))}
        </div>
      </div>
    );
  }
  return (null);
};

export default ReviewAttributes;
