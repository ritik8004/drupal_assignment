import React from 'react';

const UserPersonalDetails = ({
  userNickname,
  userAgeValue,
  userGender,
}) => {
  if (userNickname !== undefined) {
    return (
      <div className="user-personal-details">
        <div className="user-attributes">
          <span className="user-name">{`${userNickname}:`}</span>
          <span className="user-attribute-value">{userAgeValue}</span>
        </div>
        {(userGender !== undefined) ? (
          <div className="user-attributes">
            <span className="user-name">{`${userGender.DimensionLabel}: `}</span>
            <span className="user-attribute-value">{userGender.Value}</span>
          </div>
        ) : null}
      </div>
    );
  }
  return (null);
};

export default UserPersonalDetails;
