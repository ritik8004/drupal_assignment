import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const OrderItems = (props) => {
  const { products, cancelled } = props;

  return (
    <>
      {Object.values(products).map((product) => (
        <div className="order-item-row" key={`${product.sku}${cancelled ? '_cancelled' : ''}`}>
          <ConditionalView condition={hasValue(cancelled) && hasValue(product.image)}>
            <div className="order__product--image">
              <div className="image-wrapper" dangerouslySetInnerHTML={{ __html: product.image }} />
              <span>{Drupal.t('Cancelled')}</span>
            </div>
          </ConditionalView>

          <ConditionalView condition={!hasValue(cancelled) && hasValue(product.image)}>
            <div className="order__product--image">
              <div className="image-wrapper" dangerouslySetInnerHTML={{ __html: product.image }} />
            </div>
          </ConditionalView>

          <div>
            <div className="dark">{product.name}</div>
            <ConditionalView condition={hasValue(product.attributes)}>
              {Object.values(product.attributes).map((attribute) => (
                <div className="light attr-wrapper" key={`${attribute.label}_${attribute.value}`}>
                  {/* @todo test order page in Arabic */}
                  {attribute.label}
                  :
                  {attribute.value}
                </div>
              ))}
            </ConditionalView>

            <div className="light">
              {Drupal.t('Item code: @sku', { '@sku': product.sku })}
            </div>

            <div className="light">
              {Drupal.t('Quantity: @qty', { '@qty': product.ordered })}
            </div>

            <ConditionalView condition={hasValue(product.free_gift_label)}>
              <div className="free-gift-label">{product.free_gift_label}</div>
            </ConditionalView>

            <div className="tablet-only">
              <div className="light">{Drupal.t('Unit price')}</div>
              <div className="dark" dangerouslySetInnerHTML={{ __html: product.price }} />
            </div>

            <div className="mobile-only">
              <ConditionalView condition={hasValue(product.total)}>
                <div className="light">{Drupal.t('Total')}</div>
                <ConditionalView condition={hasValue(cancelled)}>
                  <div className="dark cancelled-total-price" dangerouslySetInnerHTML={{ __html: product.total }} />
                </ConditionalView>
                <ConditionalView condition={!hasValue(cancelled)}>
                  <div className="dark" dangerouslySetInnerHTML={{ __html: product.total }} />
                </ConditionalView>
              </ConditionalView>

              <ConditionalView condition={hasValue(product.bazaarvoice_link)}>
                <div className="myaccount-write-review" data-sku={product.parent_sku} />
              </ConditionalView>
            </div>
          </div>

          <div className="desktop-only">
            <div className="light">{Drupal.t('Unit price')}</div>
            <div className="dark" dangerouslySetInnerHTML={{ __html: product.price }} />
          </div>

          <div className="above-mobile blend">
            <ConditionalView condition={hasValue(product.total)}>
              <div className="light">{Drupal.t('Total')}</div>
              <ConditionalView condition={hasValue(cancelled)}>
                <div className="dark cancelled-total-price" dangerouslySetInnerHTML={{ __html: product.total }} />
              </ConditionalView>
              <ConditionalView condition={!hasValue(cancelled)}>
                <div className="dark" dangerouslySetInnerHTML={{ __html: product.total }} />
              </ConditionalView>
            </ConditionalView>
          </div>

          <ConditionalView condition={hasValue(product.bazaarvoice_link)}>
            <div className="above-mobile user-review bazaarvoice-enable">
              <div className="myaccount-write-review" data-sku={product.parent_sku} />
            </div>
          </ConditionalView>
        </div>
      ))}
    </>
  );
};

export default OrderItems;
