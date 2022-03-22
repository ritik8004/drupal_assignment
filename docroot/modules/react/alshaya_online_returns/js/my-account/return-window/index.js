import React from 'react';
import { formatDate } from '../../utilities/online_returns_util';

const ReturnWindow = (props) => {
  const { date } = props;
  return (
    <span>
      {
        Drupal.t('You have untill @date to return the items', {
          '@date': formatDate(date, 'DD-Mon-YYYY'),
        })
      }
    </span>
  );
};

export default ReturnWindow;
