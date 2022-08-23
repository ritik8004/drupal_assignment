import React from 'react';
import MembershipPopup from './membership-popup';
import MyBenefits from './my-benefits';

const MyAccount = () => {
  const { currentPath } = drupalSettings.path;
  return (
    <>
      {currentPath.includes('user/') && (
        <MembershipPopup />
      )}
      <MyBenefits />
    </>
  );
};

export default MyAccount;
