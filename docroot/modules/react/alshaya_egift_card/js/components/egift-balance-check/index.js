import React from 'react';
import EgiftCheckBalanceStepOne from './egift-check-balance-step-one';

export default class EgiftCheckBalance extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openModal: false, // OpenModal.
      initialStep: 1, // InitialStep of the modal.
      egiftCardNumber: '', // Egift Card Number.
    };
  }

  // Open Modal.
  openModal = (e) => {
    this.setState({
      openModal: true,
    });

    e.stopPropagation();
  };

  // Close Modal.
  closeModal = () => {
    this.setState({
      openModal: false,
      initialStep: 1,
      egiftCardNumber: '',
    });
  };

  // Update the initial step.
  handleStepChange = (updatedStep, cardNumber = '') => {
    this.setState({
      initialStep: updatedStep,
      egiftCardNumber: cardNumber,
    });
  };

  render() {
    const {
      initialStep,
      openModal,
      egiftCardNumber,
    } = this.state;
    const buttonName = Drupal.t('CHECK BALANCE', {}, { context: 'egift' });
    const button = React.createElement('button', { type: 'submit', onClick: this.openModal }, buttonName);
    return (
      <>
        { button }
        <EgiftCheckBalanceStepOne
          closeModal={this.closeModal}
          open={openModal}
          initialStep={initialStep}
          stepChange={this.handleStepChange}
          egiftCardNumber={egiftCardNumber}
        />
      </>
    );
  }
}
