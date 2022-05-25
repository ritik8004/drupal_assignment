import React from 'react';
import MemberID from './member-id';
import TierProgress from './tier-progress';
import QrCode from './qr-code';

const MyMembership = () => (
  <>
    <div className="member-name">
      Hi Abdul
    </div>
    <div className="points-block">
      <div className="my-points">
        55 points
      </div>
      <TierProgress />
      <div className="my-points-details">
        You are 45 points away from getting your next bonus voucher and 245 points away
        to become a Plus member. Vouchers have
        a 30-day delay.
      </div>
      <QrCode />
      <MemberID />
    </div>
  </>
);

export default MyMembership;
