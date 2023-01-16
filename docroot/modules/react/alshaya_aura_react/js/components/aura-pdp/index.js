import React from 'react';
import ReactDOM from 'react-dom';
import parse from 'html-react-parser';
import ToolTip from '../../../../alshaya_spc/js/utilities/tooltip';
import { getPriceToPoint } from '../../utilities/aura_utils';
import { cartAvailableInStorage } from '../../../../alshaya_spc/js/utilities/get_cart';
import { showFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';
import { redeemAuraPoints } from '../../../../alshaya_spc/js/aura-loyalty/components/utilities/checkout_helper';
import { getUserDetails } from '../../utilities/helper';

class AuraPDP extends React.Component {
  constructor(props) {
    super(props);
    const { mode } = this.props;

    this.state = {
      productPoints: this.getInitialProductPoints(mode),
      context: mode,
    };
  }

  componentDidMount() {
    document.addEventListener('auraProductUpdate', this.processVariant, false);
    document.addEventListener('auraProductModalOpened', this.loadModalAuraPoints, false);
    document.addEventListener('auraProductModalClosed', this.removeModalAuraPoints, false);
    // Listener to track any update in customer's aura details.
    document.addEventListener('customerDetailsFetched', this.setCustomerDetails, false);
    document.addEventListener('loyaltyStatusUpdated', this.setCustomerDetails, false);
    // Listener to track refresh cart event - Add to cart on PDP.
    document.addEventListener('refreshCart', this.removeRedeemedPoints, false);
  }

  componentWillUnmount() {
    document.removeEventListener('auraProductUpdate', this.processVariant, false);
  }

  setCustomerDetails = (data) => {
    const { stateValues } = data.detail;
    this.setState({
      ...stateValues,
    });
  };

  getInitialProductPoints = (mode) => {
    const {
      skuCode,
      firstChild,
      productInfo,
    } = this.props;

    // Check if skuCode, firstChild and productInfo props are provided and
    // calculate initial product points from that.
    if (typeof skuCode !== 'undefined'
      && typeof firstChild !== 'undefined'
      && typeof productInfo !== 'undefined') {
      const priceKey = (mode === 'related') ? 'final_price' : 'finalPrice';
      const price = Drupal.hasValue(productInfo[skuCode].variants)
        ? productInfo[skuCode].variants[firstChild][priceKey]
        : productInfo[skuCode][priceKey];

      // Return price as aura points.
      return getPriceToPoint(price.replace(',', ''));
    }

    // If above details are not there in props, proceed with usual approach to
    // get the data from price amount HTML text block.
    let selector = null;

    if (mode === 'main') {
      selector = document.querySelector('.content__title_wrapper .special--price .price-amount') || document.querySelector('.content__title_wrapper .price-amount');
    } else if (mode === 'related') {
      selector = document.querySelector('#drupal-modal .special--price .price-amount') || document.querySelector('#drupal-modal .price-amount');
    }

    // Fetch Product price using selector.
    const productPrice = (selector !== null) ? selector.innerText.replace(/,/g, '') : 0;

    // Return price as aura points.
    return getPriceToPoint(productPrice);
  };

  loadModalAuraPoints = () => {
    if (document.querySelector('#aura-pdp-modal')) {
      ReactDOM.render(
        <AuraPDP mode="related" />,
        document.querySelector('#aura-pdp-modal'),
      );
    }
  };

  removeModalAuraPoints = () => {
    if (document.querySelector('#aura-pdp-modal')) {
      ReactDOM.unmountComponentAtNode(document.getElementById('aura-pdp-modal'));
    }
  };

  updateState = (data) => {
    const { stateValues, context } = data.detail;
    const { mode } = this.props;

    if (context !== undefined && context !== mode) {
      return null;
    }

    this.setState({
      ...stateValues,
    });

    return null;
  };

  processVariant = (variantDetails) => {
    const { data, context } = variantDetails.detail;
    const { mode } = this.props;

    if (context !== undefined && context !== mode) {
      return null;
    }

    if (data.length !== 0) {
      this.setState({
        productPoints: data.amount ? getPriceToPoint(data.amount) : 0,
        context,
      });

      // On change in variant or quantity order total amount might change
      // so we remove redeemed aura points.
      this.removeRedeemedPoints();
    }

    return null;
  };

  // Helper to remove redeemed aura points if any.
  removeRedeemedPoints = () => {
    const cart = cartAvailableInStorage();
    const { cardNumber } = this.state;

    // Return if cart not available or paidWithAura and balancePayable is not present
    // in cart totals that means user has not redeemed any points.
    if (cart === false
      || cart === null
      || cart === 'empty'
      || cart.totals.paidWithAura === undefined
      || cart.totals.balancePayable === undefined
      || cardNumber === undefined) {
      return;
    }

    // Call API to remove redeemed aura points.
    const requestData = {
      action: 'remove points',
      userId: getUserDetails().id,
      cardNumber,
    };
    showFullScreenLoader();
    redeemAuraPoints(requestData);
  };

  getToolTipContent = () => Drupal.t('Join Aura, our new loyalty programme, to earn and spend points while you shop, and discover exclusive benefits. Points are not earned on purchases made with points.');

  getPointsText = () => {
    const { productPoints } = this.state;

    if (productPoints !== 0) {
      return (
        <div className="points-text">
          {parse(Drupal.t(
            'Earn <span><b>@pts points with Aura</b></span> on this purchase',
            { '@pts': productPoints },
            { context: 'aura' },
          ))}
        </div>
      );
    }

    return <span>{Drupal.t('Earn Aura points')}</span>;
  };

  render() {
    const {
      productPoints,
      context,
    } = this.state;
    const { mode } = this.props;

    if (context !== mode) {
      return null;
    }

    if (productPoints === 0) {
      return null;
    }

    return (
      <div className="aura-pdp-points-section">
        { this.getPointsText()}
        <ToolTip enable question>{ this.getToolTipContent() }</ToolTip>
      </div>
    );
  }
}

export default AuraPDP;
