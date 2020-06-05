import React from 'react';
import PdpInfo from '../pdp-info';
import ClickCollectStoreDetail from '../pdp-click-and-collect-store-detail';

const ClickCollectContent = (props) => {
  const {
    title, pdpProductPrice, finalPrice, closeModal, stores,
  } = props;


  return (
    <div className="magv2-click-collect-popup-container">
      <div className="magv2-click-collect-popup-wrapper">
        <div className="magv2-click-collect-popup-header-wrapper">
          <a className="close" onClick={() => closeModal()}>
            &times;
          </a>
          <PdpInfo
            title={title}
            finalPrice={finalPrice}
            pdpProductPrice={pdpProductPrice}
            shortDetail="true"
          />
        </div>

        <div className="magv2-click-collect-popup-content-wrapper">
          {stores.map((store, key) => (
            <ClickCollectStoreDetail
              key={store.id}
              index={key + 1}
              store={store}
            />
          ))}
        </div>
      </div>
    </div>
  );
};
export default ClickCollectContent;
