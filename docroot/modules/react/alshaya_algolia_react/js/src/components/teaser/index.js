import React from 'react';
import Gallery from '../gallery';
import Price from '../price';
import Promotions from '../promotions';
import Lables from '../labels';
import { getCurrentSearchQueryString, setClickedItem } from '../../utils';
import Parser from 'html-react-parser';
import Swatches from '../swatch';

const Teaser = ({hit}) => {

  const localStorageStore = (event) => {
    const articleNode = event.target.closest('.node--view-mode-search-result');
    const queryString = getCurrentSearchQueryString();

    var storage_details = {
      sku: articleNode.getAttribute('data-sku'),
      grid_type: articleNode.classList.contains('product-large') ? 'large' : 'small',
    };

    storage_details.page = 1;
    if (queryString.page !== null) {
      storage_details.page = parseInt(queryString.page);
    }

    setClickedItem(storage_details);
  }

  const showSwatches = drupalSettings.reactTeaserView.swatches.showSwatches;
  const collectionLabel = [];
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

  return (
    <div className="c-products__item views-row" >
      <article
        className="node--view-mode-search-result"
        data-sku={hit.sku}
        data-vmode="search_result"
        data-insights-object-id={hit.objectID}
        data-insights-position={hit.__position}
        data-insights-query-id={hit.__queryID}
        gtm-type="gtm-product-link"
        {...hit.gtm}
      >
        <div className="field field--name-field-skus field--type-sku field--label-hidden field__items">
          <a
            href={`${hit.url}`}
            data--original-url={`${hit.url}`}
            onClick={(event) => localStorageStore(event)}
            className="list-product-gallery product-selected-url">
            <Gallery media={hit.media} title={hit.title} />
          </a>
          <div className="product-plp-detail-wrapper">
            { collectionLabel.length > 0 &&
              <div className="product-labels">
                <ul className="collection-labels">
                  {labelItems}
                </ul>
              </div>
            }
            <h2 className="field--name-name">
              <a href={`${hit.url}`} className="product-selected-url">
                <div className="aa-suggestion">
                  <span className="suggested-text">
                    {hit.title}
                  </span>
                </div>
              </a>
            </h2>
            {hit.rendered_price ?
               Parser(hit.rendered_price)
            :
              <Price price={hit.original_price} final_price={hit.final_price} />
            }
            <Promotions promotions={hit.promotions}/>
            {showSwatches ? <Swatches swatches={hit.swatches} url={hit.url} /> : null}
          </div>
        </div>
        <Lables labels={hit.product_labels} sku={hit.sku} />
      </article>
    </div>
  );
};

export default Teaser;
