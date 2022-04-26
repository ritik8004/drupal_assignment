import React from 'react';

class PromotionsWarningModal extends React.Component {
  constructor(props) {
    super(props);
  }

  componentDidMount = () => {
    // document.addEventListener('updateRefundAccordionState', this.updateRefundAccordionState, false);
  };

  // /**
  //  * Method to update react state of refund accordion.
  //  */
  // updateRefundAccordionState = (event) => {
  //   this.setState({
  //     open: event.detail,
  //   });
  // };



  render() {
    const { closePromotionsWarningModal } = this.props;

    return (
      <div className="promotions-warning-modal-wrapper">
        <button type="button" className="close" onClick={() => closePromotionsWarningModal()} > close </button>
        <div className='title'>
          {Drupal.t('Selected Item is Promotional Item', {}, { context: 'online_returns' })}
        </div>
        <div className='description'>
          <span> {Drupal.t('To receive refund for a promotional items, all items related to the promotion has to be returned.', {}, { context: 'online_returns' })} </span>
          <span> {Drupal.t('Clicking continue will select all items in this promotion.', {}, { context: 'online_returns' })} </span>
        </div>
        <div className='cta-wrapper'>
          <button
            type="button"
            onClick={this.handlePromotionContinue}
          >
            <span className="continue-button-label">{Drupal.t('Continue', {}, { context: 'online_returns' })}</span>
          </button>
          <button
            type="button"
            onClick={this.handlePromotionDeselect}
          >
            <span className="deselect-button-label">{Drupal.t('Deselect this item', {}, { context: 'online_returns' })}</span>
          </button>
        </div>
      </div>
    );
  }
}

export default PromotionsWarningModal;
