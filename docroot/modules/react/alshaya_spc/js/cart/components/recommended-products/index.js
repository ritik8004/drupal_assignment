import React from 'react';

import RecommendedProduct from '../../../utilities/recommended-product';
import SectionTitle from '../../../utilities/section-title';
import { isRTL } from '../../../utilities/rtl';

export default class CartRecommendedProducts extends React.Component {
  listHorizontalScroll = (direction) => {
    // Lets try native scroll for now using scroll-snap from CSS
    // if doesnt work out this has to be a slider.
    const container = document.querySelector('.spc-recommended-products .block-content');
    if (direction === 'next') {
      container.scrollBy({
        top: 0,
        left: isRTL() === true ? -320 : 320,
        behavior: 'smooth',
      });
    } else {
      container.scrollBy({
        top: 0,
        left: isRTL() === true ? 320 : -320,
        behavior: 'smooth',
      });
    }
  };

  render() {
    const {
      recommended_products: recommendedProducts,
      sectionTitle,
    } = this.props;

    // If recommended products available.
    if (Object.keys(recommendedProducts).length > 0) {
      return (
        <>
          <SectionTitle>{sectionTitle}</SectionTitle>
          <div className="spc-recommended-products">
            <button className="nav-prev" type="button" onClick={() => { this.listHorizontalScroll('prev'); }} />
            <div className="block-content">
              { Object.keys(recommendedProducts).map(
                (key) => (
                  <RecommendedProduct
                    key={key}
                    item={recommendedProducts[key]}
                  />
                ),
              )}
            </div>
            <button className="nav-next" type="button" onClick={() => { this.listHorizontalScroll('next'); }} />
          </div>
        </>
      );
    }
    return (null);
  }
}
