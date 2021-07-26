import React from 'react';
import parse from 'html-react-parser';
import ConditionalView from '../../../../common/components/conditional-view';
import getStringMessage from '../../../../utilities/strings';
import { getCncModalButtonText } from '../../../../utilities/cnc_util';

const StoreItem = ({
  display, index, store, onStoreChoose, onStoreExpand, onStoreFinalize, onStoreClose,
}) => (
  <>
    <span className="spc-cnc-store-name">
      <span className="spc-store-name-wrapper" onClick={(e) => onStoreChoose(e, index)}>
        <span className="store-name">{store.name}</span>
        <span className="store-distance">
          {getStringMessage(
            'cnc_distance',
            { '@distance': store.formatted_distance },
          )}
        </span>
      </span>
      <ConditionalView condition={display === 'accordion'}>
        <span className="expand-btn" onClick={(e) => onStoreExpand(e, index)}>Expand</span>
      </ConditionalView>
      <ConditionalView condition={display === 'default'}>
        <span className="spc-map-list-close" onClick={(e) => onStoreClose(e, index)} />
      </ConditionalView>
    </span>
    <ConditionalView condition={display === 'accordion' || display === 'default'}>
      <div className="store-address-content">
        <div className="store-address">{parse(store.address)}</div>
        <div className="store-delivery-time">
          <span className="label--delivery-time">{getStringMessage('cnc_collect_in_store')}</span>
          <span className="delivery--time--value">{` ${store.delivery_time}`}</span>
        </div>
        <div className="store-open-hours">
          {
            Object.entries(store.open_hours_group).map(([weekdays, timings]) => (
              <div key={weekdays}>
                <span className="key-value-key">{weekdays}</span>
                <span className="key-value-value">{` (${timings})`}</span>
              </div>
            ))
          }
        </div>
        <ConditionalView condition={(typeof onStoreFinalize !== 'undefined' && display !== 'accordion')}>
          <div
            className="store-actions"
            gtm-store-address={store.address.replace(/(<([^>]+)>)/ig, '')}
            gtm-store-title={store.name}
          >
            <button
              className="select-store"
              type="button"
              onClick={(e) => onStoreFinalize(e, store.code)}
            >
              {getStringMessage(getCncModalButtonText())}
            </button>
          </div>
        </ConditionalView>
      </div>
    </ConditionalView>
  </>
);

export default StoreItem;
