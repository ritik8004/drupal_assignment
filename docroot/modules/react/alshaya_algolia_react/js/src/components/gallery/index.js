import React from 'react';
import AssetGallery from './variants/AssetGallery';
import SearchGallery from './variants/SearchGallery';
import { isMobile } from '../../utils';
import ImageElement from './imageHelper/ImageElement';

const Gallery = (props) => {
  if (typeof props.media === 'undefined') {
    return (null);
  }

  if (isMobile()) {
    const images = [...props.media];
    const mainImage = images.length > 0 ? images.shift() : {};

    return (
      <div className="alshaya_search_gallery">
        <div className='alshaya_search_mainimage'>
          <ImageElement
            src={drupalSettings.reactTeaserView.gallery.lazy_load_placeholder}
            data-src={ typeof mainImage.url != 'undefined' ? mainImage.url : '' }
            title={props.title}
            className='b-lazy'
          />
        </div>
      </div>
    );
  }
  else {
    if (drupalSettings.reactTeaserView.gallery.showHoverImage) {
      return (<AssetGallery {...props}/>);
    }
    else if (drupalSettings.reactTeaserView.gallery.showThumbnails){
      return (<SearchGallery {...props}/>);
    }
    else {
      return (<AssetGallery {...props} />);
    }
  }

};

export default Gallery;
