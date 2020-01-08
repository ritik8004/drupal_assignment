import React from 'react';
import ImageElement from '../../gallery/imageHelper/ImageElement';

// Swatch type to be displayed with "ColorFilter".
export default function SwatchList({ swatch, label }) {
  if (typeof swatch == 'undefined') {
    return (null);
  }
  const [swatch_type, swatch_data] = swatch.trim().split(':');

  switch(swatch_type) {
    case 'swatch_color':
      return (<span className={`swatch swatch-color swatch-color-${swatch_data.substr(1)}`} style={{ backgroundColor: swatch_data}}></span>);

    case 'swatch_image':
      return (
        <span className="swatch swatch-image">
          <ImageElement src={swatch_data} title={label} />
        </span>
      );

    case 'swatch_text':
    default:
      return (
        <span className="swatch swatch-text">{swatch_data}</span>
      );
  }
}
