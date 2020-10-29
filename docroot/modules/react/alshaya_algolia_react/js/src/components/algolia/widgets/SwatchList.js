import React from 'react';
import ImageElement from '../../gallery/imageHelper/ImageElement';

// Swatch type to be displayed with "ColorFilter".
export default function SwatchList({ swatch, label }) {
  if (typeof swatch === 'undefined') {
    return (null);
  }
  const [swatchType, swatchData] = swatch.trim().split(':');

  switch (swatchType) {
    case 'swatch_color':
      return (<span className={`swatch swatch-color swatch-color-${swatchData.substr(1)}`} style={{ backgroundColor: swatchData }} />);

    case 'swatch_image':
      return (
        <span className="swatch swatch-image">
          <ImageElement src={swatchData} title={label} />
        </span>
      );

    case 'swatch_text':
    default:
      return (
        <span className="swatch swatch-text">{swatchData}</span>
      );
  }
}
