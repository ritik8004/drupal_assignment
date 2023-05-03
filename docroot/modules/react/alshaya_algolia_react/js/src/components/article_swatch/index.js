import React, { useState } from 'react';
import Slider from 'react-slick';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isMobile } from '../../../../../js/utilities/display';
import getSingleProductByColorSku from '../../utils/articleSwatchUtil';
import { getFormattedPrice } from '../../../../../js/utilities/price';
import ImageElement from '../gallery/imageHelper/ImageElement';

const ArticleSwatches = ({
  sku, articleSwatches, url, handleSwatchSelect,
}) => {
  if (!hasValue(articleSwatches[0])) {
    return null;
  }

  let currentSku = sku;
  const { showColorSwatchSlider } = drupalSettings.reactTeaserView.swatches;
  // Assign the current sku based on default flag for color swatches.
  if (hasValue(showColorSwatchSlider)) {
    Object.values(articleSwatches).forEach((articleSwatch) => {
      if (hasValue(articleSwatch.default)) {
        currentSku = articleSwatch.article_sku_code;
      }
    });
  }
  const [selectedSwatch, setActiveSwatch] = useState(currentSku);
  const [disabled, setDisabled] = useState({});
  // Get plp color swatch limit for desktop/mobile view.
  // Adding 0.5 to the mobile limit to show half of the next slide.
  let swatchesLimit = (isMobile())
    ? drupalSettings.reactTeaserView.swatches.swatchPlpLimitMobileView + 0.5
    : drupalSettings.reactTeaserView.swatches.swatchPlpLimit;

  const totalNoOfSwatches = articleSwatches.length;
  const diff = totalNoOfSwatches - swatchesLimit;
  let swatchMoreText = null;
  const swatchTypeClass = `swatch-${drupalSettings.reactTeaserView.swatches.articleSwatchType}`;

  const sliderSettings = {
    infinite: false,
    slidesToShow: swatchesLimit,
    // Scrolling -1 for desktop slider to compensate previous arrow overlap.
    slidesToScroll: isMobile()
      ? drupalSettings.reactTeaserView.swatches.swatchPlpLimitMobileView
      : drupalSettings.reactTeaserView.swatches.swatchPlpLimit - 1,
  };

  // Show all color swatches with slider when total number of swatches
  // is greater than max number swatches to display.
  if (hasValue(showColorSwatchSlider) && diff > 0) {
    swatchesLimit = totalNoOfSwatches;
  }

  if (diff > 0 && !showColorSwatchSlider) {
    swatchMoreText = (
      <a className="more-color-swatch" href={url}>
        {' '}
        +
        {diff}
      </a>
    );
  }

  // Update content for the product as per selected swatch item.
  const showSelectedSwatchProduct = async (e, swatch) => {
    e.preventDefault();

    // Don't call the graphql query when selected SKU is clicked.
    if (selectedSwatch === swatch.article_sku_code) {
      return;
    }
    setActiveSwatch(swatch.article_sku_code);
    const response = await getSingleProductByColorSku(swatch.article_sku_code);
    if (hasValue(response)) {
      const price = showColorSwatchSlider
        ? {
          price: getFormattedPrice(response[0].price_range.maximum_price.regular_price.value),
          finalPrice: getFormattedPrice(response[0].price_range.maximum_price.final_price.value),
          percent_off: response[0].price_range.maximum_price.discount.percent_off,
        }
        : window.commerceBackend.getPrices(response[0], true);
      const productData = {
        sku: swatch.article_sku_code,
        media: response[0].article_media_gallery,
        name: response[0].name,
        gtm_name: response[0].gtm_attributes.name,
        url: Drupal.url(response[0].end_user_url),
        priceData: price,
        color: swatch.rgb_color,
      };
      handleSwatchSelect(productData);
      Drupal.alshayaSeoGtmPushSwatchClick(productData);
    } else {
      // Set disabled as true for current swatch for product goes oos
      // or any error.
      setDisabled({ [swatch.article_sku_code]: true });
      // If graphQl API is returning Error.
      Drupal.alshayaLogger('error', 'Error while calling the GraphQL to fetch product info for sku: @sku', {
        '@sku': sku,
      });
    }
  };

  const renderArticleSwatches = articleSwatches.slice(0, swatchesLimit).map(
    (swatch) => (
      <ArticleSwatch
        key={swatch.article_sku_code}
        swatch={swatch}
        selectedSwatch={selectedSwatch}
        disabled={disabled[swatch.article_sku_code]}
        showSelectedSwatchProduct={showSelectedSwatchProduct}
      />
    ),
  );

  return (
    <div className="article-swatch-wrapper">
      { showColorSwatchSlider
        ? (
          <Slider {...sliderSettings} className={`swatches swatch-slider ${swatchTypeClass} swatch-limit-${Math.floor(sliderSettings.slidesToShow)}`}>
            { renderArticleSwatches }
          </Slider>
        )
        : (
          <div className={`swatches ${swatchTypeClass}`}>
            { renderArticleSwatches }
            { swatchMoreText }
          </div>
        )}
    </div>
  );
};

const ArticleSwatch = ({
  swatch,
  selectedSwatch,
  disabled,
  showSelectedSwatchProduct,
}) => {
  // Render Image swatches when swatch_type is image.
  if (hasValue(swatch.swatch_type) && swatch.swatch_type === 'image') {
    return (
      <a href="#" onClick={(e) => showSelectedSwatchProduct(e, swatch)}>
        <span
          className={selectedSwatch === swatch.article_sku_code ? 'image-swatch active' : 'image-swatch'}
        >
          <ImageElement
            src={swatch.swatch_image}
            loading="lazy"
          />
        </span>
      </a>
    );
  }

  const colors = swatch.rgb_color.split('|');
  if (colors.length > 1) {
    return (
      <li
        type="button"
        className={selectedSwatch === swatch.article_sku_code ? 'article-swatch dual-color-tone active' : 'article-swatch dual-color-tone'}
        onClick={(e) => showSelectedSwatchProduct(e, swatch)}
      >
        <a href="#" style={{ backgroundColor: colors[0] }} />
        <a href="#" style={{ backgroundColor: colors[1] }} />
      </li>
    );
  }
  return (
    <button
      onClick={(e) => showSelectedSwatchProduct(e, swatch)}
      type="button"
      className={selectedSwatch === swatch.article_sku_code ? 'article-swatch active' : 'article-swatch'}
      style={{ backgroundColor: swatch.rgb_color }}
      disabled={disabled}
    />
  );
};

export default ArticleSwatches;
