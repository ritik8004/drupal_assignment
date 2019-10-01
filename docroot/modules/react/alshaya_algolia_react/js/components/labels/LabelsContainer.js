import React from 'react';
import ImageElement from '../gallery/imageHelper/ImageElement';

const LabelsContainer = ({labels, sku}) => {
  if (labels.length === 0) {
    return (null);
  }
  const labelItems = labels.map(({image, position}) => (
    <div class={`label ${position}`}>
      <ImageElement src={image.url} alt={image.alt} title={image.title} />
    </div>
  ));

  return (
    <div class="labels-container" data-type="plp" data-sku={sku} data-main-sku={sku}>
      {labelItems}
    </div>
  );
};

export default LabelsContainer;

