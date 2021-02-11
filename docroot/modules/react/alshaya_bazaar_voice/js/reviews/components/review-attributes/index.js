import React from 'react';

const ReviewAttributes = ({
  ReviewAttributesData,
}) => {
  if (ReviewAttributesData !== undefined) {
    return (
      <div className="review-attributes">
        <div className="review-attributes-wrapper">
          {Object.keys(ReviewAttributesData).map((item) => (
            <div className="review-attributes-details" key={ReviewAttributesData[item].Id}>
              <span className="attribute-name">{`${ReviewAttributesData[item].DimensionLabel}: `}</span>
              <span className="attribute-value">{ReviewAttributesData[item].Value}</span>
            </div>
          ))}
        </div>
      </div>
    );
  }
  return (null);
};

export default ReviewAttributes;
