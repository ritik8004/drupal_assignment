import React from 'react';
import ImageElement from '../imageHelper/ImageElement';

const SingleImageGallery = (props) => {
  const images = [...props.media];
  const mainImage = images.length > 0 ? images.shift() : {};

  return(
    <div className="alshaya_search_gallery">
      <div className='alshaya_search_mainimage'>
        <ImageElement
          src={drupalSettings.reactTeaserView.gallery.lazy_load_placeholder}
          data-src={typeof mainImage.url != 'undefined' ? mainImage.url : ''}
          title={props.title}
          className='b-lazy'
        />
      </div>
    </div>
  );
}

export default SingleImageGallery;
