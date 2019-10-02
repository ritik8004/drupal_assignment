import React from 'react';
import { ImageWrapper } from '../imageHelper/ImageWrapper';

const AssetGallery = ({media, title}) => {
  const mainImage = media.length > 0 ? media.shift() : {};
  const hoverImage = media.length > 0 ? media.shift() : {};

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
