import React from 'react';

const ClickCollectStoreDetail = (props) => {
  const { store, index } = props;

  return (
    <div className="store-detail-wrapper">
      <div className="store-count">{index}</div>
      <div className="store-details">
        <span style={{ color: store.status_color }} className="store-product-status">{Drupal.t(store.status_text)}</span>
        <span>{Drupal.t(' at')}</span>
        <div className="store-name">{Drupal.t(store.address_title)}</div>
        <div className="store-address">{Drupal.t(store.address_details)}</div>
      </div>
    </div>
  );
};
export default ClickCollectStoreDetail;
