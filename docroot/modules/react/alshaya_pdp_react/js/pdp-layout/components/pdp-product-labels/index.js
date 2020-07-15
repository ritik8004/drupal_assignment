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

  return (
    <>
      <div className="product-labels">
        <div className="labels-container" dataSku={variantSelected} dataMainSku={skuCode}>
          {Object.keys(labels).map((key) => (
            <div className={`labels ${labels[key].position}`}>
              <img
                src={labels[key].image.url}
                alt={labels[key].image.alt}
                title={labels[key].image.title}
              />
            </div>
          ))}
        </div>
      </div>
    </>
  );
};

export default PdpProductLabels;
