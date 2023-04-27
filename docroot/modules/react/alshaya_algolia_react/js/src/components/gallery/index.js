import React from 'react';
import AssetGallery from './variants/AssetGallery';
import SearchGallery from './variants/SearchGallery';
import SingleImageGallery from './variants/SingleImageGallery';
import { isDesktop } from '../../../../../js/utilities/display';

const Gallery = (props) => {
  const { media } = props;
  if (typeof media === 'undefined') {
    return (null);
  }

  const { swipeImage, gallery } = drupalSettings.reactTeaserView;

  if (!isDesktop() && swipeImage.enableSwipeImageMobile) {
    return (<SearchGallery {...props} />);
  }

  if (gallery.showHoverImage) {
    return (<AssetGallery {...props} />);
  }

  if (gallery.showThumbnails) {
    return (<SearchGallery {...props} />);
  }

  return (<SingleImageGallery {...props} />);
};

export default Gallery;
