import React from 'react';
import ImageElement from '../components/gallery/imageHelper/ImageElement';

export default function Swatch({ swatch, label }) {
  if (typeof swatch == 'undefined') {
    return (null);
  }
  const [swatch_type, swatch_data] = swatch.trim().split(':');

  switch(swatch_type) {
    case 'swatch_color':
      return (<span class={`swatch swatch-color swatch-color-${swatch_data.substr(1)}`} style={{ backgroundColor: swatch_data}}></span>);
      break;

    case 'swatch_image':
      return (
        <span class="swatch swatch-image">
          <ImageElement src={swatch_data} title={label} />
        </span>
      );
      break;

    case 'swatch_text':
      return (
        <span class="swatch swatch-text">{swatch_data}</span>
      );
      break;
  }
}
