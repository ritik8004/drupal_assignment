import moment from 'moment-timezone';

export const getDateFormat = () => {
  const format = 'MMM DD, YYYY';
  return format;
};

export const getDate = (date) => {
  const dateObj = new Date(date);
  const dateStr = moment(dateObj).format(getDateFormat());
  return dateStr;
};
