import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const ReturnAtStore = (props) => {
  const { returnType, returnButtonclass } = props;

  const message = hasValue(returnType) ? Drupal.t('Search for a nearby store') : Drupal.t('Or return directly at any one of our stores');
  return (
    <div className={returnButtonclass}>
      <span>{ message }</span>
      <a href={Drupal.url('store-finder')} className="find-stores">
        { Drupal.t('Find Stores') }
      </a>
    </div>
  );
};

export default ReturnAtStore;
