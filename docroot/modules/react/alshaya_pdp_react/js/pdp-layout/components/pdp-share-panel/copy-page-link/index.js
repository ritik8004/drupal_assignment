import React from 'react';
import { copyToClipboard } from '../../../../utilities/pdp_layout';

const CopyPageLink = () => (
  <button onClick={() => copyToClipboard()} type="button">
    {Drupal.t('Copy page link')}
  </button>
);

export default CopyPageLink;
