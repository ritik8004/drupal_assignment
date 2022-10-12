import React, { useState } from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getSingleProductByColorSku } from '../../utils/articleSwatchUtil';

const ArticleSwatches = ({
  sku, articleSwatches, url, handleSwatchSelect,
}) => {
  const [selectedSwatch, setActiveSwatch] = useState(sku);
  if (typeof articleSwatches === 'undefined') {
    return null;
  }

  const limit = drupalSettings.reactTeaserView.swatches.swatchPlpLimit;
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
  const showSelectedSwatchProduct = async (e, skuCode) => {
    e.preventDefault();
    setActiveSwatch(skuCode);
    const response = await getSingleProductByColorSku(skuCode);
    if (hasValue(response)) {
      const price = window.commerceBackend.getPrices(response[0], true);
      const productData = {
        sku: skuCode,
        media: response[0].article_media_gallery,
        name: response[0].name,
        url: response[0].url_key,
        priceData: price,
      };
      handleSwatchSelect(productData);
    } else {
      // If graphQl API is returning Error.
      Drupal.alshayaLogger('error', 'Error while calling the graph ql to fetch product info for sku: @sku', {
        '@sku': sku,
      });
    }
  };

  return (
    <div className="article-swatch-wrapper">
      <div className="swatches">
        {articleSwatches.map(
          (swatch) => (
            <button
              onClick={(e) => showSelectedSwatchProduct(e, swatch.article_sku_code)}
              type="button"
              className={selectedSwatch === swatch.article_sku_code ? 'article-swatch active' : 'article-swatch'}
              key={swatch.article_sku_code}
              style={{ backgroundColor: swatch.rgb_color }}
            />
          ),
        )}
        {swatchMoreText}
      </div>
    </div>
  );
};

export default ArticleSwatches;
