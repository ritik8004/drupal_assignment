import React from 'react';
import Gallery from '../gallery/Gallery';
import PriceContainer from '../price/PriceContainer';
import PromotionsContainer from '../Promotions/PromotionsContainer';

const Teaser = ({hit}) => {

  const swatches = '';

  return (
    <article data-sku={hit.sku} gtm-view-mode="search_result">
      <div className="content">
        <div className="field field--name-field-skus field--type-sku field--label-hidden field__items">
          <a
            href={hit.url}
            data--original-url={hit.url}
            className="list-product-gallery product-selected-url">
            <Gallery media={hit.media} title={hit.title} />
          </a>
          <div className="product-plp-detail-wrapper">
            <h2 className="field--name-name">
              <a href={hit.url} className="product-selected-url">{hit.title}</a>
            </h2>
            <PriceContainer price={hit.original_price} final_price={hit.final_price}/>
            <PromotionsContainer promotions={hit.promotions}/>
            {swatches}
          </div>
        </div>
        <div className="labels-container" data-type="plp" data-sku={hit.sku} data-main-sku={hit.sku}>
        </div>
      </div>
    </article>
  );
}

export default Teaser;
