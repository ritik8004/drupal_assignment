import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import EgiftCheckBalanceStepOne from './egift-check-balance-step-one';

export default class EgiftCheckBalance extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openModal: false, // OpenModal.
      initialStep: 1, // InitialStep of the modal.
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
    });
  };

  // Update the initial step.
  handleStepChange = (updatedStep) => {
    this.setState({
      initialStep: updatedStep,
    });
  };

  render() {
    const {
      initialStep,
      openModal,
    } = this.state;
    const buttonName = Drupal.t('CHECK BALANCE', {}, { context: 'egift' });
    const button = React.createElement('button', { type: 'submit', onClick: this.openModal }, buttonName);
    return (
      <>
        { button }
        <ConditionalView conditional={openModal}>
          <EgiftCheckBalanceStepOne
            closeModal={this.closeModal}
            open={openModal}
            initialStep={initialStep}
            stepChange={this.handleStepChange}
          />
        </ConditionalView>
      </>
    );
  }
}
