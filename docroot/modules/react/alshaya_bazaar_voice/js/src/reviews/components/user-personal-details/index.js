import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';

const UserPersonalDetails = ({
  userNickname,
  userAge,
  userGender,
}) => {
  if (userNickname !== undefined) {
    return (
      <div className="user-personal-details">
        <div className="user-attributes">
          <span className="user-name">{`${userNickname}: `}</span>
          <ConditionalView condition={userAge !== ''}>
            <span className="user-attribute-value">{userAge.ValueLabel}</span>
          </ConditionalView>
        </div>
        <ConditionalView condition={userGender !== ''}>
          <div className="user-attributes">
            <span className="user-name">{`${userGender.DimensionLabel}: `}</span>
            <span className="user-attribute-value">{userGender.ValueLabel}</span>
          </div>
        </ConditionalView>
      </div>
    );
  }
  return (null);
};

export default UserPersonalDetails;
