import React from 'react';
import { ImageWrapper } from '../imageHelper/ImageWrapper';

const AssetGallery = ({media, title}) => {
  const mainImage = media.length > 0 ? media.shift() : {};
  const mainImageWrapper = ImageWrapper(mainImage, title, "alshaya_search_mainimage", true);

  const hoverImage = media.length > 0 ? media.shift() : {};
  const hoverImageWrapper = ImageWrapper(hoverImage, title, "alshaya_search_hoverimage");

  return (
    <div className="alshaya_search_gallery">
      {mainImageWrapper}
      {hoverImageWrapper}
    </div>
  );
}

export default AssetGallery;
