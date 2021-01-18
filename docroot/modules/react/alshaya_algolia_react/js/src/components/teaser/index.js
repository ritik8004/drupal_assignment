import React, { useState } from 'react';
import Parser from 'html-react-parser';
import Gallery from '../gallery';
import Price from '../price';
import Promotions from '../promotions';
import Lables from '../labels';
import { storeClickedItem } from '../../utils';
import Swatches from '../swatch';

const Teaser = ({
  hit, gtmContainer = null, pageType,
}) => {
  const { showSwatches } = drupalSettings.reactTeaserView.swatches;
  const collectionLabel = [];
  const [initiateSlider, setInitiateSlider] = useState(false);
  if (drupalSettings.plp_attributes && drupalSettings.plp_attributes.length > 0) {
    const { plp_attributes: plpAttributes } = drupalSettings;
    for (let i = 0; i < plpAttributes.length; i++) {
      if (hit && hit.collection_labels && hit.collection_labels[plpAttributes[i]]) {
        collectionLabel.push({
          class: plpAttributes[i],
          value: hit.collection_labels[plpAttributes[i]],
        });
        break;
      }
    }
  }

  let labelItems = '';
  if (collectionLabel.length > 0) {
    labelItems = collectionLabel.map((d) => <li className={d.class} key={d.value}>{d.value}</li>);
  }
  const overridenGtm = gtmContainer ? { ...hit.gtm, ...{ 'gtm-container': gtmContainer } } : hit.gtm;

  return (
    <div className="c-products__item views-row">
      <article
        className="node--view-mode-search-result"
        onClick={(event) => storeClickedItem(event, pageType)}
        data-sku={hit.sku}
        data-vmode="search_result"
        data-insights-object-id={hit.objectID}
        /* Dangling variable _state is coming from an external library here. */
        // eslint-disable-next-line no-underscore-dangle
        data-insights-position={hit.__position}
        // eslint-disable-next-line no-underscore-dangle
        data-insights-query-id={hit.__queryID}
        gtm-type="gtm-product-link"
        onMouseEnter={() => {
          setInitiateSlider(true);
        }}
        {...overridenGtm}
      >
        <div className="field field--name-field-skus field--type-sku field--label-hidden field__items">
          <a
            href={`${hit.url}`}
            data--original-url={`${hit.url}`}
            className="list-product-gallery product-selected-url"
          >
            <Gallery media={hit.media} title={hit.title} initiateSlider={initiateSlider} />
          </a>
          <div className="product-plp-detail-wrapper">
            { collectionLabel.length > 0
              && (
              <div className="product-labels">
                <ul className="collection-labels">
                  {labelItems}
                </ul>
              </div>
              )}
            <h2 className="field--name-name">
              <a href={`${hit.url}`} className="product-selected-url">
                <div className="aa-suggestion">
                  <span className="suggested-text">
                    {Parser(hit.title)}
                  </span>
                </div>
              </a>
            </h2>
            {hit.rendered_price
              ? Parser(hit.rendered_price)
              : <Price price={hit.original_price} final_price={hit.final_price} />}
            <Promotions promotions={hit.promotions} />
            {showSwatches ? <Swatches swatches={hit.swatches} url={hit.url} /> : null}
          </div>
        </div>
        <Lables labels={hit.product_labels} sku={hit.sku} />
      </article>
    </div>
  );
};

export default Teaser;
