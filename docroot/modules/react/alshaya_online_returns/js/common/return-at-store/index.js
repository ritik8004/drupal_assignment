import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const ReturnAtStore = (props) => {
  const { returnType, returnButtonclass } = props;

  const message = hasValue(returnType)
    ? Drupal.t('Search for a nearby store', {}, { context: 'online_returns' })
    : Drupal.t('Or return directly at any one of our stores', {}, { context: 'online_returns' });
  return (
    <div className={returnButtonclass}>
      <span>{ message }</span>
      <a href={Drupal.url('store-finder')} className="find-stores">
        { Drupal.t('Find Stores', {}, { context: 'online_returns' }) }
      </a>
    </div>
  );
};

export default ReturnAtStore;
