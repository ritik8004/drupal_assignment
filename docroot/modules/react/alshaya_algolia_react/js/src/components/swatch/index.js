import React from 'react';
import ImageElement from '../gallery/imageHelper/ImageElement';

export const Swatch = ({ swatch, url, title }) => {
  let selectedImage = `${url}?selected=${swatch.child_id}`;
  if (swatch.url !== undefined) {
    selectedImage = swatch.url;
  }

  /**
   * Push swatch gtm data on swatch click on plp.
   */
  const handleSwatchClick = (e) => {
    e.preventDefault();
    const productData = {
      sku: swatch.child_sku_code,
      gtm_name: title,
      color: swatch.display_label,
    };
    Drupal.alshayaSeoGtmPushSwatchClick(productData);
    window.location.href = selectedImage;
  };

  return (
    <a href="#" onClick={(e) => handleSwatchClick(e)}>
      <span className="swatch-block swatch-image">
        {swatch.product_image_url
          ? <ImageElement data-sku-image={swatch.product_image_url} src={swatch.image_url} loading="lazy" />
          : <ImageElement src={swatch.image_url} loading="lazy" />}
      </span>
    </a>
  );
};

const Swatches = ({ swatches, url, title }) => {
  if (typeof swatches === 'undefined') {
    return null;
  }

  // Display the colors count for mobile only if different variants images
  // being shown in gallery on PLP.
  const showVariantsThumbnailPlpGallery = drupalSettings
    .reactTeaserView.swatches.showVariantsThumbnail;
  // Display the configured number of swatches.
  const limit = drupalSettings.reactTeaserView.swatches.swatchPlpLimit;
  const totalNoOfSwatches = swatches.length;
  const diff = totalNoOfSwatches - limit;

  let swatchMoreText = '+ ';
  if (diff > 0) {
    swatchMoreText = (diff === 1) ? swatchMoreText + Drupal.t('1 color') : swatchMoreText + Drupal.t('@swatch_count colors', { '@swatch_count': diff });
  } else {
    swatchMoreText = null;
  }

  let swatchColorCount;
  if (totalNoOfSwatches > 0) {
    swatchColorCount = (totalNoOfSwatches === 1) ? Drupal.t('1 color') : Drupal.t('@swatch_count colors', { '@swatch_count': totalNoOfSwatches });
  } else {
    swatchColorCount = null;
  }

  let swatcheContainer;
  if (totalNoOfSwatches > 0 && !showVariantsThumbnailPlpGallery) {
    swatcheContainer = (
      <div className="swatches">
        {swatches.slice(0, limit).map(
          (swatch) => <Swatch swatch={swatch} key={swatch.child_id} url={url} title={title} />,
        )}
        {(diff > 0) ? <a className="swatch-more-link product-selected-url" href={url}>{swatchMoreText}</a> : null}
      </div>
    );
  } else if (totalNoOfSwatches > 0) {
    swatcheContainer = (
      <div className="swatches">
        <div className="swatch-color-count-wrapper mobile-only-block">
          <a
            className="swatch-color-count product-selected-url"
            href={url}
          >
            {swatchColorCount}
          </a>
        </div>
      </div>
    );
  } else {
    swatcheContainer = null;
  }

  return (swatcheContainer
    ? (
      <>
        {swatcheContainer}
      </>
    )
    : null
  );
};

export default Swatches;
