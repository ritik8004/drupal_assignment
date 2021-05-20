import React from 'react';
import PdpInfo from '../../pdp-info';

const PdpCrossellUpsellImageContent = ({
  imageUrl,
  alt,
  title,
  pdpProductPrice,
  finalPrice,
  productLabels,
  productPromotions,
}) => (
  <>
    <div className="magv2-pdp-crossell-upsell-img">
      <img
        src={imageUrl}
        alt={alt}
        title={title}
        loading="lazy"
      />
      {productLabels ? (
        <div className="product-labels">
          {Object.keys(productLabels).map((key) => (
            <div className={`labels ${productLabels[key].position}`} key={productLabels[key].position}>
              <img
                src={productLabels[key].image.url}
                alt={productLabels[key].image.alt}
                title={productLabels[key].image.title}
              />
            </div>
          ))}
        </div>
      ) : null}
    </div>
    <PdpInfo
      title={title}
      finalPrice={finalPrice}
      pdpProductPrice={pdpProductPrice}
      animateTitlePrice={false}
      hidepostpay
    />
    {productPromotions ? (
      <div className="promotions promotions-full-view-mode">
        {Object.keys(productPromotions).map((key) => (
          <span>
            <a href={productPromotions[key].promo_web_url}>{productPromotions[key].text}</a>
          </span>
        ))}
      </div>
    ) : null}
  </>
);

export default PdpCrossellUpsellImageContent;
