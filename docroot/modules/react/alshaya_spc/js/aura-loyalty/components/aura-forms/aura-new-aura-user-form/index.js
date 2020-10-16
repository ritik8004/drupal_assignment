import React from 'react';
import SectionTitle from '../../../../utilities/section-title';
import TextField from '../../../../utilities/textfield';

class AuraFormNewAuraUserModal extends React.Component {
  getNewUserFormDescription = () => [
    <span key="part1">{Drupal.t('By pressing submit, you agree to have read and accepted our')}</span>,
    <a key="part2" className="t-c-link">{Drupal.t('Terms & Conditions')}</a>,
  ];

  registerUser = (closeModal) => {
    // @todo: API Call to register Aura User.
    // Close both the modals.
    closeModal();
  };

  render() {
    const {
      closeModal,
      mobileNumber,
    } = this.props;

    const submitButtonText = Drupal.t('Submit');

    return (
      <div className="aura-new-user-form">
        <div className="aura-modal-header">
          <SectionTitle>{Drupal.t('Say hello to Aura')}</SectionTitle>
          <a className="close" onClick={() => closeModal()} />
        </div>
        <div className="aura-modal-form">
          <div className="aura-modal-form-items">
            <TextField
              type="text"
              required
              disabled
              name="new-aura-user-mobile-number"
              defaultValue={mobileNumber}
              label={Drupal.t('Mobile Number')}
            />
            <TextField
              type="text"
              required
              name="new-aura-user-full-name"
              label={Drupal.t('Full name')}
            />
            <TextField
              type="email"
              required
              name="new-aura-user-email"
              label={Drupal.t('Email address')}
            />
          </div>
          <div className="aura-modal-form-actions">
            <div className="aura-new-user-t-c aura-otp-submit-description">
              {this.getNewUserFormDescription()}
            </div>
            <div className="aura-modal-form-submit" onClick={() => this.registerUser(closeModal)}>
              {submitButtonText}
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default AuraFormNewAuraUserModal;
