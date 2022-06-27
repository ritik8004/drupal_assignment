import React from 'react';
import ImageElement from '../imageHelper/ImageElement';
import Lozenges from '../../../../common/components/lozenges';

const AssetGallery = ({
  media, title, labels, sku,
}) => {
  if (!media) {
    return (null);
  }
  // Clone media items, so that .shift() deletes items from
  // cloned array, keep original array reusable on state change.
  const images = [...media];
  const mainImage = images.length > 0 ? images.shift() : {};
  const hoverImage = images.length > 0 ? images.shift() : {};
  const mainImageUrl = mainImage.url || '';

  let galleryClass = 'alshaya_search_gallery';
  if (hoverImage.url) {
    galleryClass += ' lazy-hover';
  }

  return (
    <div className={galleryClass}>
      <div className="alshaya_search_mainimage" data-sku-image={mainImageUrl}>
        <ImageElement
          src={mainImageUrl}
          title={title}
          loading="lazy"
        />
      </div>
      {
        hoverImage.url
          ? (
            <div className="alshaya_search_hoverimage">
              <ImageElement
                src={hoverImage.url || ''}
                title={title}
                loading="lazy"
              />
            </div>
          )
          : ''
      }
      <Lozenges labels={labels} sku={sku} />
    </div>
  );
};

export default AssetGallery;
