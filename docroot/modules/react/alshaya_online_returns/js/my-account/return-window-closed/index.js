import React from 'react';
import { formatDate } from '../../utilities/online_returns_util';

const ReturnWindowClosed = (props) => {
  const { date } = props;
  return (
    <span>
      {
        Drupal.t('Return window closed on @date', {
          '@date': formatDate(date, 'DD-Mon-YYYY'),
        })
      }
    </span>
  );
};

export default ReturnWindowClosed;
