import React from 'react';
import parse from 'html-react-parser';
import collectionPointsEnabled from '../../../../../js/utilities/pudoAramaxCollection';
import PriceElement from '../../../utilities/special-price/PriceElement';

const OrderSummaryItem = (props) => {
  const {
    type,
    label,
    value,
    animationDelay: animationDelayValue,
    context,
  } = props;

  let styles = {
    animationDelay: animationDelayValue,
  };
  if (context === 'print') {
    styles = {
      animation: 'none !important',
      transition: 'none !important',
    };
  }

  if (type === 'address') {
    const { name, address } = props;
    return (
      <div className="spc-order-summary-item spc-order-summary-address-item fadeInUp" style={styles}>
        <span className="spc-label">{`${label}:`}</span>
        <span className="spc-value">
          <span className="spc-address-name">
            {name}
          </span>
          <span className="spc-address">
            {address}
          </span>
        </span>
      </div>
    );
  }

  if (type === 'click_and_collect') {
    const {
      name,
      address,
      phone,
      openingHours,
      mapLink,
      pickUpPointIcon,
      pickUpPointTitle,
      collectionDate,
      collectionCharge,
    } = props;
    return (
      <div className="spc-order-summary-item spc-order-summary-address-item spc-order-summary-cnc fadeInUp" style={styles}>
        <span className="spc-label">{`${label}:`}</span>
        <span className="spc-value">
          <div className="spc-store-name-wrapper">
            {(collectionPointsEnabled() && pickUpPointIcon !== undefined)
              && (
              <span className={`${pickUpPointIcon}-icon`} />
              )}
            {(collectionPointsEnabled() && pickUpPointTitle !== undefined)
              && (
                <span className="pickup-point-title">{pickUpPointTitle}</span>
              )}
            <span className="spc-address-name">
              {name}
            </span>
          </div>
          <span className="spc-address">
            {address}
            <span className="spc-cnc-address-phone">{phone}</span>
          </span>
          <div className="spc-store-open-hours">
            {
              Object.entries(openingHours).map(([weekdays, timings]) => (
                <div key={weekdays}>
                  <span className="key-value-key">{weekdays}</span>
                  <span className="key-value-value">{` (${timings})`}</span>
                </div>
              ))
            }
          </div>
          {(collectionPointsEnabled() && pickUpPointTitle !== undefined)
            && (
              <>
                <div className="store-delivery-time">
                  <span className="label--delivery-time">{Drupal.t('Collect in')}</span>
                  <span className="delivery--time--value">{` ${collectionDate}`}</span>
                  <PriceElement amount={collectionCharge} />
                </div>
                <div className="spc-cnc-confirmation-govtid-msg">
                  <span className="spc-cnc-confirmation-govtid-msg-label">{`${Drupal.t('Important Note:')} `}</span>
                  <span>{Drupal.t('Please ensure that the person collecting this order has a valid government ID and printed copy of the invoice.')}</span>
                </div>
              </>
            )}
          <span className="spc-store-map-link">
            <a href={mapLink} rel="noopener noreferrer" target="_blank">
              {Drupal.t('Get directions')}
            </a>
          </span>
        </span>
      </div>
    );
  }

  if (type === 'markup') {
    return (
      <div className="spc-order-summary-item spc-order-summary-markup-item fadeInUp" style={styles}>
        <span className="spc-label">{`${label}:`}</span>
        <span className="spc-value">{parse(value)}</span>
      </div>
    );
  }

  if (type === 'mobile') {
    return (
      <div className="spc-order-summary-item fadeInUp" style={styles}>
        <span className="spc-label">{`${label}:`}</span>
        <span className="spc-value phone-number" dir="ltr">{value}</span>
      </div>
    );
  }

  if (type === 'hello_member') {
    return (
      <div className="spc-order-summary-item fadeInUp" style={styles}>
        <span className="spc-label">{`${label}:`}</span>
        <span className="spc-value hello-member-accured-points">{value}</span>
      </div>
    );
  }

  return (
    <div className="spc-order-summary-item fadeInUp" style={styles}>
      <span className="spc-label">{`${label}:`}</span>
      <span className="spc-value">{value}</span>
    </div>
  );
};

export default OrderSummaryItem;
