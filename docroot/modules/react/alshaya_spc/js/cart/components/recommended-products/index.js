import React from 'react';

import RecommendedProduct from '../../../utilities/recommended-product';
import CheckoutSectionTitle from "../spc-checkout-section-title";

export default class CartRecommendedProducts extends React.Component {

  listHorizontalScroll = (direction) => {
    // Lets try native scroll for now using scroll-snap from CSS
    // if doesnt work out this has to be a slider.
    var container = document.querySelector('.spc-recommended-products .block-content');
    if (direction === 'next') {
      container.scrollLeft += 167;
    }
    else {
      container.scrollLeft -= 167;
    }
  };

  render() {
    const recommended_products = this.props.recommended_products;

    // If recommended products available.
    if (Object.keys(recommended_products).length > 0) {
      return (
        <React.Fragment>
          <CheckoutSectionTitle>
            {Drupal.t('you may also like')}
          </CheckoutSectionTitle>
          <div className="spc-recommended-products">
            <button className="nav-prev" onClick={() => {this.listHorizontalScroll('prev')}}/>
            <div className="block-content">
              {Object.keys(recommended_products).map(function(key) {
                return <RecommendedProduct key={key} item={recommended_products[key]}/>
              })}
            </div>
            <button className="nav-next" onClick={() => {this.listHorizontalScroll('next')}}/>
          </div>
        </React.Fragment>
      );
    }

    return (null);
  }

}
