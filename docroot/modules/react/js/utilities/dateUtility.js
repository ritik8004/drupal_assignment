import moment from 'moment-timezone';
import TimeAgo from 'javascript-time-ago';
import en from 'javascript-time-ago/locale/en';
import ar from 'javascript-time-ago/locale/ar';

export const getDateFormat = () => {
  const format = 'MMM DD, YYYY';
  return format;
};

export const getDate = (date) => {
  const dateObj = new Date(date);
  const dateStr = moment(dateObj).format(getDateFormat());
  return dateStr;
};

export const getTimeAgoDate = (date, countryCode, langCode) => {
  TimeAgo.addLocale(en);
  TimeAgo.addLocale(ar);
  const timeAgo = new TimeAgo(`${langCode}-${countryCode}`);
  return timeAgo.format(new Date(date));
};
