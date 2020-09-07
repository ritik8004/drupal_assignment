import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import PdpCrossellUpsellImageContent from './pdp-crossell-upsell-images-content';

const PdpCrossellUpsellImage = ({
  imageUrl,
  alt,
  title,
  pdpProductPrice,
  finalPrice,
  productLabels,
  productPromotions,
  productUrl,
  openModal,
  getPanelData,
  relatedSku,
  gtmAttributes,
}) => (
<<<<<<< HEAD
  <>
    <ConditionalView condition={window.innerWidth < 768}>

      {/* eslint-disable-next-line react/jsx-props-no-spreading */}
      <a className="magv2-pdp-crossell-upsell-image-wrapper" href={productUrl} {...gtmAttributes}>
        <PdpCrossellUpsellImageContent
          imageUrl={imageUrl}
          alt={alt}
          title={title}
          pdpProductPrice={pdpProductPrice}
          finalPrice={finalPrice}
          productLabels={productLabels}
          productPromotions={productPromotions}
        />
      </a>
    </ConditionalView>

    <ConditionalView condition={window.innerWidth > 767}>

      {/* eslint-disable-next-line react/jsx-props-no-spreading */}
      <a className="magv2-pdp-crossell-upsell-image-wrapper" href={productUrl} onClick={() => getPanelData(openModal(relatedSku))} {...gtmAttributes}>
        <PdpCrossellUpsellImageContent
          imageUrl={imageUrl}
          alt={alt}
          title={title}
          pdpProductPrice={pdpProductPrice}
          finalPrice={finalPrice}
          productLabels={productLabels}
          productPromotions={productPromotions}
        />
      </a>
    </ConditionalView>
  </>
=======
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
      animateTitlePrice
    />
    {productPromotions ? (
      <div className="promotions promotions-full-view-mode">
        {Object.keys(productPromotions).map((key) => (
          <p><a href={productPromotions[key].promo_web_url}>{productPromotions[key].text}</a></p>
        ))}
      </div>
    ) : null}
  </a>
>>>>>>> CORE-22801: Add loader and remove animations form component inside related product panel.
);

export default PdpCrossellUpsellImage;
