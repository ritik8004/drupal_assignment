import React from 'react';
import PdpInfo from '../pdp-info';

const PdpCrossellUpsellImage = ({
  imageUrl,
  alt,
  title,
  pdpProductPrice,
  finalPrice,
  productUrl,
  productLabels,
  productPromotions,
  openModal,
  getPanelData,
  relatedSku,
  gtmAttributes,
}) => (
  // eslint-disable-next-line react/jsx-props-no-spreading
  <a className="magv2-pdp-crossell-upsell-image-wrapper" href={productUrl} onClick={() => getPanelData(openModal(relatedSku))} {...gtmAttributes}>
    <div className="magv2-pdp-crossell-upsell-img">
      <img
        src={imageUrl}
        alt={alt}
        title={title}
        loading="lazy"
      />
    </div>
    {productLabels ? (
      <div className="product-labels">
        {Object.keys(productLabels).map((key) => (
          <div className={`labels ${productLabels[key].position}`}>
            <img
              src={productLabels[key].image.url}
              alt={productLabels[key].image.alt}
              title={productLabels[key].image.title}
            />
          </div>
        ))}
      </div>
    ) : null}
    <PdpInfo
      title={title}
      finalPrice={finalPrice}
      pdpProductPrice={pdpProductPrice}
    />
    {productPromotions ? (
      <div className="promotions promotions-full-view-mode">
        {Object.keys(productPromotions).map((key) => (
          <p><a href={productPromotions[key].promo_web_url}>{productPromotions[key].text}</a></p>
        ))}
      </div>
    ) : null}
  </a>
);

export default PdpCrossellUpsellImage;
