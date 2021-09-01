import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';

function concatTagValues(val) {
  return val === undefined ? '' : val.join(', ').trim();
}

const ReviewAdditionalAttributes = ({
  additionalFieldsData,
  additionalFieldsOrder,
  tagDimensionsData,
  tagDimensionsOrder,
}) => (
  <>
    <ConditionalView condition={additionalFieldsOrder.length > 0}>
      {additionalFieldsOrder.map((item) => (
        <div key={additionalFieldsData[item].Id}>
          <div className="review-textarea-attributes review-attributes-details">
            <div className="review-textarea-label attribute-name">{`${additionalFieldsData[item].Label}:`}</div>
            <div className="review-textarea-value attribute-value">{additionalFieldsData[item].Value}</div>
          </div>
        </div>
      ))}
    </ConditionalView>
    <ConditionalView condition={tagDimensionsOrder.length > 0}>
      {tagDimensionsOrder.map((item) => (
        <div key={tagDimensionsData[item].Id}>
          <div className="review-attributes-details">
            <span className="attribute-name">{`${tagDimensionsData[item].Label}: `}</span>
            <span className="attribute-value">{concatTagValues(tagDimensionsData[item].Values)}</span>
          </div>
        </div>
      ))}
    </ConditionalView>
  </>
);

export default ReviewAdditionalAttributes;
