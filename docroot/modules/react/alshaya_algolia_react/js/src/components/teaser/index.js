import React, { useEffect, useState } from 'react';
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
import { isAddToBagHoverEnabled } from '../../../../../js/utilities/addToBagHelper';
import ArticleSwatches from '../article_swatch';
import SliderSwatch from '../slider-swatch';
import { getShoeAiStatus } from '../../../../../js/utilities/util';
import ProductTeaserAttributes from '../product-teaser-attributes';

const Teaser = ({
  hit, gtmContainer = null, pageType, extraInfo, indexName,
  // 'extraInfo' is used to pass additional information that
  // we want to use in this component.
}) => {
  const { showSwatches, showSliderSwatch } = drupalSettings.reactTeaserView.swatches;
  const { showColorSwatchSlider } = drupalSettings.reactTeaserView.swatches;
  const { showReviewsRating, plpTeaserAttributes } = drupalSettings.algoliaSearch;
  const collectionLabel = [];
  const [initSlider, setInitiateSlider] = useState(false);
  const [slider, setSlider] = useState(false);
  const [sku, setSkuCode] = useState(hit.sku);
  const [media, setSkuMedia] = useState(hit.media);
  const [mediaPdp] = useState(hit.media_pdp);
  const defaultChildId = (hasValue(hit.swatches) && hasValue(hit.swatches[0].child_id))
    ? hit.swatches[0].child_id
    : null;
  const [childId, setChildId] = useState(defaultChildId);
  const [updatedAttribute, setSwatchAttributeData] = useState({
    title: null,
    url: null,
    renderProductPrice: null,
  });
  const isDesktop = window.innerWidth > 1024;
  const { currentLanguage } = drupalSettings.path;
  const { showBrandName, swipeImage } = drupalSettings.reactTeaserView;
  const activateShoeAI = getShoeAiStatus();

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
  let overallRating = (Drupal.hasValue(hit.attr_bv_average_overall_rating)) ? hit.attr_bv_average_overall_rating : '';
  if (pageType === 'plp' && productListIndexStatus()) {
    overallRating = overallRating[currentLanguage];
  }
  let labelItems = '';
  if (collectionLabel.length > 0) {
    labelItems = collectionLabel.map((d) => <li className={d.class} key={d.value}>{d.value}</li>);
  }

  // Update product data as per selected color swatch.
  const handleSwatchSelect = (productData) => {
    setSkuCode(productData.sku);
    setSkuMedia(productData.media);
    setChildId(productData.child_id);
    const renderSkuPrice = hasValue(productData.priceData)
      ? (
        <Price
          price={productData.priceData.price}
          finalPrice={productData.priceData.finalPrice}
        />
      )
      : null;
    setSwatchAttributeData({
      ...updatedAttribute,
      title: productData.name,
      url: productData.url,
      renderProductPrice: renderSkuPrice,
    });
  };

  const overridenGtm = gtmContainer ? { ...hit.gtm, ...{ 'gtm-container': gtmContainer } } : hit.gtm;
  // Delete 'data-insights-query-id' key from overridenGtm object
  // as it is coming from algolia.
  if (typeof overridenGtm['data-insights-query-id'] !== 'undefined') {
    delete overridenGtm['data-insights-query-id'];
  }
  const attribute = [];
  Object.entries(hit).forEach(([key, value]) => {
    if (pageType === 'plp'
      && productListIndexStatus()
      && value !== null) {
      if (Drupal.hasValue(value[currentLanguage])) {
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

  const showRating = (Drupal.hasValue(hit.attr_bv_total_review_count)
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

  // Check if price is a range or single value.
  let renderPrice = '';
  if (!hasPriceRange(attribute.alshaya_price_range)) {
    renderPrice = hasValue(attribute.rendered_price)
      ? Parser(attribute.rendered_price)
      : (
        <Price
          price={attribute.original_price}
          finalPrice={attribute.final_price}
          fixedPrice={attribute.fixed_price}
        />
      );
  } else {
    renderPrice = <PriceRangeElement priceRange={attribute.alshaya_price_range} />;
  }

  let title = hasValue(updatedAttribute.title) ? updatedAttribute.title : attribute.title;
  title = title.toString();
  const url = hasValue(updatedAttribute.url) ? updatedAttribute.url : attribute.url;
  // Checking whether the green leaf attribute is present or not.
  let greenLeaf = null;
  if (hasValue(hit.attr_green_leaf)) {
    // Determining the green leaf flag for PLP and wishlist page.
    greenLeaf = typeof hit.attr_green_leaf[currentLanguage] !== 'undefined'
      ? hit.attr_green_leaf[currentLanguage]
      : hit.attr_green_leaf;
  }

  let dataVmode = null;
  if (pageType === 'search') {
    dataVmode = { 'data-vmode': 'search_result' };
  }

  // Show attributes on PLP product teaser.
  const plpProductCategoryAttributes = {};
  if (hasValue(plpTeaserAttributes)) {
    const attributeArr = plpTeaserAttributes.split(',');
    attributeArr.forEach((attr) => {
      plpProductCategoryAttributes[attr] = hit[attr];
    });
  }

  // Initialize slick carousel for touch devices.
  useEffect(() => {
    // Check if touch device, swipe Image is enabled, and Slick is initialized.
    if (!isDesktop && swipeImage.enableSwipeImageMobile && !initSlider) {
      setInitiateSlider(true);
    }
  }, []);

  return (
    <div className={teaserClass}>
      <article
        className="node--view-mode-search-result quick-view"
        onClick={(event) => storeClickedItem(event, pageType)}
        data-sku={sku}
        {...dataVmode}
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
            href={`${url}`}
            data--original-url={`${url}`}
            className="list-product-gallery product-selected-url"
          >
            <Gallery
              media={media}
              mediaPdp={mediaPdp}
              title={title}
              labels={labels}
              sku={sku}
              initSlider={initSlider}
              setSlider={setSlider}
            />
            {/* Render the green leaf icon for the sustainable products. */}
            {hasValue(greenLeaf) && greenLeaf
              && (
                <div className="labels-container bottom-right">
                  <span className="map-green-leaf" />
                </div>
              )}
          </a>
          {/* Render the component if the page isn't wishlist listing page. */}
          <ConditionalView condition={!isWishlistPage(extraInfo)}>
            <WishlistContainer
              context="wishlist"
              position="top-right"
              sku={sku}
              title={title && Parser(title)}
              format="icon"
              setWishListButtonRef={ref.current}
            />
          </ConditionalView>
          {pageType === 'plp' && activateShoeAI === true ? (
            <div
              className="ShoeSizeMe ssm_plp"
              data-shoeid={sku}
              data-availability={attribute.attr_size_shoe_eu}
              data-sizerun={attribute.attr_size_shoe_eu}
            />
          ) : null}
          {isAddToBagHoverEnabled()
            && (
            <div className="quick-add">
              <AddToBagContainer
                url={url}
                sku={sku}
                stockQty={hit.stock_quantity}
                productData={attribute.atb_product_data}
                isBuyable={attribute.is_buyable}
                // Pass extra information to the component for update the behaviour.
                extraInfo={extraInfo}
                wishListButtonRef={ref}
                styleCode={hit.attr_style_code ? hit.attr_style_code : null}
              />
            </div>
            )}
          {(hasValue(attribute.attr_article_swatches) && showColorSwatchSlider)
            ? (
              <ArticleSwatches
                sku={sku}
                handleSwatchSelect={handleSwatchSelect}
                articleSwatches={attribute.attr_article_swatches}
                url={url}
              />
            ) : null}
          <div className="product-plp-detail-wrapper">
            { collectionLabel.length > 0
              && (
              <div className="product-labels">
                <ul className="collection-labels">
                  {labelItems}
                </ul>
              </div>
              )}
            <ConditionalView condition={
                showBrandName
                && Drupal.hasValue(attribute.attr_brand_name)
              }
            >
              <div className="listing-brand-name">
                {attribute.attr_brand_name}
              </div>
            </ConditionalView>
            <ConditionalView condition={showBrandName && attribute.attr_brand_name === undefined}>
              <div className="alignment-placeholder" />
            </ConditionalView>
            <h2 className="field--name-name">
              <a href={url} className="product-selected-url">
                <div className="aa-suggestion">
                  <span className="suggested-text" title={title && Parser(title)}>
                    {title && Parser(title)}
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
            {hasValue(updatedAttribute.renderProductPrice)
              ? updatedAttribute.renderProductPrice : renderPrice}
            {/* Render attributes on PLP product teaser. */}
            {hasValue(plpProductCategoryAttributes) ? (
              <ProductTeaserAttributes
                plpProductCategoryAttributes={plpProductCategoryAttributes}
              />
            ) : null}
            <ConditionalView condition={isPromotionFrameEnabled()}>
              <PromotionsFrame promotions={attribute.promotions} />
            </ConditionalView>
            <ConditionalView condition={!isPromotionFrameEnabled()}>
              <Promotions promotions={attribute.promotions} />
            </ConditionalView>
            {/* Render the Article color swatches when showColorSwatchSlider is TRUE */}
            {showSwatches && !showColorSwatchSlider ? (
              <Swatches
                swatches={attribute.swatches}
                url={url}
                title={title}
                handleSwatchSelect={handleSwatchSelect}
                childId={childId}
              />
            ) : null}
            {showSliderSwatch ? (
              <SliderSwatch
                swatches={attribute.swatches}
                url={url}
                title={title}
                handleSwatchSelect={handleSwatchSelect}
                childId={childId}
              />
            ) : null}
            {/* Render color swatches based on article/sku id */}
            {(hasValue(attribute.article_swatches)
              && drupalSettings.reactTeaserView.swatches.showArticleSwatches)
              ? (
                <ArticleSwatches
                  sku={sku}
                  handleSwatchSelect={handleSwatchSelect}
                  articleSwatches={attribute.article_swatches}
                  url={url}
                />
              ) : null}
          </div>
          <ConditionalView condition={
              isExpressDeliveryEnabled()
              && checkExpressDeliveryStatus()
              && Drupal.hasValue(hit.attr_express_delivery)
              && hit.attr_express_delivery[0] === '1'
            }
          >
            <ExpressDeliveryLabel />
          </ConditionalView>
          <ConditionalView condition={
              isExpressDeliveryEnabled()
              && checkExpressDeliveryStatus()
              && pageType === 'plp'
              && Drupal.hasValue(hit.attr_express_delivery)
              && hit.attr_express_delivery[currentLanguage][0] === '1'
            }
          >
            <ExpressDeliveryLabel />
          </ConditionalView>
        </div>
        {/* Don't render component on wishlist page if product is OOS. */}
        <ConditionalView condition={!showOOSButton && !isAddToBagHoverEnabled()}>
          <AddToBagContainer
            url={url}
            sku={sku}
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
            sku={sku}
            format="link"
          />
        </ConditionalView>
      </article>
    </div>
  );
};

export default Teaser;
