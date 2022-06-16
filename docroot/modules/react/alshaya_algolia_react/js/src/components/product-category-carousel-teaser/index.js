import React from 'react';
import Parser from 'html-react-parser';
import Price from '../price';
import PromotionsFrame from '../promotion-frame';
import Gallery from '../gallery';
import ConditionalView from '../../../common/components/conditional-view';
import {
  isPromotionFrameEnabled,
} from '../../utils/indexUtils';
import Promotions from '../promotions';

const ProductCategoryTeaser = ({
  hit, gtmContainer = null, vatText,
}) => {
  const { currentLanguage } = drupalSettings.path;

  const overridenGtm = gtmContainer ? { ...hit.gtm, ...{ 'gtm-container': gtmContainer } } : hit.gtm;
  const attribute = [];
  Object.entries(hit).forEach(([key, value]) => {
    if (value !== null && value[currentLanguage] !== undefined) {
      attribute[key] = value[currentLanguage];
    } else {
      attribute[key] = value;
    }
  });
  // Skip if there is no value for current language.
  if (attribute.title === undefined) {
    return null;
  }

  let labels = [];
  if (attribute.product_labels.length > 0) {
    // Check if product labels are in V3 format by checking if image is string.
    if (typeof attribute.product_labels[0].image === 'string') {
      attribute.product_labels.forEach((label) => {
        labels.push({
          image: {
            url: label.image,
            alt: label.text,
            title: label.text,
          },
          position: label.position,
        });
      });
    } else {
      labels = attribute.product_labels;
    }
  }

  const teaserClass = 'views-row';

  return (
    <div className={teaserClass}>
      <article
        className="node--view-mode-search-result"
        data-sku={hit.sku}
        data-vmode="product_category_carousel"
        data-insights-object-id={hit.objectID}
        /* Dangling variable _state is coming from an external library here. */
        // eslint-disable-next-line no-underscore-dangle
        data-insights-position={hit.__position}
        // eslint-disable-next-line no-underscore-dangle
        data-insights-query-id={hit.__queryID}
        gtm-type="gtm-product-link"
        {...overridenGtm}
      >
        <a
          href={`${attribute.url}`}
          data--original-url={`${attribute.url}`}
          className="list-product-gallery product-selected-url"
        >
          <div className="image-label-wrapper">
            <ConditionalView condition={hit.media.length > 0}>
              <Gallery
                media={hit.media.splice(0, 1)}
                title={attribute.title}
                labels={labels}
                sku={hit.sku}
              />
            </ConditionalView>
          </div>
          <div className="c-products__item__label">
            {attribute.title && Parser(attribute.title)}
          </div>
          <div className={`price-block price-block-${hit.sku}`}>
            {attribute.rendered_price
              ? Parser(attribute.rendered_price)
              : <Price price={attribute.original_price} final_price={attribute.final_price} />}
          </div>
        </a>
        <div className="product-plp-detail-wrapper">
          <ConditionalView condition={isPromotionFrameEnabled()}>
            <PromotionsFrame promotions={attribute.promotions} />
          </ConditionalView>
          <ConditionalView condition={!isPromotionFrameEnabled()}>
            <Promotions promotions={attribute.promotions} />
          </ConditionalView>
          <ConditionalView condition={vatText}>
            <div className="vat-text">{vatText}</div>
          </ConditionalView>
        </div>
      </article>
    </div>
  );
};

export default ProductCategoryTeaser;
