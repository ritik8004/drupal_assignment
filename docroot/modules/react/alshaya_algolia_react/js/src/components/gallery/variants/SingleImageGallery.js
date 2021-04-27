import React from 'react';
import ImageElement from '../imageHelper/ImageElement';
import Lozenges from '../../../../common/components/lozenges';

const SingleImageGallery = (props) => {
  const {
    title, media, labels, sku,
  } = props;

  // Clone media items, so that .shift() deletes items from
  // cloned array, keep original array reusable on state change.
  const images = [...media];

  const mainImage = images.length > 0 ? images.shift() : {};
  const mainImageUrl = typeof mainImage.url !== 'undefined' ? mainImage.url : '';

  return (
    <div className="alshaya_search_gallery">
      <div className="alshaya_search_mainimage" data-sku-image={`${mainImageUrl}`}>
        <ImageElement
          src={drupalSettings.reactTeaserView.gallery.lazy_load_placeholder}
          data-src={mainImageUrl}
          title={title}
          className="b-lazy"
        />
        <Lozenges labels={labels} sku={sku} />
      </div>
    </div>
  );
};

export default SingleImageGallery;
