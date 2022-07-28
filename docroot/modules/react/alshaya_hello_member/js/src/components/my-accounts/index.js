import React from 'react';
import MembershipPopup from './membership-popup';
import MyBenefits from './my-benefits';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';


const MyAccount = () => {
  const { currentPath } = drupalSettings.path;
  return (
    <>
      <ConditionalView condition={currentPath.includes('user/')}>
        <MembershipPopup />
      </ConditionalView>
      <MyBenefits />
    </>
  );
};

export default MyAccount;
