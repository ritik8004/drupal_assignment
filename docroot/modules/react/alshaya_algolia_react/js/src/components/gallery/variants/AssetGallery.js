import React from 'react';
import ImageElement from '../imageHelper/ImageElement';

const AssetGallery = ({media, title}) => {
  if (typeof media === 'undefined') {
    return (null);
  }
  // Clone media items, so that .shift() deletes items from
  // clonned array, keep original array reusable on state change.
  const images = [...media];
  const mainImage = images.length > 0 ? images.shift() : {};
  const hoverImage = images.length > 0 ? images.shift() : {};

  return (
    <div className="alshaya_search_gallery">
      <div className='alshaya_search_mainimage' ref={this.mainImageRef}>
        <ImageElement
          src={drupalSettings.reactTeaserView.gallery.lazy_load_placeholder}
          data-src={ typeof mainImage.url != 'undefined' ? mainImage.url : '' }
          title={title}
          className='b-lazy'
        />
      </div>
      <div className='alshaya_search_hoverimage' ref={this.mainImageRef}>
        <ImageElement
          src={drupalSettings.reactTeaserView.gallery.lazy_load_placeholder}
          data-src={ typeof hoverImage.url != 'undefined' ? hoverImage.url : '' }
          title={title}
          className='b-lazy'
        />
      </div>
    </div>
  );
}

export default AssetGallery;
