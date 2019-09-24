import React from 'react';
import ImageElement from '../ImageElement';

const SearchGallery = (props) => {
  const mainImage = props.media.length > 0 ? props.media[0] : {};

  const thumbnails = [];
  for (const [index, value] of props.media.shift()) {
    thumbnails.push(<li key={index} data-sku-id="{{ key }}"><ImageElement src={value.url} title={props.title} /></li>)
  }

  return (
    <div className="alshaya_search_gallery">
      <div className="alshaya_search_mainimage" data-sku-image={ mainImage.url }>
        <ImageElement src={ mainImage.url } title={props.title} />
      </div>
      <div className="alshaya_search_slider">
        <ul className="search-lightSlider">
          {thumbnails}
        </ul>
      </div>
    </div>
  );
}

export default SearchGallery;
