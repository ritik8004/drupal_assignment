import React, { useRef, useEffect } from 'react';

const PdpInfo = (props) => {
  const {
    title, pdpProductPrice, finalPrice,
    shortDetail = false, childRef,
    brandLogo, brandLogoAlt, brandLogoTitle,
  } = props;

  let discountPercantage = null;

  if (!(pdpProductPrice === finalPrice)) {
    discountPercantage = ((pdpProductPrice - finalPrice) / pdpProductPrice) * 100;
  }

  const specialPriceClass = (finalPrice < pdpProductPrice) ? 'has-special-price' : '';

  const wrapper = useRef();

  useEffect(() => {
    if (childRef) {
      childRef(wrapper);
    }
  },
  [
    childRef,
    wrapper,
  ]);

  return (
    <div className={(shortDetail ? 'magv2-compact-detail-wrapper' : 'magv2-detail-wrapper')} ref={wrapper}>
      <div className="magv2-pdp-title-wrapper">
        <div className="magv2-pdp-title">{title}</div>
        <div className="magv2-pdp-brand-logo"><img src={brandLogo} alt={brandLogoAlt} title={brandLogoTitle} /></div>
      </div>
      <div className="magv2-pdp-price">
        <div className={`magv2-pdp-price-container ${specialPriceClass}`}>
          {(finalPrice < pdpProductPrice)
            ? (
              <div className="magv2-pdp-final-price-wrapper">
                <span className="magv2-pdp-final-price-currency suffix">{drupalSettings.alshaya_spc.currency_config.currency_code}</span>
                <span className="magv2-pdp-final-price-amount">{finalPrice}</span>
                <span className="magv2-pdp-discount-percentage">{discountPercantage}</span>
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
    </div>
  );
};
export default PdpInfo;
