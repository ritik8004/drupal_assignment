import React from 'react';
import { getPriceToPointRatio } from '../../utilities/helper';

const AuraProgressNextTierMessage = (props) => {
  const {
    userPoints,
    nextTierThreshold,
    nextTierLabel,
  } = props;

  const difference = nextTierThreshold - userPoints;
  const { currency_code: currencyCode } = drupalSettings.alshaya_spc.currency_config;
  const differenceAmount = difference / getPriceToPointRatio();

  if (Number.isNaN(differenceAmount)) {
    return null;
  }

  return (
    <div className="spc-aura-points-upgrade-item">
      {`${Drupal.t('Spend')}`}
      <b>{`${currencyCode} ${differenceAmount}`}</b>
      {`${Drupal.t('to reach')}`}
      <b>{`${nextTierLabel}`}</b>
      {`${Drupal.t('status')}`}
    </div>
  );
};

export default AuraProgressNextTierMessage;
