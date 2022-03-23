import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const ReturnAtStore = (props) => {
  const { returnType } = props;

  const message = hasValue(returnType) ? Drupal.t('Search for a nearby store') : Drupal.t('Or return directly at any one of our stores');
  return (
    <>
      <span>{ message }</span>
      <a href={Drupal.url('store-finder')} className="find-stores">
        { Drupal.t('Find Stores') }
      </a>
    </>
  );
};

export default ReturnAtStore;
