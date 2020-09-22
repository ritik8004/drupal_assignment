import React from 'react';
import parse from 'html-react-parser';

const ClickCollectStoreDetail = ({ store, index }) => (
  <div className="store-detail-wrapper fadeInUp">
    <div className="store-count">{index}</div>
    <div className="store-details">
      <div className="store-name">{store.name}</div>
      <div className="store-address">{parse(store.address)}</div>
    </div>
  </div>
);
export default ClickCollectStoreDetail;
