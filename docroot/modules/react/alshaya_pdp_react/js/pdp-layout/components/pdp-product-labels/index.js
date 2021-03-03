import React from 'react';

const PdpProductLabels = ({
  labels, skuCode, variantSelected, context,
}) => {
  if (!(labels && Array.isArray(labels) && labels.length)) return null;

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
          <div className="labels-container" datasku={variantSelected} datamainsku={skuCode}>
            {
              bifercatedLabelsList.map((key) => (
                <div className={`labels-container__inner labels-container__inner--${key}`} key={`${key}-label-container`}>
                  <PdpProductLabel
                    bifercatedLabels={bifercatedLabels}
                    directionKey={key}
                    context={context}
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

const PdpProductLabel = ({
  bifercatedLabels, directionKey, context,
}) => (
  <>
    {
        bifercatedLabels[directionKey].map((labelItem) => (
          // BE to provide and add a unique key here.
          <div className="labels" key={labelItem}>
            <img
              src={(context === 'main') ? labelItem.image.url : labelItem.image}
              alt={labelItem.image.alt || ''}
              title={labelItem.image.title || ''}
            />
          </div>
        ))
      }
  </>
);

export default PdpProductLabels;
