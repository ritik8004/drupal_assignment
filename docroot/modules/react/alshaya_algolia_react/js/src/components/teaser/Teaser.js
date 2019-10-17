import React from 'react';
import Gallery from '../gallery/Gallery';
import PriceContainer from '../price/PriceContainer';
import PromotionsContainer from '../promotions/PromotionsContainer';
import LabelsContainer from '../labels/LabelsContainer';

const Teaser = ({hit}) => {
  const swatches = (null);

  return (
    <div className="c-products__item views-row" >
      <article
        className="node--view-mode-search-result"
        data-sku={hit.sku}
        data-vmode="search_result"
        data-insights-object-id={hit.objectID}
        data-insights-position={hit.__position}
        data-insights-query-id={hit.__queryID}
        {...hit.gtm}
      >
        <div className="field field--name-field-skus field--type-sku field--label-hidden field__items">
          <a
            href={`${hit.url}?queryID=${hit.__queryID}`}
            data--original-url={`${hit.url}?queryID=${hit.__queryID}`}
            className="list-product-gallery product-selected-url">
            <Gallery media={hit.media} title={hit.title} />
          </a>
          <div className="product-plp-detail-wrapper">
            <h2 className="field--name-name">
              <a href={`${hit.url}?queryID=${hit.__queryID}`} className="product-selected-url">{hit.title}</a>
            </h2>
            <PriceContainer price={hit.original_price} final_price={hit.final_price}/>
            <PromotionsContainer promotions={hit.promotions}/>
            {swatches}
          </div>
        </div>
        <LabelsContainer labels={hit.product_labels} sku={hit.sku} />
      </article>
    </div>
  );
};

export default Teaser;
