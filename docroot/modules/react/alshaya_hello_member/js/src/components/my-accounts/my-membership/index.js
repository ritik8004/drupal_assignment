import React from 'react';
import MemberName from './member-name';
import MemberID from './member-id';
import TierProgress from './tier-progress';
import QrCode from './qr-code';

const MyMembership = () => (
  <>
    <MemberName />
    <div className="points-block">
      <div className="my-points">
        55 points
      </div>
      <div className="my-tier-progress">
        <TierProgress />
      </div>
      <div className="my-points-details">
        You are 45 points away from getting your next bonus voucher and 245 points away
        to become a Plus member. Vouchers have
        a 30-day delay.
      </div>
      <QrCode />
      <div className="my-membership-id">
        <MemberID />
      </div>
    </div>
  </>
);

export default MyMembership;
