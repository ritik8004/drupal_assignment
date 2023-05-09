import React from 'react';
import PostpayCart
  from '../../../../../alshaya_spc/js/cart/components/postpay/postpay';
import Postpay from '../../../../../alshaya_spc/js/utilities/postpay';
import TabbyWidget from '../../../../../js/tabby/components';
import Tabby from '../../../../../js/tabby/utilities/tabby';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import PriceElement from '../../../../../js/utilities/components/price/price-element';

const PdpInfo = ({
  title, pdpProductPrice, finalPrice,
  shortDetail = false, brandLogo,
  brandLogoAlt, brandLogoTitle, animateTitlePrice,
  hidepostpay, context,
}) => {
  let discountPercantage = null;
  const productPriceNumber = pdpProductPrice.replace(',', '');
  const finalPriceNumber = finalPrice.replace(',', '');

  if (!(productPriceNumber === finalPriceNumber)) {
    // eslint-disable-next-line max-len
    discountPercantage = Math.round(((productPriceNumber - finalPriceNumber) / productPriceNumber) * 100);
  }

  const specialPriceClass = (parseInt(finalPriceNumber, 10) < parseInt(productPriceNumber, 10)) ? 'has-special-price' : '';

  let postpay;
  if (Postpay.isPostpayEnabled() && !hidepostpay) {
    postpay = (
      <PostpayCart
        amount={finalPriceNumber}
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
          ? <div className="magv2-pdp-brand-logo"><img loading="lazy" src={brandLogo} alt={brandLogoAlt} title={brandLogoTitle} /></div>
          : null }
      </div>
      <div
        className={`magv2-pdp-price ${(animateTitlePrice ? 'fadeInUp' : '')}`}
        style={(animateTitlePrice ? { animationDelay: '0.4s' } : null)}
      >
        <div className={`magv2-pdp-price-container ${specialPriceClass}`}>
          {(parseInt(finalPriceNumber, 10) < parseInt(productPriceNumber, 10))
            ? (
              <div className="magv2-pdp-final-price-wrapper">
                <PriceElement amount={finalPrice} currencyClass="magv2-pdp-final-price-currency" amountClass="magv2-pdp-final-price-amount" />
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
              <PriceElement amount={pdpProductPrice} currencyClass="magv2-pdp-price-currency" amountClass="magv2-pdp-price-amount" />
            </div>
            {(!shortDetail && drupalSettings.vatText)
              ? <div className="magv2-pdp-vat-text">{drupalSettings.vatText}</div>
              : null}
          </div>
        </div>
      </div>
      {postpay}
      <ConditionalView condition={context === 'main'
        && Tabby.showTabbyWidget()
        && Tabby.isTabbyEnabled()}
      >
        <TabbyWidget
          classNames=""
          pageType="pdp"
          id="tabby-promo-pdp-main"
        />
      </ConditionalView>
    </div>
  );
};
export default PdpInfo;
