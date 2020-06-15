import React from 'react';

const ClickCollectStoreDetail = ({ store, index }) => (
  <div className="store-detail-wrapper">
    <div className="store-count">{index}</div>
    <div className="store-details">
      <span style={{ color: store.status_color }} className="store-product-status">{store.status_text}</span>
      <span>
        {' '}
        {Drupal.t('at')}
      </span>
      <div className="store-name">{store.address_title}</div>
      <div className="store-address">{store.address_details}</div>
    </div>
  </div>
);
export default ClickCollectStoreDetail;
