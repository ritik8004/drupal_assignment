import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';

function concatTagValues(val) {
  return val === undefined ? '' : val.join(',');
}

const ReviewAdditionalAttributes = ({
  reviewAdditionalAttributesData,
  includes,
}) => {
  if (reviewAdditionalAttributesData !== undefined) {
    return (
      <>
        {Object.keys(reviewAdditionalAttributesData).map((item) => (
          <div key={reviewAdditionalAttributesData[item].Id}>
            <ConditionalView condition={includes !== undefined}>
              <ConditionalView condition={item.includes('_textarea')}>
                <div className="review-textarea-attributes review-attributes-details">
                  <div className="review-textarea-label attribute-name">{`${reviewAdditionalAttributesData[item].Label}:`}</div>
                  <div className="review-textarea-value attribute-value">{reviewAdditionalAttributesData[item].Value}</div>
                </div>
              </ConditionalView>
            </ConditionalView>

            <ConditionalView condition={includes === undefined}>
              <div className="review-attributes-details">
                <span className="attribute-name">{`${reviewAdditionalAttributesData[item].Label}: `}</span>
                <span className="attribute-value">{concatTagValues(reviewAdditionalAttributesData[item].Values)}</span>
              </div>
            </ConditionalView>
          </div>
        ))}
      </>
    );
  }
  return (null);
};

export default ReviewAdditionalAttributes;
