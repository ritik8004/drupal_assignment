import React from 'react';
import { getImageUrl } from '../../../utilities';

/**
 * Show egift card hero image from selected item.
 */
const HeroImage = (props) => {
  const { item } = props;
  const { custom_attributes: customAttributes } = item || [];

  // @todo handle cards without image.
  const image = {
    url: typeof item.url !== 'undefined' ? item.url : getImageUrl(customAttributes, 'image'),
    title: item.name,
    alt: item.name,
  };

  return (
    <div className="hero-image-wrapper">
      <img
        src={image.url}
        alt={image.alt}
        title={image.title}
        className="hero-image"
      />
    </div>
  );
};

export default HeroImage;
