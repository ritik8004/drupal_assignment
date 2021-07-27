import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';

const ReviewAttributes = ({
  contextDataValues,
  contextDataValuesOrder,
  showLocationFilter,
}) => {
  if (contextDataValuesOrder.length > 0) {
    return (
      <div className="review-attributes">
        <div className="review-attributes-wrapper">
          {contextDataValuesOrder.map((item) => (
            <ConditionalView key={contextDataValues[item].Id} condition={contextDataValues[item].Id !== 'location_filter' || showLocationFilter}>
              <div className="review-attributes-details" key={contextDataValues[item].Id}>
                <span className="attribute-name">{`${contextDataValues[item].DimensionLabel}: `}</span>
                <span className="attribute-value">{contextDataValues[item].ValueLabel}</span>
              </div>
            </ConditionalView>
          ))}
        </div>
      </div>
    );
  }
  return (null);
};

export default ReviewAttributes;
