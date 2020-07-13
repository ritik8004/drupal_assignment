import React from 'react';

const PdpInfo = (props) => {
  const {
    title, pdpProductPrice, finalPrice,
    shortDetail = false,
    brandLogo, brandLogoAlt, brandLogoTitle,
  } = props;

  let discountPercantage = null;

  if (!(pdpProductPrice === finalPrice)) {
    discountPercantage = Math.round(((pdpProductPrice - finalPrice) / pdpProductPrice) * 100);
  }

  const specialPriceClass = (finalPrice < pdpProductPrice) ? 'has-special-price' : '';

  return (
    <div className={(shortDetail ? 'magv2-compact-detail-wrapper' : 'magv2-detail-wrapper')}>
      <div className="magv2-pdp-title-wrapper fadeInUp" style={{ animationDelay: '0.3s' }}
        <div className="magv2-pdp-title">{title}</div>
        <div className="magv2-pdp-brand-logo"><img src={brandLogo} alt={brandLogoAlt} title={brandLogoTitle} /></div>
      </div>
      <div className="magv2-pdp-price fadeInUp" style={{ animationDelay: '0.4s' }}>
        <div className={`magv2-pdp-price-container ${specialPriceClass}`}>
          {(finalPrice < pdpProductPrice)
            ? (
              <div className="magv2-pdp-final-price-wrapper">
                <span className="magv2-pdp-final-price-currency suffix">{drupalSettings.alshaya_spc.currency_config.currency_code}</span>
                <span className="magv2-pdp-final-price-amount">{finalPrice}</span>
                {!shortDetail
                  ? (
                    <span className="magv2-pdp-discount-percentage">
                      {`${Drupal.t('Save')} ${discountPercantage}%`}
                    </span>
                  )
                  : null}
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
