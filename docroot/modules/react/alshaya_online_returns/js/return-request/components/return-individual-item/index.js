import React from 'react';
import parse from 'html-react-parser';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

const ReturnIndividualItem = ({
  item,
}) => {
  const eligibleClass = item.is_returnable ? 'return-eligible' : 'in-eligible';
  return (
    <div className="order-item-detail">
      <ConditionalView condition={item.is_big_ticket}>
        <span>{Drupal.t('Large Item')}</span>
      </ConditionalView>
      <ConditionalView condition={item.image_data}>
        <div className="order-item-image">
          <div className={`image-data-wrapper ${eligibleClass}`}>
            <img src={`${item.image_data.url}`} alt={`${item.image_data.alt}`} title={`${item.image_data.title}`} />
            <ConditionalView condition={!item.is_returnable}>
              <div className="not-eligible-label">{ Drupal.t('Not eligible for return') }</div>
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
            { Drupal.t('Item Code: @sku', { '@sku': item.sku }) }
          </div>
          <div className="item-quantity light">
            { Drupal.t('Quantity: @quantity', { '@quantity': item.ordered }) }
          </div>
        </div>
        <div className="item-price">
          <div className="light">{Drupal.t('Unit Price')}</div>
          <span className="currency-code dark prefix">{ parse(item.price) }</span>
        </div>
      </div>
      <ConditionalView condition={item.is_big_ticket}>
        <span>{Drupal.t('Kindly contact customer care for initiating online returns for Large Items')}</span>
      </ConditionalView>
    </div>
  );
};

export default ReturnIndividualItem;
