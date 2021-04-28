import React from 'react';
import PostpayCart
  from '../../../../../alshaya_spc/js/cart/components/postpay/postpay';
import Postpay from '../../../../../alshaya_spc/js/utilities/postpay';

const PdpInfo = ({
  title, pdpProductPrice, finalPrice,
  shortDetail = false, brandLogo,
  brandLogoAlt, brandLogoTitle, animateTitlePrice,
  hidepostpay,
}) => {
  let discountPercantage = null;

  if (!(pdpProductPrice === finalPrice)) {
    discountPercantage = Math.round(((pdpProductPrice - finalPrice) / pdpProductPrice) * 100);
  }

  const specialPriceClass = (parseInt(finalPrice, 10) < parseInt(pdpProductPrice, 10)) ? 'has-special-price' : '';

  let postpay;
  if (Postpay.isPostpayEnabled() && !hidepostpay) {
    postpay = (
      <PostpayCart
        amount={finalPrice.replace(',', '')}
        classNames=""
        pageType="pdp"
      />
    );
  }

  return (
    <div className={(shortDetail ? 'magv2-compact-detail-wrapper' : 'magv2-detail-wrapper')}>
      <div
        className={`magv2-pdp-title-wrapper ${(animateTitlePrice ? 'fadeInUp' : '')}`}
        style={(animateTitlePrice ? { animationDelay: '0.3s' } : null)}
      >
        <div className={`magv2-pdp-title ${(brandLogo ? 'has-brand-logo' : '')}`}>{title}</div>
        {(brandLogo)
          ? <div className="magv2-pdp-brand-logo"><img src={brandLogo} alt={brandLogoAlt} title={brandLogoTitle} /></div>
          : null }
      </div>
      <div
        className={`magv2-pdp-price ${(animateTitlePrice ? 'fadeInUp' : '')}`}
        style={(animateTitlePrice ? { animationDelay: '0.4s' } : null)}
      >
        <div className={`magv2-pdp-price-container ${specialPriceClass}`}>
          {(parseInt(finalPrice, 10) < parseInt(pdpProductPrice, 10))
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
          <div className="magv2-meta-data-wrapper">
            <div className="magv2-pdp-price-wrapper">
              <span className="magv2-pdp-price-currency suffix">{drupalSettings.alshaya_spc.currency_config.currency_code}</span>
              <span className="magv2-pdp-price-amount">{pdpProductPrice}</span>
            </div>
            {(!shortDetail && drupalSettings.vatText)
              ? <div className="magv2-pdp-vat-text">{drupalSettings.vatText}</div>
              : null}
          </div>
        </div>
      </div>
      {postpay}
    </div>
  );
};
export default PdpInfo;
