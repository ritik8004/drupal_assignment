import React from 'react';

const CartNotification = (props) => {
  const { productInfo, productData } = props;
  const cartImage = productData.image;
  const cartTitle = productData.productName;
  const cartQuantity = productData.quantity;
  const skuCode = productData.parentSku;
  const path = drupalSettings.path.pathPrefix;
  let configurables = [];
  if (typeof productInfo[skuCode].variants !== 'undefined') {
    configurables = productInfo[skuCode].variants[productData.variants].configurableOptions;
  }

  return (
    <>
      <div className="notification">
        <div className="col-1">
          <img src={cartImage} alt={cartTitle} title={cartTitle} />
          <span className="qty">{Drupal.t('Qty')}</span>
          <span className="qty-value">{cartQuantity}</span>
        </div>
        <div className="col-2">
          <span className="name">{cartTitle}</span>
          <span>{Drupal.t('has been added to your basket.')}</span>
          {Object.keys(configurables).map(() => (
            <>
              <span>{configurables.label}</span>
              <span>{configurables.value}</span>
            </>
          ))}
          <a href={`/${path}cart`}>{Drupal.t('view basket')}</a>
        </div>
      </div>
    </>
  );
};
export default CartNotification;
