import React from 'react';

const LoyaltyClubBenefits = () => (
  <div className="loyalty-club-details-wrapper">
    <div className="title">
      { Drupal.t('Loyalty Club Benefits') }
    </div>
    <div className="details">
      <div className="row">
        <div className="col">
          { Drupal.t('Spend per Calendar Year') }
        </div>
        <div className="col">
          { Drupal.t('Loyalty') }
          { Drupal.t('FREE') }
        </div>
        <div className="col">
          { Drupal.t('Loyalty') }
          {' +'}
          { Drupal.t('KD') }
          {' 500'}
        </div>
        <div className="col">
          { Drupal.t('Loyalty') }
          { Drupal.t('VIP') }
          { Drupal.t('KD') }
          {' 1000'}
        </div>
      </div>
      <div className="row">
        <div className="col">
          { Drupal.t('Points Per KD 1') }
        </div>
        <div className="col">
          {'10'}
          { Drupal.t('Points') }
        </div>
        <div className="col">
          {'15'}
          { Drupal.t('Points') }
        </div>
        <div className="col">
          {'20'}
          { Drupal.t('Points') }
        </div>
      </div>
      <div className="row">
        <div className="col">
          {Drupal.t('Birthday Gift')}
        </div>
        <div className="col">
          {'2'}
          {Drupal.t('Choices')}
        </div>
        <div className="col">
          {'3'}
          {Drupal.t('Choices')}
        </div>
        <div className="col">
          {'4'}
          {Drupal.t('Choices')}
        </div>
      </div>
      <div className="row">
        <div className="col">
          { Drupal.t('Seasonal Saving') }
        </div>
        <div className="col">
          *
        </div>
        <div className="col">
          **
        </div>
        <div className="col">
          ***
        </div>
      </div>
      <div className="row">
        <div className="col">
          { Drupal.t('Free Standard Shipping') }
        </div>
        <div className="col" />
        <div className="col">
          .
        </div>
        <div className="col">
          .
        </div>
      </div>
      <div className="row">
        <div>
          { Drupal.t('Early Access to New Launches') }
        </div>
        <div className="col" />
        <div className="col">
          .
        </div>
        <div className="col">
          ..
        </div>
      </div>
      <div className="row">
        <div className="col">
          { Drupal.t('Exclusive Events') }
        </div>
        <div className="col">
          .
        </div>
      </div>
    </div>
  </div>
);

export default LoyaltyClubBenefits;
