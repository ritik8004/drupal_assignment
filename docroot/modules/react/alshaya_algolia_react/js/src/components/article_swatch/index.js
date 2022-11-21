import React, { useState } from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isMobile } from '../../../../../js/utilities/display';
import getSingleProductByColorSku from '../../utils/articleSwatchUtil';

const ArticleSwatches = ({
  sku, articleSwatches, url, handleSwatchSelect,
}) => {
  const [selectedSwatch, setActiveSwatch] = useState(sku);
  const [disabled, setDisabled] = useState({});
  if (typeof articleSwatches === 'undefined') {
    return null;
  }
  // Get plp color swatch limit for desktop/mobile view.
  const limit = (isMobile()) ? drupalSettings.reactTeaserView.swatches.swatchPlpLimitMobileView
    : drupalSettings.reactTeaserView.swatches.swatchPlpLimit;

  const totalNoOfSwatches = articleSwatches.length;
  const diff = totalNoOfSwatches - limit;
  let swatchMoreText = null;

  if (diff > 0) {
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

    setActiveSwatch(swatch.article_sku_code);
    const response = await getSingleProductByColorSku(swatch.article_sku_code);
    if (hasValue(response)) {
      const price = window.commerceBackend.getPrices(response[0], true);
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

  return (
    <div className="article-swatch-wrapper">
      <div className="swatches">
        {articleSwatches.slice(0, limit).map(
          (swatch) => (
            <button
              onClick={(e) => showSelectedSwatchProduct(e, swatch)}
              type="button"
              className={selectedSwatch === swatch.article_sku_code ? 'article-swatch active' : 'article-swatch'}
              key={swatch.article_sku_code}
              style={{ backgroundColor: swatch.rgb_color }}
              disabled={disabled[swatch.article_sku_code]}
            />
          ),
        )}
        {swatchMoreText}
      </div>
    </div>
  );
};

export default ArticleSwatches;
