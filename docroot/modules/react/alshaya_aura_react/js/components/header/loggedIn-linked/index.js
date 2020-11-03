import React from 'react';
import UserNamePoints from '../user-name-points';
import PointsWithLoyaltyPageLinked from '../points-with-loyalty-page-linked';

const LoggedInLinked = (props) => {
  const {
    isDesktop,
    points,
    isHeaderModalOpen,
  } = props;

  // For Desktop.
  if (isDesktop) {
    return (
      <PointsWithLoyaltyPageLinked
        points={points}
        isHeaderModalOpen={isHeaderModalOpen}
      />
    );
  }

  // For Mobile.
  return <UserNamePoints points={points} />;
};

export default LoggedInLinked;
