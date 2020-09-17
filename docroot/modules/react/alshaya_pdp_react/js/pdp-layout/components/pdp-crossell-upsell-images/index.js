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
  keyId,
}) => (
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
          key={keyId}
          keyId={keyId}
        />
      </a>
    </ConditionalView>

    <ConditionalView condition={window.innerWidth > 767}>

      {/* eslint-disable-next-line react/jsx-props-no-spreading */}
      <a className="magv2-pdp-crossell-upsell-image-wrapper" onClick={() => getPanelData(openModal(relatedSku))} {...gtmAttributes}>
        <PdpCrossellUpsellImageContent
          imageUrl={imageUrl}
          alt={alt}
          title={title}
          pdpProductPrice={pdpProductPrice}
          finalPrice={finalPrice}
          productLabels={productLabels}
          productPromotions={productPromotions}
          key={keyId}
          keyId={keyId}
        />
      </a>
    </ConditionalView>
  </>
);

export default PdpCrossellUpsellImage;
