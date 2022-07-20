import React, { useState } from 'react';
import Parser from 'html-react-parser';
import Gallery from '../gallery';
import Price from '../price';
import PromotionsFrame from '../promotion-frame';
import { storeClickedItem } from '../../utils';
import Swatches from '../swatch';
import AddToBagContainer from '../../../../../js/utilities/components/addtobag-container';
import WishlistContainer from '../../../../../js/utilities/components/wishlist-container';
import ConditionalView from '../../../common/components/conditional-view';
import DisplayStar from '../stars';
import {
  isProductElementAlignmentEnabled,
  isProductFrameEnabled,
  isProductTitleTrimEnabled,
  isPromotionFrameEnabled,
  productListIndexStatus,
  hasPriceRange,
} from '../../utils/indexUtils';
import Promotions from '../promotions';
import { checkExpressDeliveryStatus, isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';
import { isWishlistPage } from '../../../../../js/utilities/wishlistHelper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ExpressDeliveryLabel from './ExpressDeliveryLabel';
import PriceRangeElement from '../price/PriceRangeElement';

const Teaser = ({
  hit, gtmContainer = null, pageType, extraInfo,
  // 'extraInfo' is used to pass additional information that
  // we want to use in this component.
}) => {
  const { showSwatches } = drupalSettings.reactTeaserView.swatches;
  const { showReviewsRating } = drupalSettings.algoliaSearch;
  const collectionLabel = [];
  const [initSlider, setInitiateSlider] = useState(false);
  const [slider, setSlider] = useState(false);
  const isDesktop = window.innerWidth > 1024;
  const { currentLanguage } = drupalSettings.path;
  const { showBrandName } = drupalSettings.reactTeaserView;

  if (drupalSettings.plp_attributes
    && drupalSettings.plp_attributes.length > 0
    && hasValue(hit.collection_labels)
  ) {
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
  let overallRating = (hit.attr_bv_average_overall_rating !== undefined) ? hit.attr_bv_average_overall_rating : '';
  if (pageType === 'plp' && productListIndexStatus()) {
    overallRating = overallRating[currentLanguage];
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
      } else {
        // If the value for current language code does not exist
        // then use value from first available language code.
        attribute[key] = value[Object.keys(value)[0]];
      }
    } else {
      attribute[key] = value;
    }
    // Update URL to relative URL to avoid host mismatch issue.
    if (key === 'url') {
      // Check if the URL is an absolute URL or not.
      const isAbsolute = (attribute.url.indexOf('://') > 0 || attribute.url.indexOf('//') === 0);
      if (isAbsolute) {
        attribute[key] = new URL(attribute.url).pathname;
      } else {
        attribute[key] = attribute.url[0] !== '/' ? `/${attribute.url}` : attribute.url;
      }
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
            styles: label.styles,
          },
          position: label.position,
        });
      });
    } else {
      labels = attribute.product_labels;
    }
  }


  let teaserClass = 'c-products__item views-row';
  if (isProductFrameEnabled()) {
    teaserClass = `${teaserClass} product-frame`;
  }

  if (isPromotionFrameEnabled()) {
    teaserClass = `${teaserClass} promotion-frame`;
  }

  if (isProductTitleTrimEnabled()) {
    teaserClass = `${teaserClass} product-title-trim`;
  }

  if (isProductElementAlignmentEnabled()) {
    teaserClass = `${teaserClass} product-element-alignment`;
  }

  const showRating = (hit.attr_bv_total_review_count !== undefined
    && hit.attr_bv_total_review_count > 0
    && showReviewsRating !== undefined
    && showReviewsRating === 1
    && overallRating !== '');

  // Check if it's a wishlist page and stock status
  // of the sku available in extraInfo data. If stock
  // status says OOS then show the OOS button.
  const showOOSButton = (isWishlistPage(extraInfo)
    && typeof extraInfo.inStock !== 'undefined'
    && !extraInfo.inStock);

  // Create a ref for wishlist icon button. This ref will be used to update the
  // icon in teaser when product is added to wishlist from drawer.
  const ref = React.createRef();

  // Index name is required for algolia analytics.
  // Set index name from search settings and pass in data attributes in teaser.
  let { indexName } = drupalSettings.algoliaSearch.search;
  if (isWishlistPage(extraInfo)) {
    // If user has visited wish-list page then get index name
    // from wishlist settings.
    ({ indexName } = drupalSettings.wishlist);
  } else if (pageType === 'plp' && productListIndexStatus()) {
    // If user has visited plp then get index name from listing.
    ({ indexName } = drupalSettings.algoliaSearch.listing);
  }

  // Check if price is a range or single value.
  let renderPrice = '';
  if (!hasPriceRange(attribute.alshaya_price_range)) {
    renderPrice = hasValue(attribute.rendered_price)
      ? Parser(attribute.rendered_price)
      : <Price price={attribute.original_price} final_price={attribute.final_price} />;
  } else {
    renderPrice = <PriceRangeElement priceRange={attribute.alshaya_price_range} />;
  }

  return (
    <div className={teaserClass}>
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
        data-insights-index={indexName}
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

              media={hit.media}
              title={attribute.title}
              labels={labels}
              sku={hit.sku}
              initSlider={initSlider}
              setSlider={setSlider}
            />
          </a>
          {/* Render the component if the page isn't wishlist listing page. */}
          <ConditionalView condition={!isWishlistPage(extraInfo)}>
            <WishlistContainer
              context="wishlist"
              position="top-right"
              sku={hit.sku}
              title={attribute.title && Parser(attribute.title)}
              format="icon"
              setWishListButtonRef={ref}
            />
          </ConditionalView>
          <div className="product-plp-detail-wrapper">
            { collectionLabel.length > 0
              && (
              <div className="product-labels">
                <ul className="collection-labels">
                  {labelItems}
                </ul>
              </div>
              )}
            <ConditionalView condition={showBrandName && attribute.attr_brand_name !== undefined}>
              <div className="listing-brand-name">
                {attribute.attr_brand_name}
              </div>
            </ConditionalView>
            <ConditionalView condition={showBrandName && attribute.attr_brand_name === undefined}>
              <div className="alignment-placeholder" />
            </ConditionalView>
            <h2 className="field--name-name">
              <a href={attribute.url} className="product-selected-url">
                <div className="aa-suggestion">
                  <span className="suggested-text" title={attribute.title && Parser(attribute.title)}>
                    {attribute.title && Parser(attribute.title)}
                  </span>
                </div>
              </a>
            </h2>

            {/* Adding placeholder div for alignment
            if rating is not available and boots frame feature is enabled. */}
            <ConditionalView condition={!showRating && isProductElementAlignmentEnabled()}>
              <div className="alignment-placeholder" />
            </ConditionalView>

            <ConditionalView condition={showRating}>
              <div className="listing-inline-star">
                <DisplayStar starPercentage={overallRating} />
                (
                {hit.attr_bv_total_review_count}
                )
              </div>
            </ConditionalView>
            {/* Render price based on range/single price conditionals */}
            {renderPrice}
            <ConditionalView condition={isPromotionFrameEnabled()}>
              <PromotionsFrame promotions={attribute.promotions} />
            </ConditionalView>
            <ConditionalView condition={!isPromotionFrameEnabled()}>
              <Promotions promotions={attribute.promotions} />
            </ConditionalView>
            {showSwatches ? <Swatches swatches={attribute.swatches} url={attribute.url} /> : null}
          </div>
          <ConditionalView condition={
              isExpressDeliveryEnabled()
              && checkExpressDeliveryStatus()
              && hit.attr_express_delivery !== undefined
              && hit.attr_express_delivery[0] === '1'
            }
          >
            <ExpressDeliveryLabel />
          </ConditionalView>
          <ConditionalView condition={
              isExpressDeliveryEnabled()
              && checkExpressDeliveryStatus()
              && pageType === 'plp'
              && hit.attr_express_delivery !== undefined
              && hit.attr_express_delivery[currentLanguage][0] === '1'
            }
          >
            <ExpressDeliveryLabel />
          </ConditionalView>
        </div>
        {/* Don't render component on wishlist page if product is OOS. */}
        <ConditionalView condition={!showOOSButton}>
          <AddToBagContainer
            url={attribute.url}
            sku={hit.sku}
            stockQty={hit.stock_quantity}
            productData={attribute.atb_product_data}
            isBuyable={attribute.is_buyable}
            // Pass extra information to the component for update the behaviour.
            extraInfo={extraInfo}
            wishListButtonRef={ref}
            styleCode={hit.attr_style_code ? hit.attr_style_code : null}
          />
        </ConditionalView>
        {/* Render OOS message on wishlist page if product is OOS. */}
        <ConditionalView condition={showOOSButton}>
          <div className="oos-text-container">
            <span className="oos-text">
              { Drupal.t('Out of stock') }
            </span>
          </div>
        </ConditionalView>
        {/* Render the component if the page is wishlist listing page. */}
        <ConditionalView condition={isWishlistPage(extraInfo)}>
          <WishlistContainer
            context="wishlist_page"
            position="top-right"
            sku={hit.sku}
            format="link"
          />
        </ConditionalView>
      </article>
    </div>
  );
};

export default Teaser;
