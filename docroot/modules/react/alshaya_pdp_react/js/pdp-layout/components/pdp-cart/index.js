import React, { createRef } from 'react';
import ConfigurableProductForm from './configurable-product-form';
import SimpleProductForm from './simple-product-form';

class PdpCart extends React.Component {
  constructor(props) {
    super(props);
    this.wrapper = createRef();
  }

  componentDidMount() {
    const { childRef } = this.props;
    if (childRef) {
      childRef(this.wrapper);
    }

    Drupal.attachBehaviors(document.querySelector('.pdp-cart-form'), drupalSettings);
  }

  componentDidUpdate() {
    const { childRef } = this.props;
    if (childRef) {
      childRef(this.wrapper);
    }
  }

  render() {
    const {
      configurableCombinations,
      skuCode,
      productInfo,
      pdpRefresh,
      pdpLabelRefresh,
      stockQty,
      firstChild,
      context,
      closeModal,
      animatePdpCart,
      refButton,
    } = this.props;

    if (configurableCombinations) {
      return (
        <div
          className={`pdp-cart-form ${(animatePdpCart ? 'fadeInUp notInMobile' : '')}`}
          style={(animatePdpCart ? { animationDelay: '0.6s' } : null)}
          ref={this.wrapper}
        >
          <ConfigurableProductForm
            configurableCombinations={configurableCombinations}
            skuCode={skuCode}
            productInfo={productInfo}
            pdpRefresh={pdpRefresh}
            pdpLabelRefresh={pdpLabelRefresh}
            stockQty={stockQty}
            firstChild={firstChild}
            context={context}
            closeModal={closeModal}
            refCartButton={(ref) => (refButton(ref))}
          />
        </div>
      );
    }
    return (
      <div
        className={`pdp-cart-form ${(animatePdpCart ? 'fadeInUp notInMobile' : '')}`}
        style={(animatePdpCart ? { animationDelay: '0.6s' } : null)}
        ref={this.wrapper}
      >
        <SimpleProductForm
          skuCode={skuCode}
          productInfo={productInfo}
          pdpLabelRefresh={pdpLabelRefresh}
          stockQty={stockQty}
          context={context}
          closeModal={closeModal}
          refCartButton={(ref) => (refButton(ref))}
        />
      </div>
    );
  }
}

export default PdpCart;
