import React from 'react';
import parse from 'html-react-parser';

const ReturnItemsListing = ({
  products,
}) => (
  <>
    <div className="products-list-wrapper">
      <div className="select-items-label">
        <div className="select-items-header">{ Drupal.t('Select items to return') }</div>
      </div>
    </div>
    {products.map((item) => (
      <div key={item.name}>
        <div className="items-tabel">
          <div className="order-item-row">
            {item.image_data
                && (
                <div className="order-item-image">
                  <div className="image-data-wrapper">
                    <img src={`${item.image_data.url}`} alt={`${item.image_data.alt}`} title={`${item.image_data.title}`} />
                  </div>
                </div>
                )}
            <div className="order__details--summary order__details--description">
              <div className="item-name">{ item.name }</div>
              {item.attributes && Object.keys(item.attributes).map((attribute) => (
                <div key={item.attributes[attribute].label} className="attribute-detail">
                  { item.attributes[attribute].label }
                  :
                  { item.attributes[attribute].value }
                </div>
              ))}
              <div className="item-code">
                {Drupal.t('Item code')}
                :
                { item.sku }
              </div>
              <div className="item-quantity">
                {Drupal.t('Quantity')}
                :
                { item.ordered }
              </div>
            </div>
            <div>
              <div className="item-price">
                <div className="light">{Drupal.t('Unit Price')}</div>
                <span className="currency-code dark prefix">{ parse(item.price) }</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    ))}
  </>
);

export default ReturnItemsListing;
