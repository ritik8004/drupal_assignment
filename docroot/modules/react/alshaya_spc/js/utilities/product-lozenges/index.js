import React from 'react';

const ProductLozenges = ({ labels, sku }) => {
  if (typeof labels === 'undefined' || labels.length === 0) {
    return (null);
  }

  if (!(labels && Array.isArray(labels) && labels.length)) { return null; }

  const bifercatedLabels = labels.reduce((aggregator, item) => {
    const currentAggregation = { ...aggregator };
    const currentItem = { ...item };
    currentItem.position = currentItem.position || 'top-left';
    if (!(currentItem.position in currentAggregation)) {
      currentAggregation[currentItem.position] = [];
    }
    currentAggregation[currentItem.position].push(currentItem);
    return currentAggregation;
  }, {});

  const bifercatedLabelsList = Object.keys(bifercatedLabels);

  if (bifercatedLabelsList && Array.isArray(bifercatedLabelsList) && bifercatedLabelsList.length) {
    return (
      <>
        <div className="product-labels">
          <div className="labels-wrapper" data-type="plp" data-sku={sku} data-main-sku={sku}>
            {
              bifercatedLabelsList.map((key) => (
                <div className={`labels-container ${key}`} key={`${key}-label-container`}>
                  <LabelItems
                    bifercatedLabels={bifercatedLabels}
                    directionKey={key}
                  />
                </div>
              ))
            }
          </div>
        </div>
      </>
    );
  }
  return null;
};

const LabelItems = ({ bifercatedLabels, directionKey }) => (
  <>
    {
      bifercatedLabels[directionKey].map((labelItem) => (
        // BE to provide and add a unique key here.
        <div className="label testing" key={labelItem.image}>
          <img
            src={labelItem.image}
            alt={labelItem.text || ''}
            title={labelItem.text || ''}
            loading="lazy"
          />
        </div>
      ))
    }
  </>
);

export default ProductLozenges;
