import React from 'react';
import StoreAddress from '../store-address';
import StoreTiming from '../store-timing';
import getStringMessage from '../../../../../../../js/utilities/strings';

const StoreItem = ({
  display, index, store, onStoreChoose, onStoreExpand, onStoreFinalize, onStoreClose,
}) => (
  <div>
    <span className="appointment-store-name">
      <span className="appointment-store-name-wrapper" onClick={(e) => onStoreChoose(e, index)}>
        <span className="store-name">{store.name}</span>
        <span className="store-distance">
          {`${store.distanceInMiles} ${getStringMessage('miles')}`}
        </span>
      </span>
      {display === 'accordion'
        ? <span className="expand-btn" onClick={(e) => onStoreExpand(e, index)}>Expand</span>
        : null}
      {display === 'default'
        ? <span className="appointment-map-list-close" onClick={(e) => onStoreClose(e, index)} />
        : null}
    </span>
    {display === 'accordion' || display === 'default'
      ? (
        <div className="store-address-content">
          <div className="store-address">
            <StoreAddress
              address={store.address}
            />
          </div>
          <StoreTiming
            timing={store.storeTiming}
          />
          {(typeof onStoreFinalize !== 'undefined' && display !== 'accordion')
            ? (
              <div
                className="store-actions"
                gtm-store-title={store.name}
              >
                <button
                  className="select-store"
                  type="button"
                  onClick={(e) => onStoreFinalize(e, store.code)}
                >
                  {getStringMessage('select_store_button')}
                </button>
              </div>
            )
            : null}
        </div>
      )
      : null}
  </div>
);

export default StoreItem;
