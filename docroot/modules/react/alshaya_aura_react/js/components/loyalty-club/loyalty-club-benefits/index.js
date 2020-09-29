import React from 'react';
import LoyaltyClubBenefitsRow from './loyalty-club-benefits-row';

const LoyaltyClubBenefits = () => (
  <div className="loyalty-club-details-wrapper">
    <div className="title">
      <span className="title-1">{ Drupal.t('Your Aura.') }</span>
      <span className="title-2">{ Drupal.t('Your Benefits.') }</span>
    </div>
    <div className="details">
      <LoyaltyClubBenefitsRow
        rowClass="header"
        rowLabel={Drupal.t('Plan Tiers')}
        cell1={Drupal.t('Hello')}
        cell2={Drupal.t('Star')}
        cell3={Drupal.t('VIP')}
      />
      <LoyaltyClubBenefitsRow
        rowClass="item-bold"
        rowLabel={Drupal.t('Spend per Calendar Year')}
        cell1={Drupal.t('FREE')}
        cell2={Drupal.t('KWD 500')}
        cell3={Drupal.t('KWD 1000')}
      />
      <LoyaltyClubBenefitsRow
        rowClass="normal"
        rowLabel={Drupal.t('Points Per KD 1')}
        cell1={Drupal.t('10 Points')}
        cell2={Drupal.t('15 Points')}
        cell3={Drupal.t('20 Points')}
      />
      <LoyaltyClubBenefitsRow
        rowClass="normal"
        rowLabel={Drupal.t('Birthday Gift')}
        cell1={Drupal.t('2 Choices')}
        cell2={Drupal.t('3 Choices')}
        cell3={Drupal.t('4 Choices')}
      />
      <LoyaltyClubBenefitsRow
        rowClass="stars-row"
        iconType="stars"
        rowLabel={Drupal.t('Seasonal Saving')}
      />
      <LoyaltyClubBenefitsRow
        rowClass="tick-row"
        iconType="tick23"
        rowLabel={Drupal.t('Free Standard Shipping')}
      />
      <LoyaltyClubBenefitsRow
        rowClass="tick-row"
        iconType="tick23"
        rowLabel={Drupal.t('Early Access to New Launches')}
      />
      <LoyaltyClubBenefitsRow
        rowClass="tick-row"
        iconType="tick3"
        rowLabel={Drupal.t('Exclusive Events')}
      />
    </div>
  </div>
);

export default LoyaltyClubBenefits;
