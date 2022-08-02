import React from 'react';
import parse from 'html-react-parser';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import RecentOrders from '../../../../../alshaya_bazaar_voice/js/src/myaccount/components/orders/recent-orders';
import { getFilteredProductAttributes } from '../../../../../js/utilities/display';

const OrderItems = (props) => {
  const { products, cancelled } = props;

  // Remove the lpn attribute from the product attribute.
  products.forEach((product, index) => {
    products[index].attributes = getFilteredProductAttributes(product);
  });

  return (
    <>
      {Object.values(products).map((product) => (
        <div className="order-item-row" key={`${product.sku}${cancelled ? '_cancelled' : ''}`}>
          <div className="order__product--image">
            { hasValue(product.image) && (
              <>
                { hasValue(cancelled) && (
                  <div className="image-wrapper">
                    {parse(product.image)}
                    <span>{Drupal.t('Cancelled')}</span>
                  </div>
                )}
                { !hasValue(cancelled) && (
                  parse(product.image)
                )}
              </>
            )}
          </div>

          <div>
            <div className="dark">{product.name}</div>
            { hasValue(product.attributes) && (
              <>
                {Object.values(product.attributes).map((attribute) => (
                  <div className="light attr-wrapper" key={`${attribute.label}_${attribute.value}`}>
                    {attribute.label}
                    :
                    {` ${attribute.value}`}
                  </div>
                ))}
              </>
            )}

            <div className="light">
              {Drupal.t('Item Code: @sku', { '@sku': product.sku })}
            </div>

            <div className="light">
              {Drupal.t('Quantity: @quantity', { '@quantity': product.ordered })}
            </div>

            { hasValue(product.free_gift_label) && (
              <div className="free-gift-label">{product.free_gift_label}</div>
            )}

            <div className="tablet-only">
              <div className="light">{Drupal.t('Unit price')}</div>
              <div className="dark">
                {parse(product.price)}
              </div>
            </div>

            <div className="mobile-only">
              { hasValue(product.total) && (
                <>
                  <div className="light">{Drupal.t('Total')}</div>
                  <div className={`dark ${hasValue(cancelled) ? 'cancelled-total-price' : ''}`}>
                    {parse(product.total)}
                  </div>
                </>
              )}

              { hasValue(product.bazaarvoice_link) && (
                <div className="myaccount-write-review" data-sku={product.parent_sku}>
                  <RecentOrders productId={product.parent_sku} />
                </div>
              )}
            </div>
          </div>

          <div className="desktop-only">
            <div className="light">{Drupal.t('Unit price')}</div>
            <div className="dark">
              {parse(product.price)}
            </div>
          </div>

          <div className="above-mobile blend">
            { hasValue(product.total) && (
              <>
                <div className="light">{Drupal.t('Total')}</div>
                <div className={`dark ${hasValue(cancelled) ? 'cancelled-total-price' : ''}`}>
                  {parse(product.total)}
                </div>
              </>
            )}
          </div>

          { hasValue(product.bazaarvoice_link) && (
            <div className="above-mobile user-review bazaarvoice-enable">
              <div className="myaccount-write-review" data-sku={product.parent_sku}>
                <RecentOrders productId={product.parent_sku} />
              </div>
            </div>
          )}
        </div>
      ))}
    </>
  );
};

export default OrderItems;
