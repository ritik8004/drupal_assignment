import React from 'react';

const PdpProductLabels = (props) => {
  const { productLabels } = drupalSettings;
  const { configurableCombinations } = drupalSettings;
  const { skuCode } = props;
  let variantSelected = skuCode;
  let labels = productLabels[skuCode];

  // For configurable products.
  if (document.getElementById('pdp-add-to-cart-form') && configurableCombinations) {
    variantSelected = document.getElementById('pdp-add-to-cart-form').getAttribute('variantselected');
    labels = productLabels[variantSelected];
  }

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
          <div className="labels-container" dataSku={variantSelected} dataMainSku={skuCode}>
            {
              bifercatedLabelsList.map((key) => (
                <div className={`labels-container__inner labels-container__inner--${key}`} key={`${key}-label-container`}>
                  <PdpProductLabel bifercatedLabels={bifercatedLabels} directionKey={key} />
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

const PdpProductLabel = (props) => {
  const { bifercatedLabels, directionKey } = props;
  return (
    <>
      {
        bifercatedLabels[directionKey].map((labelItem) => (
          // BE to provide and add a unique key here.
          <div className={`labels ${labelItem.position}`}>
            <img
              src={labelItem.image.url || ''}
              alt={labelItem.image.alt || ''}
              title={labelItem.image.title || ''}
            />
          </div>
        ))
      }
    </>
  );
};

export default PdpProductLabels;
