import React from 'react';
import parse from 'html-react-parser';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import Price from '../../../../../js/utilities/components/price';

const ReturnIndividualItem = ({
  item,
}) => {
  const eligibleClass = item.is_returnable ? 'return-eligible' : 'in-eligible';
  const bigTicketClass = item.is_big_ticket ? 'big-ticket-item' : '';
  let itemQuantity = item.qty_ordered;
  // For returned items data, we process return data
  // else we process order items data.
  if (item.returnData) {
    itemQuantity = item.returnData.qty_returned !== null
      ? item.returnData.qty_returned : item.returnData.qty_requested;
  }
  // For refunded items if any, update the itemQuantity.
  if (item.qty_refunded < item.qty_ordered) {
    itemQuantity -= item.qty_refunded;
  }

  const {
    url: imageUrl,
    alt: imageAlt,
    title: imageTitle,
  } = item.image_data || {};

  const {
    qty_ordered: qtyOrdered,
    qty_refunded: qtyRefunded,
  } = item;

  return (
    <>
      <ConditionalView condition={hasValue(imageUrl) && qtyRefunded < qtyOrdered}>
        <div className="order-item-image">
          <div className={`image-data-wrapper ${eligibleClass} ${bigTicketClass}`}>
            <img src={`${imageUrl}`} alt={`${imageAlt}`} title={`${imageTitle}`} />
            <ConditionalView condition={item.is_big_ticket}>
              <div className="big-ticket-item-label">{Drupal.t('Big Ticket Item', {}, { context: 'online_returns' })}</div>
            </ConditionalView>
            <ConditionalView condition={!item.is_big_ticket && !item.is_returnable}>
              <div className="not-eligible-label">{ Drupal.t('Ineligible for return', {}, { context: 'online_returns' }) }</div>
            </ConditionalView>
          </div>
        </div>
      </ConditionalView>
      <div className="order__details--wrapper">
        <div className="order__details--summary order__details--description">
          <div className="item-name dark">{ item.name }</div>
          {item.attributes && Object.keys(item.attributes).map((attribute) => (
            <div key={item.attributes[attribute].label} className="attribute-detail light">
              { Drupal.t('@attrLabel: @attrValue', { '@attrLabel': item.attributes[attribute].label, '@attrValue': item.attributes[attribute].value }) }
            </div>
          ))}
          <div className="item-code light">
            { Drupal.t('Item Code: @sku', { '@sku': item.sku }, { context: 'online_returns' }) }
          </div>
          <div className="item-quantity light">
            { Drupal.t('Quantity: @quantity', { '@quantity': itemQuantity }, { context: 'online_returns' }) }
          </div>
        </div>

        <ConditionalView condition={window.innerWidth > 767}>
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

        <ConditionalView condition={window.innerWidth < 768}>
          <div className="item-total-price">
            <div className="light">{Drupal.t('Total', {}, { context: 'online_returns' })}</div>
            <span className="dark">{ parse(item.total) }</span>
          </div>
        </ConditionalView>
      </div>

      <ConditionalView condition={window.innerWidth > 767}>
        <div className="item-total-price">
          <div className="light">{Drupal.t('Total', {}, { context: 'online_returns' })}</div>
          <span className="dark">{ parse(item.total) }</span>
        </div>
      </ConditionalView>

      <ConditionalView condition={item.is_big_ticket}>
        <div className="big-ticket-wrapper">{Drupal.t('Kindly contact customer care for initiating the online returns for Big Ticket Items.', {}, { context: 'online_returns' })}</div>
      </ConditionalView>
    </>
  );
};

export default ReturnIndividualItem;
