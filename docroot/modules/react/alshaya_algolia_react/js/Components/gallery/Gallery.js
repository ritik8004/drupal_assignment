import React from 'react';
import ImageElement from './ImageElement';

const Gallery = (props) => {
  return (
    <div className="alshaya_search_gallery">
      <div className="alshaya_search_mainimage">
        <ImageElement />
      </div>
      <div className="alshaya_search_hoverimage">
        <ImageElement />
      </div>
    </div>
  );
};

export default Gallery;
