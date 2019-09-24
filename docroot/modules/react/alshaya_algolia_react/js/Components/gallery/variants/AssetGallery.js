import React from 'react';
import ImageElement from '../ImageElement';

function getImageWithWrapper(imageObj, title, classname, showDefaultImage = false) {
  if (typeof imageObj != 'undefined' && typeof imageObj.url != 'undefined') {
    return (
      <div className={classname}>
        <ImageElement src={ imageObj.url } title={title} />
      </div>
    );
  }

  if (showDefaultImage && drupalSettings.reactTeaserView.gallery.defaultImage) {
    return (
      <div className={classname}>
        <ImageElement src={ drupalSettings.reactTeaserView.gallery.defaultImage } title={title} />
      </div>
    );
  }

  return '';
}

const AssetGallery = ({media, title}) => {
  const mainImage = media.length > 0 ? media[0] : {};
  const mainImageWrapper = getImageWithWrapper(mainImage, title, "alshaya_search_mainimage", true);

  media.shift();
  const hoverImage = media[0];
  const hoverImageWrapper = getImageWithWrapper(hoverImage, title, "alshaya_search_hoverimage");

  return (
    <div className="alshaya_search_gallery">
      {mainImageWrapper}
      {hoverImageWrapper}
    </div>
  );
}

export default AssetGallery;
