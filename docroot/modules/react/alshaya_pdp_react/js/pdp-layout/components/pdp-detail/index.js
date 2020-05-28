import React from 'react';

const PdpDetail = (props) => {
  const {
    title, pdpProductPrice, finalPrice, shortDetail = false,
  } = props;

  const specialPriceClass = (finalPrice < pdpProductPrice) ? 'has-special-price' : '';


  return (
    <div className={(shortDetail ? 'magv2-compact-detail-wrapper' : 'magv2-detail-wrapper')}>
      <div className="magv2-pdp-title">{title}</div>
      <div className={`magv2-pdp-price-container${specialPriceClass}`}>
        {(finalPrice < pdpProductPrice)
          ? (
            <div className="magv2-pdp-final-price-wrapper">
              <span className="magv2-pdp-final-price-currency suffix">{drupalSettings.alshaya_spc.currency_config.currency_code}</span>
              <span className="magv2-pdp-final-price-amount">{finalPrice}</span>
            </div>
          )
          : null}
        <div className="magv2-pdp-price-wrapper">
          <span className="magv2-pdp-price-currency suffix">{drupalSettings.alshaya_spc.currency_config.currency_code}</span>
          <span className="magv2-pdp-price-amount">{pdpProductPrice}</span>
        </div>
        {(!shortDetail && drupalSettings.alshaya_spc.vat_text)
          ? <div className="magv2-pdp-vat-text">{drupalSettings.alshaya_spc.vat_text}</div>
          : null}
      </div>
    </div>
  );
};
export default PdpDetail;
