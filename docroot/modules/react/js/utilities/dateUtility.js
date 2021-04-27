import moment from 'moment-timezone';
import TimeAgo from 'javascript-time-ago';
import en from 'javascript-time-ago/locale/en';
import ar from 'javascript-time-ago/locale/ar';

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

export const getTimeAgoDate = (date, countryCode, langCode) => {
  if (langCode === 'en') {
    TimeAgo.addLocale(en);
  } else if (langCode === 'ar') {
    TimeAgo.addLocale(ar);
  }
  const timeAgo = new TimeAgo(`${langCode}-${countryCode}`);
  return timeAgo.format(new Date(date));
};
