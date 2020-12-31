import React from 'react';
import ImageElement from '../gallery/imageHelper/ImageElement';

const SubCategoryContent = ({ category }) => (
  <a href={`#${category.title.replace(' ', '-').toLowerCase()}`}>
    <div className="sub-category" data-tid={category.tid}>
      {(category.image)
        ? (
          <div className="sub-category-image">
            <ImageElement
              src={category.image.url}
              alt={category.image.alt}
              title={category.title}
            />
          </div>
        ) : (null)}
      <div className="sub-category-title">
        {category.title}
      </div>
    </div>
  </a>
);

export default SubCategoryContent;
