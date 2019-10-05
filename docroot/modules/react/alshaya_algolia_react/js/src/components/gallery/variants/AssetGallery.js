import React from 'react';
import { ImageWrapper } from '../imageHelper/ImageWrapper';

const AssetGallery = ({media, title}) => {
  // Clone media items, so that .shift() deletes items from
  // clonned array, keep original array reusable on state change.
  const images = [...media];
  const mainImage = images.length > 0 ? images.shift() : {};
  const hoverImage = images.length > 0 ? images.shift() : {};

  return (
    <div className="alshaya_search_gallery">
      <ImageWrapper
        src={ typeof mainImage.url != 'undefined' ? mainImage.url : '' }
        title={title}
        className='alshaya_search_mainimage'
        showDefaultImage={true}
      />
      <ImageWrapper
        src={ typeof hoverImage.url != 'undefined' ? hoverImage.url : '' }
        title={title}
        className='alshaya_search_hoverimage'
      />
    </div>
  );
}

export default AssetGallery;
