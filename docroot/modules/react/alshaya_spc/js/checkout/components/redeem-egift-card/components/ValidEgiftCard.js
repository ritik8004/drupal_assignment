import React from 'react';
import { egiftCardHeader } from '../../../../utilities/egift_util';

export default class ValidEgiftCard extends React.Component {
  handleRemoveCard = () => {

  }

  // Handle the amount change.
  handleAmountChange = () => {

  }

  // Update the user account with egift card.
  handleCardLink = () => {

  }

  render = () => (
    <div className="egift-wrapper">
      {egiftCardHeader({
        egiftHeading: Drupal.t('Your eGift card is applied - KWD 280', {}, { context: 'egift' }),
        egiftSubHeading: Drupal.t('Remaining Balance - KWD 220.00', {}, { context: 'egift' }),
      })}

      <div className="remove-egift-card">{Drupal.t('Remove', {}, { context: 'egift' })}</div>
      <p><strong>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</strong></p>

      <input type="checkbox" id="link-egift-card" onChange={this.handleCardLink} />
      <label htmlFor="link-egift-card">{Drupal.t('Link this card for faster payment next time', {}, { context: 'egift' })}</label>
    </div>
  )
}
