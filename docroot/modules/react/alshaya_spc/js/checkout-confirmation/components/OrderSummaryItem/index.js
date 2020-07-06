import React from 'react';
import parse from 'html-react-parser';

const OrderSummaryItem = (props) => {
  const {
    type,
    label,
    value,
    animationDelay: animationDelayValue,
  } = props;

  if (type === 'address') {
    const { name, address } = props;
    return (
      <div className="spc-order-summary-item spc-order-summary-address-item fadeInUp" style={{ animationDelay: animationDelayValue }}>
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
      name, address, phone, openingHours, mapLink,
    } = props;
    return (
      <div className="spc-order-summary-item spc-order-summary-address-item fadeInUp" style={{ animationDelay: animationDelayValue }}>
        <span className="spc-label">{`${label}:`}</span>
        <span className="spc-value">
          <span className="spc-address-name">
            {name}
          </span>
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
      <div className="spc-order-summary-item spc-order-summary-markup-item fadeInUp" style={{ animationDelay: animationDelayValue }}>
        <span className="spc-label">{`${label}:`}</span>
        <span className="spc-value">{parse(value)}</span>
      </div>
    );
  }

  return (
    <div className="spc-order-summary-item fadeInUp" style={{ animationDelay: animationDelayValue }}>
      <span className="spc-label">{`${label}:`}</span>
      <span className="spc-value">{value}</span>
    </div>
  );
};

export default OrderSummaryItem;
