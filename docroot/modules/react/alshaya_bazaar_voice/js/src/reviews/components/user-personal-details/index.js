import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import getStringMessage from '../../../../../../js/utilities/strings';

const UserPersonalDetails = ({
  userNickname,
  userAge,
  userGender,
}) => {
  if (userNickname !== undefined) {
    return (
      <div className="user-personal-details">
        <div className="user-attributes">
          <ConditionalView condition={userGender !== ''}>
            <div className="user-attributes">
              <span className="user-attribute-value">{`${userGender.ValueLabel},`}</span>
              <ConditionalView condition={userAge !== ''}>
                <span className="user-attribute-value">{`${userAge.ValueLabel} ${getStringMessage('user_age')}`}</span>
              </ConditionalView>
            </div>
          </ConditionalView>
        </div>
      </div>
    );
  }
  return (null);
};

export default UserPersonalDetails;
