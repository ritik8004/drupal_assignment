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

  const bifercatedLabels = labels.reduce(function(aggregator, item) {
    item.position = item.position || 'top-left'
    if (!aggregator.hasOwnProperty(item.position)) {
      aggregator[item.position] = [];
    }
    aggregator[item.position].push(item)
    return aggregator;
  }, {});

  let bifercatedLabelsList = Object.keys(bifercatedLabels);

  return (
    <>
      <div className="product-labels">
        <div className="labels-container" dataSku={variantSelected} dataMainSku={skuCode}>
          {
            bifercatedLabelsList.map((key, index) => (
              <div className={`labels-container__inner labels-container__inner--${key}`} key={`${key}-${index}`}>
                <PdpProductLabel bifercatedLabels={bifercatedLabels} directionKey={key}/>
              </div>
            ))
          }
        </div>
      </div>
    </>
  );
};

const PdpProductLabel = (props) => {
  const { bifercatedLabels, directionKey } = props;
  return (
    <>
      {
        bifercatedLabels[directionKey].map((labelItem, index) => (
          <div className={`labels ${labelItem.position}`} key={`${directionKey}-${labelItem.position}-${index}`}>
            <img
              src={labelItem.image.url || ""}
              alt={labelItem.image.alt || ""}
              title={labelItem.image.title || ""}
            />
          </div>
        ))
      }
    </>
  );
};

export default PdpProductLabels;
