import React from 'react';

const SectionTitle = ({ animationDelayValue, children }) => <div className="appointment-booking-section-title fadeInUp" style={{ animationDelay: animationDelayValue }}>{children}</div>;

export default SectionTitle;
