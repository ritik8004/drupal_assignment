import React from 'react';
import AssetGallery from './variants/AssetGallery';
import SearchGallery from './variants/SearchGallery';
import { isMobile } from '../../utils/utils';
import { ImageWrapper } from './imageHelper/ImageWrapper';

const Gallery = (props) => {
  if (typeof props.media === 'undefined') {
    return (null);
  }

  if (isMobile()) {
    const images = [...props.media];
    const mainImage = images.length > 0 ? images.shift() : {};

    return (
      <div className="alshaya_search_gallery">
        <ImageWrapper
          src={ typeof mainImage.url != 'undefined' ? mainImage.url : '' }
          title={props.title}
          className='alshaya_search_mainimage'
          showDefaultImage={true}
        />
      </div>
    );
  }
  else {
    if (drupalSettings.reactTeaserView.gallery.showHoverImage) {
      return (<AssetGallery {...props}/>);
    }
    else {
      return (<SearchGallery {...props}/>);
    }
  }

};

export default Gallery;
