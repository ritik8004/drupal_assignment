import React from 'react';
import ReturnItemDetails from '../return-item-details';

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
      <div key={item.name} className="item-list-wrapper">
        <ReturnItemDetails
          item={item}
        />
      </div>
    ))}
  </>
);

export default ReturnItemsListing;
