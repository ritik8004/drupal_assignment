import React from 'react';
import ReactDOM from 'react-dom';
import { getUserDetails } from '../../utilities/helper';
import { getStorageInfo } from '../../../../js/utilities/storage';
import ToolTip from '../../../../alshaya_spc/js/utilities/tooltip';
import { getAuraLocalStorageKey } from '../../utilities/aura_utils';
import { getProductPoints, isProductBuyable } from '../../utilities/pdp_helper';
import { getPointsForPrice } from '../../utilities/aura_utils';

class AuraPDP extends React.Component {
  constructor(props) {
    super(props);
    const { mode } = this.props;
    this.state = {
      productPoints: 0,
      productDetails: [],
      context: mode,
    };
  }

  componentDidMount() {
    document.addEventListener('auraProductUpdate', this.processVariant, false);
    document.addEventListener('auraProductModalOpened', this.loadModalAuraPoints, false);
    document.addEventListener('auraProductModalClosed', this.removeModalAuraPoints, false);
  }

  componentWillUnmount() {
    document.removeEventListener('auraProductUpdate', this.processVariant, false);
  }

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

  updateStates = (data) => {
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
        productDetails: data,
        productPoints: data.amount ? getPointsForPrice(data.amount) : 0,
        context,
      });
    }

    return null;
  };

  getToolTipContent = () => Drupal.t('Everytime you shop you will earn Aura points which can then be redeemed for future purchases. Not eligible for accrual when purchased through Aura points.');

  getPointsText = () => {
    const { productPoints } = this.state;

    if (productPoints !== 0) {
      return [
        <span>{`${Drupal.t('Earn')} `}</span>,
        <b>{productPoints}</b>,
        <span>{` ${Drupal.t('Aura points')}`}</span>,
      ];
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

    if (!isProductBuyable()) {
      return null;
    }

    if (productPoints === 0) {
      return null;
    }

    return (
      <div className="aura-pdp-points-section">
        <span className="points-text">
          { this.getPointsText()}
        </span>
        <ToolTip enable question>{ this.getToolTipContent() }</ToolTip>
      </div>
    );
  }
}

export default AuraPDP;
