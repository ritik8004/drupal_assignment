import React from 'react';
import parse from 'html-react-parser';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const OrderItems = (props) => {
  const { products, cancelled } = props;

  return (
    <>
      {Object.values(products).map((product) => (
        <div className="order-item-row" key={`${product.sku}${cancelled ? '_cancelled' : ''}`}>
          { hasValue(cancelled) && hasValue(product.image) && (
            <div className="order__product--image">
              <div className="image-wrapper">
                {parse(product.image)}
                <span>{Drupal.t('Cancelled')}</span>
              </div>
            </div>
          )}

          { !hasValue(cancelled) && hasValue(product.image) && (
            <div className="order__product--image">
              <div className="image-wrapper">
                {parse(product.image)}
              </div>
            </div>
          )}

          <div>
            <div className="dark">{product.name}</div>
            { hasValue(product.attributes) && (
              <>
                {Object.values(product.attributes).map((attribute) => (
                  <div className="light attr-wrapper" key={`${attribute.label}_${attribute.value}`}>
                    {/* @todo test order page in Arabic */}
                    {attribute.label}
                    :
                    {attribute.value}
                  </div>
                ))}
              </>
            )}

            <div className="light">
              {Drupal.t('Item code: @sku', { '@sku': product.sku })}
            </div>

            <div className="light">
              {Drupal.t('Quantity: @qty', { '@qty': product.ordered })}
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
                  { hasValue(cancelled) && (
                    <div className="dark cancelled-total-price">
                      {parse(product.total)}
                    </div>
                  )}
                  { !hasValue(cancelled) && (
                    <div className="dark">
                      {parse(product.total)}
                    </div>
                  )}
                </>
              )}

              { hasValue(product.bazaarvoice_link) && (
                <div className="myaccount-write-review" data-sku={product.parent_sku} />
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
            {/* @todo This is repeated. Improve logic to add a simple class */}
            { hasValue(product.total) && (
              <>
                <div className="light">{Drupal.t('Total')}</div>
                { hasValue(cancelled) && (
                  <div className="dark cancelled-total-price">
                    {parse(product.total)}
                  </div>
                )}
                { !hasValue(cancelled) && (
                  <div className="dark">
                    {parse(product.total)}
                  </div>
                )}
              </>
            )}
          </div>

          { hasValue(product.bazaarvoice_link) && (
            <div className="above-mobile user-review bazaarvoice-enable">
              <div className="myaccount-write-review" data-sku={product.parent_sku} />
            </div>
          )}
        </div>
      ))}
    </>
  );
};

export default OrderItems;
