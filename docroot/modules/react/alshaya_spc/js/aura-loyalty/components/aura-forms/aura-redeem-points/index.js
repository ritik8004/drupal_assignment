import React from 'react';
import AuraRedeemPointsTextField from '../aura-redeem-textfield';
import ConditionalView from '../../../../common/components/conditional-view';

class AuraFormRedeemPoints extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      enableSubmit: false,
      money: null,
      points: null,
      auraTransaction: false,
    };
  }

  convertPointsToMoney = (e) => {
    // @todo: Run some proper validations, for now just checking length.
    if (e.target.value.length >= 1) {
      this.setState({
        enableSubmit: true,
      });
    } else {
      this.setState({
        enableSubmit: false,
      });
    }

    // Convert to money.
    // @todo: Update conversion rate.
    // For now a simple rate of 100 points = 1 currency unit.
    if (e.target.value > 0) {
      const money = e.target.value * 0.01;
      this.setState({
        points: e.target.value,
        money: `${Drupal.t('KWD')} ${money.toFixed(3)}`,
      });
    } else {
      this.setState({
        points: null,
        money: null,
      });
    }
  };

  redeemPoints = () => {
    // @todo: Call API to do a AURA transaction against the order.
    this.setState({
      auraTransaction: true,
    });
    // Add a class for FE purposes.
    document.querySelector('.spc-aura-redeem-points-form-wrapper').classList.add('redeemed');
  };

  undoRedeemPoints = () => {
    // @todo: Call API to do undo AURA transaction against the order.
    this.setState({
      auraTransaction: false,
      points: null,
      money: null,
    });
    // Remove class.
    document.querySelector('.spc-aura-redeem-points-form-wrapper').classList.remove('redeemed');
  }

  getPointsRedeemedMessage = () => {
    const {
      points,
      money,
      auraTransaction,
    } = this.state;

    if (auraTransaction) {
      return [
        <span className="spc-aura-highlight">{`${points} ${Drupal.t('points')}`}</span>,
        <span className="spc-aura-redeem-text">{`${Drupal.t('worth')}`}</span>,
        <span className="spc-aura-highlight">{`${money}`}</span>,
        <span className="spc-aura-redeem-text">{`${Drupal.t('have been successfully redeemed')}`}</span>,
      ];
    }

    return null;
  }

  render() {
    const {
      enableSubmit,
      money,
      auraTransaction,
    } = this.state;

    return (
      <div className="spc-aura-redeem-points-form-wrapper">
        <span className="label">{ Drupal.t('Use your points') }</span>
        <div className="form-items">
          <div className="inputs">
            <ConditionalView condition={auraTransaction === false}>
              <AuraRedeemPointsTextField
                name="spc-aura-redeem-field-points"
                placeholder="0"
                onChangeCallback={this.convertPointsToMoney}
              />
              <span className="spc-aura-redeem-points-separator">=</span>
              <AuraRedeemPointsTextField
                name="spc-aura-redeem-field-amount"
                placeholder={Drupal.t('KWD 0.000')}
                money={money}
                type="money"
              />
            </ConditionalView>
            <ConditionalView condition={auraTransaction === true}>
              {this.getPointsRedeemedMessage()}
            </ConditionalView>
          </div>
          <ConditionalView condition={auraTransaction === false}>
            <button
              type="submit"
              className="spc-aura-redeem-form-submit spc-aura-button"
              onClick={() => this.redeemPoints()}
              disabled={!enableSubmit}
            >
              { Drupal.t('Use points') }
            </button>
          </ConditionalView>
          <ConditionalView condition={auraTransaction === true}>
            <button
              type="submit"
              className="spc-aura-redeem-form-submit spc-aura-button"
              onClick={() => this.undoRedeemPoints()}
            >
              { Drupal.t('Remove') }
            </button>
          </ConditionalView>
        </div>
        <div className="spc-aura-link-api-response-message" />
      </div>
    );
  }
}

export default AuraFormRedeemPoints;
