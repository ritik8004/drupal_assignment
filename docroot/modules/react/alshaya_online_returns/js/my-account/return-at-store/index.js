import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const ReturnAtStore = (props) => {
  const { returnType } = props;

  if (hasValue(returnType)) {
    return (
      <>
        <span>
          { Drupal.t('Search for a nearby store') }
        </span>
        <a href={Drupal.url('store-finder')} className="find-stores">
          { Drupal.t('Find Stores') }
        </a>
      </>
    );
  }

  return (
    <>
      <span>
        { Drupal.t('Or return directly at any one of our stores') }
      </span>
      <a href={Drupal.url('store-finder')} className="find-stores">
        { Drupal.t('Find Stores') }
      </a>
    </>
  );
};

export default ReturnAtStore;
