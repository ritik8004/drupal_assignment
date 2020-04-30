import React from 'react';
import smoothscroll from 'smoothscroll-polyfill';
import RecommendedProduct from '../../../utilities/recommended-product';
import SectionTitle from '../../../utilities/section-title';
import { getRecommendedProducts } from '../../../utilities/checkout_util';
import isRTL from '../../../utilities/rtl';
// Use smoothscroll to fill for Safari and IE,
// Otherwise while scrollIntoView() is supported by all,
// Smooth transition is not supported apart from Chrome & FF.
smoothscroll.polyfill();

export default class CartRecommendedProducts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      recommendedProducts: [],
    };
  }

  componentDidMount() {
    const { items } = this.props;
    if (items !== undefined
      && Object.keys(items).length > 0) {
      let skus = [];
      Object.entries(items).forEach(([, item]) => {
        skus.push(item.sku);
      });

      // Get recommended products.
      const recommendedProducts = getRecommendedProducts(skus, 'crosssell');
      if (recommendedProducts instanceof Promise) {
        recommendedProducts.then((result) => {
          // If there is no error.
          if (result.error === undefined) {
            this.setState({
              wait: false,
              recommendedProducts: result.data,
            });

            // Storing in localstorage to be used by GTM.
            const key = `recommendedProduct:${drupalSettings.path.currentLanguage}`;
            localStorage.setItem(key, result.data);
          }
        });
      }
    }
  }

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
    const { wait, recommendedProducts } = this.state;
    if (wait === true) {
      return (null);
    }

    const { sectionTitle } = this.props;

    // If recommended products available.
    if (Object.keys(recommendedProducts).length > 0) {
      return (
        <>
          <SectionTitle animationDelayValue="0.6s">{sectionTitle}</SectionTitle>
          <div className="spc-recommended-products fadeInUp" style={{ animationDelay: '0.8s' }}>
            <button className="nav-prev" type="button" onClick={() => { this.listHorizontalScroll('prev'); }} />
            <div className="block-content">
              { Object.keys(recommendedProducts).map(
                (key) => (
                  <RecommendedProduct
                    key={key}
                    item={recommendedProducts[key]}
                    itemKey={key}
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
