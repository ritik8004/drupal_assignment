import React from 'react';
import parse from 'html-react-parser';
import ConditionalView from '../../../../common/components/conditional-view';
import getStringMessage from '../../../../utilities/strings';
import {
  getCncModalButtonText,
  isCollectionPoint,
  getPickUpPointTitle,
  getCncDeliveryTimePrefix,
} from '../../../../utilities/cnc_util';
import PriceElement from '../../../../utilities/special-price/PriceElement';
import collectionPointsEnabled from '../../../../../../js/utilities/pudoAramaxCollection';

const getStoreAddress = (address) => {
  // Only parse valid strings/HTML.
  if (typeof address === 'string') {
    return parse(address);
  }
  return '';
};

const getStoreOpenHours = (openHoursRaw) => {
  let markup = [];
  if (openHoursRaw) {
    markup = Object.entries(openHoursRaw).map(([weekdays, timings]) => (
      <div key={weekdays}>
        <span className="key-value-key">{weekdays}</span>
        <span className="key-value-value">{` (${timings})`}</span>
      </div>
    ));
  }
  return markup;
};

const StoreItem = ({
  display, index, store, onStoreChoose, onStoreExpand, onStoreFinalize, onStoreClose,
}) => (
  <>
    <span
      className={`spc-cnc-store-name ${collectionPointsEnabled ? 'pudo-collection-list' : ''}`}
      onClick={(e) => onStoreChoose(e, index)}
    >
      <ConditionalView condition={collectionPointsEnabled()}>
        <span className="spc-collection-label-wrapper">
          <span className={`${isCollectionPoint(store) ? 'collection-point' : 'store'}-icon`} />
          <span className="pickup-point-title">{getPickUpPointTitle(store)}</span>
        </span>
      </ConditionalView>
      <span className="spc-store-name-wrapper">
        <span className="store-name">{store.name}</span>
        <span className="store-distance">
          {getStringMessage(
            'cnc_distance',
            { '@distance': store.formatted_distance || '' },
          )}
        </span>
      </span>
      <ConditionalView condition={display === 'accordion'}>
        <span className="expand-btn" onClick={(e) => onStoreExpand(e, index)}>Expand</span>
      </ConditionalView>
    </span>
    <ConditionalView condition={display === 'default'}>
      <span className="spc-map-list-close" onClick={(e) => onStoreClose(e, index)} />
    </ConditionalView>
    <ConditionalView condition={display === 'accordion' || display === 'default'}>
      <div className="store-address-content">
        <div className="store-address">{getStoreAddress(store.address)}</div>
        <div className="store-delivery-time">
          <span className="label--delivery-time">{getStringMessage(getCncDeliveryTimePrefix())}</span>
          <span className="delivery--time--value">{` ${store.delivery_time}`}</span>
          <ConditionalView condition={collectionPointsEnabled()}>
            <PriceElement amount={store.price_amount} />
          </ConditionalView>
        </div>
        <div className="store-open-hours">{getStoreOpenHours(store.open_hours_group)}</div>
        <ConditionalView condition={(typeof onStoreFinalize !== 'undefined' && display !== 'accordion')}>
          <div
            className="store-actions"
            gtm-store-address={typeof store.address === 'string' ? store.address.replace(/(<([^>]+)>)/ig, '') : ''}
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
