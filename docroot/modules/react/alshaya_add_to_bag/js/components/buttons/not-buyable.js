import React from 'react';
import getStringMessage from '../../../../js/utilities/strings';

/**
 * Displays a button for for when product is not buyable.
 *
 * @param {*} url
 *   The url of the page to redirect to when the button is clicked.
 */
const NotBuyableButton = ({ url }) => (
  <a href={url} className="is-not-buyable">
    {`${getStringMessage('view_options')}`}
  </a>
);

export default NotBuyableButton;
