import React from 'react';

const ArticleSwatches = ({ articleSwatches, url }) => {
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

  return (
    <div className="article-swatch-wrapper">
      <div className="swatches">
        {articleSwatches.map(
          (swatch) => (
            <button
              type="button"
              className="article-swatch"
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
