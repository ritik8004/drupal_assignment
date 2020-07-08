import React from 'react';
import smoothscroll from 'smoothscroll-polyfill';
import RecommendedProduct from '../../../utilities/recommended-product';
import SectionTitle from '../../../utilities/section-title';
import { getRecommendedProducts } from '../../../utilities/checkout_util';
import isRTL from '../../../utilities/rtl';
import dispatchCustomEvent from '../../../utilities/events';
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

    this.recommendedProductRef = React.createRef();
    this.recommendedSkus = [];
  }

  componentDidMount() {
    // Remove any old storage.
    const key = `recommendedProduct:${drupalSettings.path.currentLanguage}`;
    localStorage.removeItem(key);
    const { items } = this.props;
    this.spcRecommendationHandler(items);

    // Event handles cart recommendation refresh.
    document.addEventListener('spcRefreshCartRecommendation', this.spcRefreshCartRecommendation, false);
  }

  componentDidUpdate() {
    const { wait } = this.state;
    if (wait === false) {
      Drupal.ajax.bindAjaxLinks(this.recommendedProductRef.current);
    }
  }

  componentWillUnmount() {
    document.removeEventListener('spcRefreshCartRecommendation', this.spcRefreshCartRecommendation, false);
  }

  spcRecommendationHandler = (items) => {
    if (items !== undefined
      && Object.keys(items).length > 0) {
      this.requestSkus = Object.keys(items);
      this.recommendedSkuSPrepare(items);

      new Promise((resolve) => {
        const waitForSkuClear = setInterval(() => {
          if (this.requestSkus.length === 0) {
            clearInterval(waitForSkuClear);
            resolve();
          }
        }, 100);
      }).then(() => {
        // Get recommended products.
        const recommendedProducts = getRecommendedProducts(this.recommendedSkus, 'crosssell');
        if (recommendedProducts instanceof Promise) {
          recommendedProducts.then((result) => {
            // Reset it for next request.
            this.recommendedSkus = [];
            // If there is no error and there are recommended products.
            if (result.error === undefined && result.data !== undefined) {
              this.setState({
                wait: false,
                recommendedProducts: result.data,
              });

              // Storing in localstorage to be used by GTM.
              const key = `recommendedProduct:${drupalSettings.path.currentLanguage}`;
              localStorage.setItem(key, JSON.stringify(result.data));
              dispatchCustomEvent('recommendedProductsLoad', {
                products: result.data,
              });
            }
          });
        }
      });
    }
  };

  recommendedSkuSPrepare = (items) => {
    Object.entries(items).forEach(([, item]) => {
      Drupal.alshayaSpc.getProductData(item.sku, this.productDataCallback);
    });
  };

  /**
   * Call back to get product data from storage.
   */
  productDataCallback = (productData) => {
    // If sku info available.
    if (productData !== null && productData.sku !== undefined) {
      this.requestSkus.splice(this.requestSkus.indexOf(productData.sku), 1);
      this.recommendedSkus.push(productData.sku);
      if (productData.parentSKU) {
        this.recommendedSkus.push(productData.parentSKU);
      }
    }
  };

  spcRefreshCartRecommendation = (event) => {
    const { items } = event.detail;
    this.spcRecommendationHandler(items);
  };

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
          <div ref={this.recommendedProductRef} className="spc-recommended-products fadeInUp" style={{ animationDelay: '0.8s' }}>
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
