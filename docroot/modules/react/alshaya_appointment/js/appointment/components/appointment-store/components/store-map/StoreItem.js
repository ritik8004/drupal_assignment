import React from 'react';
import ConditionalView from '../../../../../common/components/conditional-view';
import StoreAddress from '../store-address';
import StoreTiming from '../store-timing';

const StoreItem = ({
  display, index, store, onStoreChoose, onStoreExpand, onStoreFinalize, onStoreClose,
}) => (
  <>
    <span className="appointment-store-name">
      <span className="appointment-store-name-wrapper" onClick={(e) => onStoreChoose(e, index)}>
        <span className="store-name">{store.name}</span>
        <span className="store-distance">
          {`${store.distanceInMiles} ${Drupal.t('Miles')}`}
        </span>
      </span>
      <ConditionalView condition={display === 'accordion'}>
        <span className="expand-btn" onClick={(e) => onStoreExpand(e, index)}>Expand</span>
      </ConditionalView>
      <ConditionalView condition={display === 'default'}>
        <span className="appointment-map-list-close" onClick={(e) => onStoreClose(e, index)} />
      </ConditionalView>
    </span>

    <ConditionalView condition={display === 'accordion' || display === 'default'}>
      <div className="store-address-content">
        <div className="store-address">
          <StoreAddress
            address={store.address}
          />
        </div>
        <div className="store-delivery-time">
          <StoreTiming
            timing={store.storeTiming}
          />
        </div>
        <ConditionalView condition={(typeof onStoreFinalize !== 'undefined' && display !== 'accordion')}>
          <div
            className="store-actions"
            gtm-store-title={store.name}
          >
            <button
              className="select-store"
              type="button"
              onClick={(e) => onStoreFinalize(e, store.code)}
            >
              {Drupal.t('Select Store')}
            </button>
          </div>
        </ConditionalView>
      </div>
    </ConditionalView>
  </>
);

export default StoreItem;
