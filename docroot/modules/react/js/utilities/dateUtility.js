import moment from 'moment-timezone';

export const getDateFormat = () => {
  const format = 'MMM DD, YYYY';
  return format;
};

export const getDate = (date, locale) => {
  const dateObj = new Date(date);
  moment.locale(locale);
  const dateStr = moment(dateObj).format(getDateFormat());
  return dateStr;
};
