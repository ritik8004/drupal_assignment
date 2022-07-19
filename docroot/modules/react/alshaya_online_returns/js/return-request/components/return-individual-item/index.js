import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import Price from '../../../../../js/utilities/components/price';
import PriceElement from '../../../../../js/utilities/components/price/price-element';
import { getFilteredProductAttributes, isMobile } from '../../../../../js/utilities/display';

const ReturnIndividualItem = ({
  item,
}) => {
  // Filter out all the lpn attributes.
  const productAttributes = getFilteredProductAttributes(item);

  const eligibleClass = item.is_returnable ? 'return-eligible' : 'in-eligible';
  const bigTicketClass = item.is_big_ticket ? 'big-ticket-item' : '';
  let itemQuantity = item.qty_ordered;
  // For returned items data, we process return data
  // else we process order items data.
  if (item.returnData) {
    itemQuantity = item.returnData.qty_returned !== null
      ? item.returnData.qty_returned : item.returnData.qty_requested;

    // Update the itemQuantity if the item is a rejected item.
    itemQuantity = item.returnData.extension_attributes.qty_rejected != null
      ? item.returnData.extension_attributes.qty_rejected
      : itemQuantity;
  }

  // Return null if itemQuantity is not valid.
  if (itemQuantity <= 0) {
    return null;
  }

  const {
    url: imageUrl,
    alt: imageAlt,
    title: imageTitle,
  } = item.image_data || {};

  const {
    price_incl_tax: priceIncTax,
  } = item;

  let reasonDescription = [];
  if (hasValue(item.returnData)
    && hasValue(item.returnData.extension_attributes.reason_description)) {
    reasonDescription = item.returnData.extension_attributes.reason_description;
  }

  return (
    <>
      <ConditionalView condition={hasValue(imageUrl)}>
        <div className="order-item-image">
          <div className={`image-data-wrapper ${eligibleClass} ${bigTicketClass}`}>
            <img src={`${imageUrl}`} alt={`${imageAlt}`} title={`${imageTitle}`} />
            <ConditionalView condition={item.is_big_ticket}>
              <div className="big-ticket-item-label">{Drupal.t('Big Ticket Item', {}, { context: 'online_returns' })}</div>
            </ConditionalView>
            <ConditionalView condition={!item.is_big_ticket && !item.is_returnable}>
              <div className="not-eligible-label">{Drupal.t('Ineligible for return', {}, { context: 'online_returns' })}</div>
            </ConditionalView>
          </div>
        </div>
      </ConditionalView>
      <div className="order__details--wrapper">
        <div className="order__details--summary order__details--description">
          <div className="item-name dark">{item.name}</div>
          {productAttributes && Object.keys(productAttributes).map((attribute) => (
            <div key={productAttributes[attribute].label} className="attribute-detail light">
              {Drupal.t('@attrLabel: @attrValue', { '@attrLabel': productAttributes[attribute].label, '@attrValue': productAttributes[attribute].value })}
            </div>
          ))}
          <div className="item-code light">
            {Drupal.t('Item Code: @sku', { '@sku': item.sku })}
          </div>
          <div className="item-quantity light">
            {Drupal.t('Quantity: @quantity', { '@quantity': itemQuantity })}
          </div>
        </div>

        <ConditionalView condition={!isMobile()}>
          <div className="item-price">
            <div className="light">{Drupal.t('Unit Price', {}, { context: 'online_returns' })}</div>
            <div className="dark">
              <Price
                price={item.original_price.toString()}
                finalPrice={item.price_incl_tax.toString()}
              />
            </div>
          </div>
        </ConditionalView>

        <ConditionalView condition={isMobile()}>
          <div className="item-total-price">
            <div className="light">{Drupal.t('Total', {}, { context: 'online_returns' })}</div>
            <span className="dark"><PriceElement amount={itemQuantity * priceIncTax} /></span>
          </div>
        </ConditionalView>

        <ConditionalView condition={isMobile()}>
          {reasonDescription.length > 0 && (
            <div className="cancellation-reason">
              {reasonDescription.map((returnReason) => (
                <div className="reason-wrapper">
                  <span className="reason-label">{returnReason.reason_description}</span>
                  {reasonDescription.length > 1 && (
                    <div>
                      <span> - </span>
                      <span>{`${returnReason.qty} `}</span>
                    </div>
                  )}
                  {reasonDescription.length > 1 && (returnReason.qty > 1
                    ? Drupal.t('items', {}, { context: 'online_returns' })
                    : Drupal.t('item', {}, { context: 'online_returns' })
                  )}
                </div>
              ))}
            </div>
          )}
        </ConditionalView>
      </div>

      <ConditionalView condition={!isMobile()}>
        <div className="item-total-price">
          <div className="light">{Drupal.t('Total', {}, { context: 'online_returns' })}</div>
          <span className="dark"><PriceElement amount={itemQuantity * priceIncTax} /></span>
        </div>
      </ConditionalView>

      <ConditionalView condition={item.is_big_ticket}>
        <div className="big-ticket-wrapper">{Drupal.t('Kindly contact customer care for initiating the online returns for Big Ticket Items.', {}, { context: 'online_returns' })}</div>
      </ConditionalView>

      <ConditionalView condition={!isMobile()}>
        {reasonDescription.length > 0 && (
          <div className="cancellation-reason">
            {reasonDescription.map((returnReason) => (
              <div className="reason-wrapper">
                <span className="reason-label">{returnReason.reason_description}</span>
                {reasonDescription.length > 1 && (
                  <div>
                    <span> - </span>
                    <span>{`${returnReason.qty} `}</span>
                  </div>
                )}
                {reasonDescription.length > 1 && (returnReason.qty > 1
                  ? Drupal.t('items', {}, { context: 'online_returns' })
                  : Drupal.t('item', {}, { context: 'online_returns' })
                )}
              </div>
            ))}
          </div>
        )}
      </ConditionalView>
    </>
  );
};

export default ReturnIndividualItem;
