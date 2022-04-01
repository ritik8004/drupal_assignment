import React from 'react';
import parse from 'html-react-parser';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

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
            <ConditionalView condition={hasValue(item.image_data.url)}>
              <img src={`${item.image_data.url}`} alt={`${item.image_data.alt}`} title={`${item.image_data.title}`} />
            </ConditionalView>
            <ConditionalView condition={!item.is_returnable}>
              <div className="not-eligible-label">{ Drupal.t('Not eligible for return', {}, { context: 'online_returns' }) }</div>
            </ConditionalView>
          </div>
        </div>
      </ConditionalView>
      <div className="order__details--summary order__details--description">
        <div className="item-name">{ item.name }</div>
        {item.attributes && Object.keys(item.attributes).map((attribute) => (
          <div key={item.attributes[attribute].label} className="attribute-detail">
            { Drupal.t('@attrLabel: @attrValue', { '@attrLabel': item.attributes[attribute].label, '@attrValue': item.attributes[attribute].value }, {}, { context: 'online_returns' }) }
          </div>
        ))}
        <div className="item-code">
          { Drupal.t('Item Code: @sku', { '@sku': item.sku }, {}, { context: 'online_returns' }) }
        </div>
        <div className="item-quantity">
          { Drupal.t('Quantity: @quantity', { '@quantity': item.ordered }, {}, { context: 'online_returns' }) }
        </div>
      </div>
      <div className="item-price">
        <div className="light">{Drupal.t('Unit Price', {}, { context: 'online_returns' })}</div>
        <span className="currency-code dark prefix">{ parse(item.price) }</span>
      </div>
      <ConditionalView condition={item.is_big_ticket}>
        <span>{Drupal.t('Kindly contact customer care for initiating online returns for Large Items')}</span>
      </ConditionalView>
    </div>
  );
};

export default ReturnIndividualItem;
