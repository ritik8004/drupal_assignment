import React, { useState } from 'react';
import Parser from 'html-react-parser';
import Gallery from '../gallery';
import Price from '../price';
import Promotions from '../promotions';
import { storeClickedItem } from '../../utils';
import Swatches from '../swatch';
import AddToBagContainer from '../../../../../js/utilities/components/addtobag-container';
import ConditionalView from '../../../common/components/conditional-view';
import DisplayStar from '../stars';
import { productListIndexStatus } from '../../utils/indexUtils';

const Teaser = ({
  hit, gtmContainer = null, pageType,
}) => {
  const { showSwatches } = drupalSettings.reactTeaserView.swatches;
  const { showReviewsRating } = drupalSettings.algoliaSearch;
  const collectionLabel = [];
  const [initSlider, setInitiateSlider] = useState(false);
  const [slider, setSlider] = useState(false);
  const isDesktop = window.innerWidth > 1024;
  const { currentLanguage } = drupalSettings.path;
  if (drupalSettings.plp_attributes && drupalSettings.plp_attributes.length > 0) {
    const { plp_attributes: plpAttributes } = drupalSettings;
    for (let i = 0; i < plpAttributes.length; i++) {
      let collectionLabelValue = hit.collection_labels[plpAttributes[i]];
      if (pageType === 'plp' && productListIndexStatus() && hit.collection_labels[currentLanguage]) {
        collectionLabelValue = hit.collection_labels[currentLanguage][plpAttributes[i]];
      }
      if (hit && hit.collection_labels && collectionLabelValue) {
        collectionLabel.push({
          class: plpAttributes[i],
          value: collectionLabelValue,
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
  const attribute = [];
  Object.entries(hit).forEach(([key, value]) => {
    if (pageType === 'plp'
      && productListIndexStatus()
      && value !== null) {
      if (value[currentLanguage] !== undefined) {
        attribute[key] = value[currentLanguage];
      }
    } else {
      attribute[key] = value;
    }
  });
  // Skip if there is no value for current language.
  if (attribute.title === undefined) {
    return null;
  }

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
        {...overridenGtm}
        onMouseEnter={() => {
          if (isDesktop) {
            setInitiateSlider(true);
            if (slider !== false) {
              slider.slickGoTo(0, true);
              slider.slickPlay();
            }
          }
        }}
        onMouseLeave={() => {
          if (isDesktop && (slider !== false)) {
            slider.slickPause();
          }
        }}
      >
        <div className="field field--name-field-skus field--type-sku field--label-hidden field__items">
          <a
            href={`${attribute.url}`}
            data--original-url={`${attribute.url}`}
            className="list-product-gallery product-selected-url"
          >
            <Gallery

              media={attribute.media}
              title={attribute.title}
              labels={attribute.product_labels}
              sku={hit.sku}
              initSlider={initSlider}
              setSlider={setSlider}
            />
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
              <a href={attribute.url} className="product-selected-url">
                <div className="aa-suggestion">
                  <span className="suggested-text">
                    {attribute.title && Parser(attribute.title)}
                  </span>
                </div>
              </a>
            </h2>
            <ConditionalView condition={
                hit.attr_bv_total_review_count !== undefined
                && hit.attr_bv_total_review_count > 0
                && showReviewsRating !== undefined
                && showReviewsRating === 1
              }
            >
              <div className="listing-inline-star">
                <DisplayStar starPercentage={hit.attr_bv_average_overall_rating} />
                (
                {hit.attr_bv_total_review_count}
                )
              </div>
            </ConditionalView>
            {attribute.rendered_price
              ? Parser(attribute.rendered_price)
              : <Price price={attribute.original_price} final_price={attribute.final_price} />}
            <Promotions promotions={attribute.promotions} />
            {showSwatches ? <Swatches swatches={attribute.swatches} url={attribute.url} /> : null}
          </div>
        </div>
        <AddToBagContainer
          url={attribute.url}
          sku={hit.sku}
          stockQty={hit.stock_quantity}
          productData={attribute.atb_product_data}
        />
      </article>
    </div>
  );
};

export default Teaser;
