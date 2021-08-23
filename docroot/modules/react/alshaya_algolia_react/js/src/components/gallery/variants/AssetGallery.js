import React from 'react';
import ImageElement from '../imageHelper/ImageElement';
import Lozenges from '../../../../common/components/lozenges';

const AssetGallery = ({
  media, title, labels, sku,
}) => {
  if (typeof media === 'undefined') {
    return (null);
  }
  // Clone media items, so that .shift() deletes items from
  // cloned array, keep original array reusable on state change.
  const images = [...media];
  const mainImage = images.length > 0 ? images.shift() : {};
  // Dimensions.
  let mWidth = null;
  let mHeight = null;
  if (typeof mainImage.width !== 'undefined') {
    mWidth = mainImage.width;
    mHeight = mainImage.height;
  }

  const hoverImage = images.length > 0 ? images.shift() : {};
  // Dimensions.
  let hWidth = null;
  let hHeight = null;
  if (typeof hoverImage.width !== 'undefined') {
    hWidth = hoverImage.width;
    hHeight = hoverImage.height;
  }

  const mainImageUrl = typeof mainImage.url !== 'undefined' ? mainImage.url : '';

  return (
    <div className="alshaya_search_gallery">
      <div className="alshaya_search_mainimage" data-sku-image={`${mainImageUrl}`}>
        <ImageElement
          src={mainImageUrl}
          title={title}
          loading="lazy"
          width={mWidth}
          height={mHeight}
        />
      </div>
      <div className="alshaya_search_hoverimage">
        <ImageElement
          src={typeof hoverImage.url !== 'undefined' ? hoverImage.url : ''}
          title={title}
          loading="lazy"
          width={hWidth}
          height={hHeight}
        />
      </div>
      <Lozenges labels={labels} sku={sku} />
    </div>
  );
};

export default AssetGallery;
