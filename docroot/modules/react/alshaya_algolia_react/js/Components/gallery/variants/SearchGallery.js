import React from 'react';
import { ImageWrapper } from '../imageHelper/ImageWrapper';
import ImageElement from '../imageHelper/ImageElement';

function thumbnailsHtml(images, title) {
  if (images.length > 0) {
    const thumbnails = [];
    images.forEach((element, index) => {
      thumbnails.push(<li key={index}><ImageElement src={element.url} title={title} /></li>)
    });

    return (
      <div className="alshaya_search_slider">
        <ul className="search-lightSlider">
          {thumbnails}
        </ul>
      </div>
    );
  }
  return (<div className="alshaya_search_slider"></div>);
}

const SearchGallery = ({media, title}) => {

  const mainImage = media.length > 0 ? media.shift() : {};
  const mainImageWrapper = ImageWrapper(mainImage, title, "alshaya_search_mainimage", true);
  const thumbnails = thumbnailsHtml(media, title);

  return (
    <div className="alshaya_search_gallery">
      {mainImageWrapper}
      {thumbnails}
    </div>
  );
}

export default SearchGallery;
