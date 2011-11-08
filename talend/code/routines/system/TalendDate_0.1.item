// ============================================================================
//
// %GENERATED_LICENSE%
//
// ============================================================================
package routines;

import java.text.FieldPosition;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;

import routines.system.FastDateParser;
import routines.system.LocaleProvider;

public class TalendDate {

    /**
     * get part of date. like YEAR, MONTH, HOUR, or DAY_OF_WEEK, WEEK_OF_MONTH, WEEK_OF_YEAR, TIMEZONE and so on
     * 
     * @param partName which part to get.
     * @param date the date value.
     * @return the specified part value.
     * 
     * {talendTypes} Integer
     * 
     * {Category} TalendDate
     * 
     * {param} string("DAY_OF_WEEK") partName : which part to get
     * 
     * {param} date(TalendDate.parseDate("yyyy-MM-dd", "2010-12-26")) date : the date value
     * 
     * {example} getPartOfDate("DAY_OF_WEEK", TalendDate.parseDate("yyyy-MM-dd", "2010-12-26")) #
     */
    public static int getPartOfDate(String partName, Date date) {

        if (partName == null || date == null)
            return 0;
        int ret = 0;
        String[] fieldsName = { "YEAR", "MONTH", "HOUR", "MINUTE", "SECOND", "DAY_OF_WEEK", "DAY_OF_MONTH", "DAY_OF_YEAR",
                "WEEK_OF_MONTH", "DAY_OF_WEEK_IN_MONTH", "WEEK_OF_YEAR", "TIMEZONE" };
        java.util.List<String> filedsList = java.util.Arrays.asList(fieldsName);
        Calendar c = Calendar.getInstance();
        c.setTime(date);

        switch (filedsList.indexOf(partName)) {
        case 0:
            ret = c.get(Calendar.YEAR);
            break;
        case 1:
            ret = c.get(Calendar.MONTH);
            break;
        case 2:
            ret = c.get(Calendar.HOUR);
            break;
        case 3:
            ret = c.get(Calendar.MINUTE);
            break;
        case 4:
            ret = c.get(Calendar.SECOND);
            break;
        case 5:
            ret = c.get(Calendar.DAY_OF_WEEK);
            break;
        case 6:
            ret = c.get(Calendar.DAY_OF_MONTH);
            break;
        case 7:
            ret = c.get(Calendar.DAY_OF_YEAR);
            break;
        case 8:
            // the ordinal number of current week in a month (it means a 'week' may be not contain 7 days)
            ret = c.get(Calendar.WEEK_OF_MONTH);
            break;
        case 9:
            // 1-7 correspond to 1, 8-14 correspond to 2,...
            ret = c.get(Calendar.DAY_OF_WEEK_IN_MONTH);
            break;
        case 10:
            ret = c.get(Calendar.WEEK_OF_YEAR);
            break;
        case 11:
            ret = (c.get(Calendar.ZONE_OFFSET)) / (1000 * 60 * 60);
            break;
        default:
            break;

        }
        return ret;
    }

    /**
     * Formats a Date into a date/time string.
     * 
     * @param pattern the pattern to format.
     * @param date the time value to be formatted into a time string.
     * @return the formatted time string.
     * 
     * {talendTypes} String
     * 
     * {Category} TalendDate
     * 
     * {param} string("yyyy-MM-dd HH:mm:ss") pattern : the pattern to format
     * 
     * {param} date(myDate) date : the time value to be formatted into a time string
     * 
     * {example} formatDate("yyyy-MM-dd", new Date()) #
     */

    public synchronized static String formatDate(String pattern, java.util.Date date) {
        return FastDateParser.getInstance(pattern).format(date);
    }

    /**
     * test string value as a date (with right pattern)
     * 
     * @param stringDate (A <code>String</code> whose beginning should be parsed)
     * @param pattern (the pattern to format, like: "yyyy-MM-dd HH:mm:ss")
     * @return the result wheather the stringDate is a date string that with a right pattern
     * 
     * {talendTypes} Boolean
     * 
     * {Category} TalendDate
     * 
     * {param} String(mydate) stringDate : the date to judge
     * 
     * {param} String("yyyy-MM-dd HH:mm:ss") pattern : the specified pattern
     * 
     * {examples}
     * 
     * ->> isDate("2008-11-24 12:15:25", "yyyy-MM-dd HH:mm:ss") return true
     * 
     * ->> isDate("2008-11-24 12:15:25", "yyyy-MM-dd HH:mm") return false
     * 
     * ->> isDate("2008-11-32 12:15:25", "yyyy-MM-dd HH:mm:ss") return false #
     */
    public static boolean isDate(String stringDate, String pattern) {

        if (stringDate == null) {
            return false;
        }
        if (pattern == null) {
            pattern = "yyyy-MM-dd HH:mm:ss";
        }

        java.text.SimpleDateFormat sdf = new java.text.SimpleDateFormat(pattern);
        java.util.Date testDate = null;

        try {
            testDate = sdf.parse(stringDate);
        } catch (ParseException e) {
            return false;
        }

        if (!sdf.format(testDate).equalsIgnoreCase(stringDate)) {
            return false;
        }

        return true;
    }

    /**
     * compare two date
     * 
     * @param date1 (first date)
     * @param date2 (second date)
     * @param pattern (compare specified part, example: "yyyy-MM-dd")
     * @return the result wheather two date is the same, if first one less than second one return number -1, equlas
     * return number 0, bigger than return number 1. (can compare partly)
     * 
     * {talendTypes} Integer
     * 
     * {Category} TalendDate
     * 
     * {param} date(myDate) date1 : the first date to compare
     * 
     * {param} date(myDate2) date2 : the second date to compare
     * 
     * {param} String("yyyy-MM-dd") pattern : compare specified part
     * 
     * {examples}
     * 
     * ->> compareDate(2008/11/24 12:15:25, 2008/11/24 16:10:35) return -1
     * 
     * ->> compareDate(2008/11/24 16:10:35, 2008/11/24 12:15:25) return 1
     * 
     * ->> compareDate(2008/11/24 12:15:25, 2008/11/24 16:10:35,"yyyy/MM/dd") return 0 #
     */
    public static int compareDate(Date date1, Date date2, String pattern) {
        if (date1 == null && date2 == null) {
            return 0;
        } else if (date1 != null && date2 == null) {
            return 1;
        } else if (date1 == null && date2 != null) {
            return -1;
        }

        if (pattern != null) {
            SimpleDateFormat sdf = new SimpleDateFormat(pattern);
            String part1 = sdf.format(date1), part2 = sdf.format(date2);
            return (part1.compareTo(part2) >= 1 ? 1 : (part1.compareTo(part2) <= -1 ? -1 : 0));
        } else {
            long time1 = date1.getTime(), time2 = date2.getTime();
            return (time1 < time2 ? -1 : (time1 == time2 ? 0 : 1));
        }
    }

    /**
     * compare two date
     * 
     * @param date1 (first date)
     * @param date2 (second date)
     * @return the result wheather two date is the same, if first one less than second one return number -1, equlas
     * return number 0, bigger than return number 1. (can compare partly)
     * 
     * {talendTypes} Integer
     * 
     * {Category} TalendDate
     * 
     * {param} date(myDate) date1 : the first date to compare
     * 
     * {param} date(myDate2) date2 : the second date to compare
     * 
     * {example} compareDate(2008/11/24 12:15:25, 2008/11/24 16:10:35) return -1 #
     * 
     */
    public static int compareDate(Date date1, Date date2) {
        return compareDate(date1, date2, null);
    }

    /**
     * add number of day, month ... to a date (with Java date type !)
     * 
     * @param date (a <code>Date</code> type value)
     * @param nb (the value to add)
     * @param dateType (date pattern = ("yyyy","MM","dd","HH","mm","ss","SSS" ))
     * @return a new date
     * 
     * {talendTypes} Date
     * 
     * {Category} TalendDate
     * 
     * {param} date(myDate) date : the date to update
     * 
     * {param} date(addValue) nb : the added value
     * 
     * {param} date("MM") dateType : the part to add
     * 
     * {examples}
     * 
     * ->> addDate(dateVariable), 5,"dd") return a date with 2008/11/29 12:15:25 (with dateVariable is a date with
     * 2008/11/24 12:15:25) #
     * 
     * ->> addDate(2008/11/24 12:15:25, 5,"ss") return 2008/11/24 12:15:30 #
     * 
     */
    public static Date addDate(Date date, int nb, String dateType) {
        if (date == null || dateType == null) {
            return null;
        }

        Calendar c1 = Calendar.getInstance();
        c1.setTime(date);

        if (dateType.equalsIgnoreCase("yyyy")) { //$NON-NLS-1$
            c1.add(Calendar.YEAR, nb);
        } else if (dateType.equals("MM")) { //$NON-NLS-1$
            c1.add(Calendar.MONTH, nb);
        } else if (dateType.equalsIgnoreCase("dd")) { //$NON-NLS-1$
            c1.add(Calendar.DAY_OF_MONTH, nb);
        } else if (dateType.equals("HH")) { //$NON-NLS-1$
            c1.add(Calendar.HOUR, nb);
        } else if (dateType.equals("mm")) { //$NON-NLS-1$
            c1.add(Calendar.MINUTE, nb);
        } else if (dateType.equalsIgnoreCase("ss")) { //$NON-NLS-1$
            c1.add(Calendar.SECOND, nb);
        } else if (dateType.equalsIgnoreCase("SSS")) { //$NON-NLS-1$
            c1.add(Calendar.MILLISECOND, nb);
        } else {
            throw new RuntimeException("Can't support the dateType: " + dateType);
        }

        return c1.getTime();
    }

    /**
     * add number of day, month ... to a date (with Date given in String with a pattern)
     * 
     * @param date (a Date given in string)
     * @param pattern (the pattern for the related date)
     * @param nb (the value to add)
     * @param dateType (date pattern = ("yyyy","MM","dd","HH","mm","ss","SSS" ))
     * @return a new date
     * 
     * {talendTypes} String
     * 
     * {Category} TalendDate
     * 
     * {param} date(myDate) date : the date to update
     * 
     * {param} date(pattern) string : the pattern
     * 
     * {param} date(addValue) nb : the added value
     * 
     * {param} date("MM") dateType : the part to add
     * 
     * {examples}
     * 
     * ->> addDate("2008/11/24 12:15:25", "yyyy-MM-dd HH:mm:ss", 5,"dd") return "2008/11/29 12:15:25"
     * 
     * ->> addDate("2008/11/24 12:15:25", "yyyy/MM/DD HH:MM:SS", 5,"ss") return "2008/11/24 12:15:30" #
     * 
     */
    public static String addDate(String string, String pattern, int nb, String dateType) {
        if (string == null || dateType == null) {
            return null;
        }
        java.util.Date date = null;

        java.text.SimpleDateFormat sdf = new java.text.SimpleDateFormat(pattern);
        try {
            date = sdf.parse(string);
        } catch (ParseException e) {
            throw new RuntimeException(pattern + " can't support the date!"); //$NON-NLS-1$
        }
        String dateString = sdf.format(addDate(date, nb, dateType));

        return dateString;
    }

    /**
     * return difference between two dates
     * 
     * @param Date1 ( first date )
     * @param Date1 ( second date )
     * @param dateType value=("yyyy","MM","dd","HH","mm","ss","SSS") for type of return
     * @return a number of years, months, days ... date1 - date2
     * 
     * {talendTypes} Long
     * 
     * {Category} TalendDate
     * 
     * {param} date(myDate) date1 : the first date to compare
     * 
     * {param} date(myDate2) date2 : the second date to compare
     * 
     * {param} String("MM") dateType : the difference on the specified part
     * 
     * {examples}
     * 
     * ->> diffDate(2008/11/24 12:15:25, 2008/10/14 16:10:35, "yyyy") : return 0
     * 
     * ->> diffDate(2008/11/24 12:15:25, 2008/10/14 16:10:35, "MM") : return 1
     * 
     * ->> diffDate(2008/11/24 12:15:25, 2008/10/14 16:10:35, "dd") : return 41 #
     */
    public static long diffDate(Date date1, Date date2, String dateType) {

        if (date1 == null) {
            date1 = new Date(0);
        }
        if (date2 == null) {
            date2 = new Date(0);
        }

        if (dateType == null) {
            dateType = "SSS";
        }

        Calendar c1 = Calendar.getInstance();
        Calendar c2 = Calendar.getInstance();
        c1.setTime(date1);
        c2.setTime(date2);

        if (dateType.equalsIgnoreCase("yyyy")) { //$NON-NLS-1$
            return c1.get(Calendar.YEAR) - c2.get(Calendar.YEAR);
        } else if (dateType.equals("MM")) { //$NON-NLS-1$
            return (c1.get(Calendar.YEAR) - c2.get(Calendar.YEAR)) * 12 + (c1.get(Calendar.MONTH) - c2.get(Calendar.MONTH));
        } else if (dateType.equalsIgnoreCase("HH")) { //$NON-NLS-1$
            return (date1.getTime() - date2.getTime()) / (1000 * 60 * 60);
        } else if (dateType.equals("mm")) { //$NON-NLS-1$
            return (date1.getTime() - date2.getTime()) / (1000 * 60);
        } else if (dateType.equalsIgnoreCase("ss")) { //$NON-NLS-1$
            return (date1.getTime() - date2.getTime()) / 1000;
        } else if (dateType.equalsIgnoreCase("SSS")) { //$NON-NLS-1$
            return date1.getTime() - date2.getTime();
        } else if (dateType.equalsIgnoreCase("dd")) {
            return (date1.getTime() - date2.getTime()) / (1000 * 60 * 60 * 24);
        } else {
            throw new RuntimeException("Can't support the dateType: " + dateType);
        }
    }

    /**
     * return difference between two dates by floor
     * 
     * @param Date1 ( first date )
     * @param Date1 ( second date )
     * @param dateType value=("yyyy","MM") for type of return
     * @return a number of years, months (date1 - date2)
     * 
     * {talendTypes} Integer
     * 
     * {Category} TalendDate
     * 
     * {param} date(myDate) date1 : the first date to compare
     * 
     * {param} date(myDate2) date2 : the second date to compare
     * 
     * {param} String("MM") dateType : the difference on the specified part
     * 
     * {examples}
     * 
     * ->> diffDate(2009/05/10, 2008/10/15, "yyyy") : return 0
     * 
     * ->> diffDate(2009/05/10, 2008/10/15, "MM") : return 6
     */
    public static int diffDateFloor(Date date1, Date date2, String dateType) {
        if (date1 == null) {
            date1 = new Date(0);
        }
        if (date2 == null) {
            date2 = new Date(0);
        }

        if (dateType == null) {
            dateType = "yyyy";
        }

        Calendar c1 = Calendar.getInstance();
        Calendar c2 = Calendar.getInstance();
        c1.setTime(date1);
        c2.setTime(date2);

        int result = 0;
        Calendar tmp = null;
        boolean flag = false;
        if (c1.compareTo(c2) < 0) {
            flag = true;
            tmp = c1;
            c1 = c2;
            c2 = tmp;
        }
        result = (c1.get(Calendar.YEAR) - c2.get(Calendar.YEAR)) * 12 + (c1.get(Calendar.MONTH) - c2.get(Calendar.MONTH));
        c2.add(Calendar.MONTH, result);
        result += c2.after(c1) ? -1 : 0;
        if (flag) {
            result = -result;
        }

        if (dateType.equalsIgnoreCase("yyyy")) {
            return result / 12;
        } else if (dateType.equals("MM")) {
            return result;
        } else {
            throw new RuntimeException("Can't support the dateType: " + dateType + " ,please try \"yyyy\" or \"MM\"");
        }
    }

    /**
     * return difference between two dates
     * 
     * @param Date1 ( first date )
     * @param Date1 ( second date )
     * @return a number of years, months, days ... date1 - date2
     * 
     * {talendTypes} Long
     * 
     * {Category} TalendDate
     * 
     * {param} date(myDate) date1 : the first date to compare
     * 
     * {param} date(myDate) date2 : the second date to compare
     * 
     * {examples} diffDate(2008/11/24 12:15:25, 2008/10/14 16:10:35) : return 41 #
     */

    public static long diffDate(Date date1, Date date2) {
        return diffDate(date1, date2, "dd");
    }

    /**
     * get first day of the month
     * 
     * @param date (a date value)
     * @return a new date (the date has been changed to the first day)
     * 
     * {talendTypes} Date
     * 
     * {Category} TalendDate
     * 
     * {param} date(mydate) date : the date to get first date of current month
     * 
     * {example} getFirstDayMonth(2008/02/24 12:15:25) return 2008/02/01 12:15:25 #
     */
    public static Date getFirstDayOfMonth(Date date) {
        if (date == null) {
            return null;
        }
        Calendar c = Calendar.getInstance();
        c.setTime(date);
        c.set(Calendar.DATE, 1);
        return c.getTime();
    }

    /**
     * get last day of the month
     * 
     * @param date (a date value)
     * @return a new date (the date has been changed to the last day)
     * 
     * {talendTypes} Date
     * 
     * {Category} TalendDate
     * 
     * {param} date(mydate) date : the date to get last date of current month
     * 
     * {example} getFirstDayMonth(2008/02/24 12:15:25) return 2008/02/28 12:15:25
     */
    public static Date getLastDayOfMonth(Date date) {
        if (date == null) {
            return null;
        }
        Calendar c = Calendar.getInstance();
        c.setTime(date);
        int lastDay = c.getActualMaximum(Calendar.DAY_OF_MONTH);
        c.set(Calendar.DATE, lastDay);
        return c.getTime();
    }

    /**
     * 
     * set a date new value partly
     * 
     * @param date (a date value)
     * @param nb (new number)
     * @param dateType (the part)
     * @return a new date
     * 
     * {talendTypes} Date
     * 
     * {Category} TalendDate
     * 
     * {param} date(mydate) date : the date to set
     * 
     * {param} Integer(newValue) nb : the new value
     * 
     * {param} String("MM") dateType : the part to set
     * 
     * {examples}
     * 
     * ->> setDate(2008/11/24 12:15:25, 2010, "yyyy") return 2010/11/24 12:15:25
     * 
     * ->> setDate(2008/11/24 12:15:25, 01, "MM") return 2008/01/24 12:15:25
     * 
     * ->> setDate(2008/11/24 12:15:25, 15, "dd") return 2008/11/15 12:15:25 #
     */
    public static Date setDate(Date date, int nb, String dateType) {
        if (date == null || dateType == null) {
            return null;
        }

        // if (nb < 0) {
        // return date;
        // }

        Calendar c = Calendar.getInstance();
        c.setTime(date);

        if (dateType.equalsIgnoreCase("yyyy")) { //$NON-NLS-1$
            c.set(Calendar.YEAR, nb);
        } else if (dateType.equals("MM")) { //$NON-NLS-1$
            c.set(Calendar.MONTH, nb - 1);
        } else if (dateType.equalsIgnoreCase("dd")) { //$NON-NLS-1$
            c.set(Calendar.DATE, nb);
        } else if (dateType.equalsIgnoreCase("HH")) { //$NON-NLS-1$
            c.set(Calendar.HOUR_OF_DAY, nb);
        } else {
            throw new RuntimeException("Can't support the dateType: " + dateType);
        }
        return c.getTime();
    }

    /**
     * Formats a Date into a date/time string using the given pattern and the default date format symbols for the given
     * locale.
     * 
     * @param pattern the pattern to format.
     * @param date the time value to be formatted into a time string.
     * @param locale the locale whose date format symbols should be used.
     * @return the formatted time string.
     * 
     * {talendTypes} String
     * 
     * {Category} TalendDate
     * 
     * {param} string("yyyy-MM-dd HH:mm:ss") pattern : the pattern to format
     * 
     * {param} date(myDate) date : the time value to be formatted into a time string
     * 
     * {param} string("EN") languageOrCountyCode : the language or country whose date format symbols should be used, in
     * lower or upper case
     * 
     * {example} formatDateLocale("yyyy-MM-dd", new Date(), "en") #
     */
    public synchronized static String formatDateLocale(String pattern, java.util.Date date, String languageOrCountyCode) {
        return FastDateParser.getInstance(pattern, LocaleProvider.getLocale(languageOrCountyCode)).format(date);
    }

    /**
     * Parses text from the beginning of the given string to produce a date using the given pattern and the default date
     * format symbols for the given locale. The method may not use the entire text of the given string.
     * <p>
     * 
     * @param pattern the pattern to parse.
     * @param stringDate A <code>String</code> whose beginning should be parsed.
     * @return A <code>Date</code> parsed from the string.
     * @throws ParseException
     * @exception ParseException if the beginning of the specified string cannot be parsed.
     * 
     * {talendTypes} Date
     * 
     * {Category} TalendDate
     * 
     * {param} string("yyyy-MM-dd HH:mm:ss") pattern : the pattern to parse
     * 
     * {param} string("") stringDate : A <code>String</code> whose beginning should be parsed
     * 
     * {example} parseDate("yyyy-MMM-dd HH:mm:ss", "23-Mar-1979 23:59:59") #
     */
    public synchronized static Date parseDate(String pattern, String stringDate) {
        try {
            return FastDateParser.getInstance(pattern).parse(stringDate);
        } catch (ParseException e) {
            throw new RuntimeException(e);
        }
    }

    /**
     * Parses text from the beginning of the given string to produce a date. The method may not use the entire text of
     * the given string.
     * <p>
     * 
     * @param pattern the pattern to parse.
     * @param stringDate A <code>String</code> whose beginning should be parsed.
     * @param locale the locale whose date format symbols should be used.
     * @return A <code>Date</code> parsed from the string.
     * @throws ParseException
     * @exception ParseException if the beginning of the specified string cannot be parsed.
     * 
     * {talendTypes} Date
     * 
     * {Category} TalendDate
     * 
     * {param} string("yyyy-MM-dd HH:mm:ss") pattern : the pattern to parse
     * 
     * {param} string("") stringDate : A <code>String</code> whose beginning should be parsed
     * 
     * {param} string("EN") languageOrCountyCode : the language or country whose date format symbols should be used, in
     * lower or upper case
     * 
     * {example} parseDateLocale("yyyy-MMM-dd", "23-Mar-1979", "en") #
     */
    public synchronized static Date parseDateLocale(String pattern, String stringDate, String languageOrCountyCode) {
        try {
            return FastDateParser.getInstance(pattern, LocaleProvider.getLocale(languageOrCountyCode)).parse(stringDate);
        } catch (ParseException e) {
            throw new RuntimeException(e);
        }
    }

    /**
     * getDate : return the current datetime with the given display format format : (optional) string representing the
     * wished format of the date. This string contains fixed strings and variables related to the date. By default, the
     * format string is DD/MM/CCYY. Here is the list of date variables:
     * 
     * 
     * {talendTypes} String
     * 
     * {Category} TalendDate
     * 
     * {param} string("CCYY-MM-DD hh:mm:ss") pattern : date pattern + CC for century + YY for year + MM for month + DD
     * for day + hh for hour + mm for minute + ss for second
     * 
     * {example} getDate("CCYY-MM-DD hh:mm:ss") #
     */
    public static String getDate(String pattern) {
        if (pattern == null) {
            pattern = "yyyy-MM-dd HH:mm:ss";
        }

        StringBuffer result = new StringBuffer();

        pattern = pattern.replace("CC", "yy"); //$NON-NLS-1$ //$NON-NLS-2$
        pattern = pattern.replace("YY", "yy"); //$NON-NLS-1$ //$NON-NLS-2$
        pattern = pattern.replace("MM", "MM"); //$NON-NLS-1$ //$NON-NLS-2$
        pattern = pattern.replace("DD", "dd"); //$NON-NLS-1$ //$NON-NLS-2$
        pattern = pattern.replace("hh", "HH"); //$NON-NLS-1$ //$NON-NLS-2$

        // not needed
        // pattern.replace("mm", "mm");
        // pattern.replace("ss", "ss");

        SimpleDateFormat sdf = new SimpleDateFormat(pattern);
        sdf.format(Calendar.getInstance().getTime(), result, new FieldPosition(0));
        return result.toString();
    }

    /**
     * getDate : return the current date
     * 
     * 
     * 
     * {talendTypes} Date
     * 
     * {Category} TalendDate
     * 
     * {example} getCurrentDate()
     */
    public static Date getCurrentDate() {
        return Calendar.getInstance().getTime();
    }

    /**
     * return an ISO formatted random date
     * 
     * 
     * {talendTypes} Date
     * 
     * {Category} TalendDate
     * 
     * {param} string("2007-01-01") min : minimum date
     * 
     * {param} string("2008-12-31") max : maximum date (superior to min)
     * 
     * {example} getRandomDate("1981-01-18", "2005-07-24") {example} getRandomDate("1980-12-08", "2007-02-26")
     */
    public static Date getRandomDate(String minDate, String maxDate) {
        if (minDate == null) {
            minDate = "1970-01-01";
        }

        if (maxDate == null) {
            maxDate = "2099-12-31";
        }

        if (!minDate.matches("\\d{4}-\\d{2}-\\d{2}") || !minDate.matches("\\d{4}-\\d{2}-\\d{2}")) {
            throw new IllegalArgumentException("The parameter should be \"yyy-MM-dd\"");
        }

        int minYear = Integer.parseInt(minDate.substring(0, 4));
        int minMonth = Integer.parseInt(minDate.substring(5, 7));
        int minDay = Integer.parseInt(minDate.substring(8, 10));

        int maxYear = Integer.parseInt(maxDate.substring(0, 4));
        int maxMonth = Integer.parseInt(maxDate.substring(5, 7));
        int maxDay = Integer.parseInt(maxDate.substring(8, 10));

        Calendar minCal = Calendar.getInstance();
        minCal.set(Calendar.YEAR, minYear);
        minCal.set(Calendar.MONTH, minMonth - 1);
        minCal.set(Calendar.DAY_OF_MONTH, minDay);

        Calendar maxCal = Calendar.getInstance();
        maxCal.set(Calendar.YEAR, maxYear);
        maxCal.set(Calendar.MONTH, maxMonth - 1);
        maxCal.set(Calendar.DAY_OF_MONTH, maxDay);

        long random = minCal.getTimeInMillis()
                + (long) ((maxCal.getTimeInMillis() - minCal.getTimeInMillis() + 1) * Math.random());
        return new Date(random);
    }

    /**
     * 
     * Method used for tests only.
     * 
     * @param args
     */
    public static void main(String[] args) {

        // test_formatDate();
        // test_isDate();
        // test_getRandomDate();
        // System.out.println(getPartOfDate("DAY_OF_WEEK_IN_MONTH", parseDate("yyyy-MM-dd", "2010-12-26")));
        // System.out.println(getPartOfDate("WEEK_OF_MONTH", parseDate("yyyy-MM-dd", "2010-12-26")));

        System.out.println(TalendDate.diffDateFloor(TalendDate.parseDate("yyyy/MM/dd hh:mm:ss.SSS", "2011/05/10 14:15:16.788"),
                TalendDate.parseDate("yyyy/MM/dd hh:mm:ss.SSS", "2010/05/10 14:15:16.789"), "MM"));
    }

    /**
     * 
     * Testcase:
     * <p>
     * getRandomDate(String minDate, String maxDate)
     * </p>
     */
    public static void test_getRandomDate() {
        System.out
                .println("getRandomDate: " + TalendDate.formatDate("yyyy-MM-dd HH:mm:ss", TalendDate.getRandomDate(null, null))); //$NON-NLS-1$
    }

    /**
     * 
     * Testcase:
     * <p>
     * compareDate(Date date1, Date date2)
     * </p>
     */
    public static void test_compareDate() {
        System.out
                .println("compareDate: " + Boolean.toString(TalendDate.compareDate(new Date(), new Date(System.currentTimeMillis() - 10000)) == 1)); //$NON-NLS-1$
    }

    /**
     * 
     * Testcase:
     * <p>
     * isDate(String stringDate, String pattern)
     * </p>
     */
    public static void test_isDate() {
        System.out.println("isDate: " + Boolean.toString(TalendDate.isDate("2008-11-35 12:15:25", "yyyy-MM-dd HH:mm") == false)); //$NON-NLS-1$ //$NON-NLS-2$ //$NON-NLS-3$       
    }

    /**
     * 
     * Testcase:
     * <p>
     * formatDate(String pattern, java.util.Date date)
     * </p>
     * <p>
     * formatDateLocale(String pattern, java.util.Date date, String languageOrCountyCode)
     * </p>
     */
    public static void test_formatDate() {
        final int LOOPS = 100000;
        final String dateTimeRef_Test1 = "1979-03-23 mars 12:30";
        Thread test1 = new Thread() {

            @Override
            public void run() {
                Calendar calendar = GregorianCalendar.getInstance();
                calendar.set(1979, 2, 23, 12, 30, 40);
                Date dateCalendar = calendar.getTime();
                for (int i = 0; i < LOOPS; i++) {
                    String date = TalendDate.formatDate("yyyy-MM-dd MMM HH:mm", dateCalendar);
                    // System.out.println("Test1:" + date + " # " + dateTimeRef_Test1);
                    if (!dateTimeRef_Test1.equals(date)) {
                        throw new IllegalStateException("Test1: Date ref : '" + dateTimeRef_Test1 + "' is different of '" + date
                                + "'");
                    }
                }
                System.out.println("test1 ok");
            }
        };
        final String dateTimeRef_Test2 = "1980-03-23 mars 12:30";
        Thread test2 = new Thread() {

            @Override
            public void run() {
                Calendar calendar = GregorianCalendar.getInstance();
                calendar.set(1980, 2, 23, 12, 30, 40);
                Date dateCalendar = calendar.getTime();
                for (int i = 0; i < LOOPS; i++) {
                    String date = TalendDate.formatDate("yyyy-MM-dd MMM HH:mm", dateCalendar);
                    // System.out.println("Test2:" + date + " # " + dateTimeRef_Test2);
                    if (!dateTimeRef_Test2.equals(date)) {
                        throw new IllegalStateException("Test2: Date ref : '" + dateTimeRef_Test2 + "' is different of '" + date
                                + "'");
                    }
                }
                System.out.println("test2 ok");
            }
        };

        final String dateTimeRef_Test3 = "1979-03-23 mars 12:30";
        Thread test3 = new Thread() {

            @Override
            public void run() {
                Calendar calendar = GregorianCalendar.getInstance();
                calendar.set(1979, 2, 23, 12, 30, 40);
                Date dateCalendar = calendar.getTime();
                for (int i = 0; i < LOOPS; i++) {
                    String date = TalendDate.formatDateLocale("yyyy-MM-dd MMM HH:mm", dateCalendar, "FR");
                    // System.out.println("Test3:" + date + " # " + dateTimeRef_Test3);
                    if (!dateTimeRef_Test3.equals(date)) {
                        throw new IllegalStateException("Test3: Date ref : '" + dateTimeRef_Test3 + "' is different of '" + date
                                + "'");
                    }
                }
                System.out.println("test3 ok");
            }
        };
        final String dateTimeRef_Test4 = "1980-03-23 Mar 12:30";
        Thread test4 = new Thread() {

            @Override
            public void run() {
                Calendar calendar = GregorianCalendar.getInstance();
                calendar.set(1980, 2, 23, 12, 30, 40);
                Date dateCalendar = calendar.getTime();
                for (int i = 0; i < LOOPS; i++) {
                    String date = TalendDate.formatDateLocale("yyyy-MM-dd MMM HH:mm", dateCalendar, "EN");
                    // System.out.println("Test4:" + date + " # " + dateTimeRef_Test4);
                    if (!dateTimeRef_Test4.equals(date)) {
                        throw new IllegalStateException("Test4: Date ref : '" + dateTimeRef_Test4 + "' is different of '" + date
                                + "'");
                    }
                }
                System.out.println("test4 ok");
            }
        };

        final String dateTimeRef_Test5 = "1979-03-23";
        Thread test5 = new Thread() {

            @Override
            public void run() {
                Calendar calendar = GregorianCalendar.getInstance();
                calendar.set(1979, 2, 23, 12, 30, 40);
                Date dateCalendar = calendar.getTime();
                for (int i = 0; i < LOOPS; i++) {
                    String date = TalendDate.formatDate("yyyy-MM-dd", dateCalendar);
                    // System.out.println("Test5:" + date + " # " + dateTimeRef_Test5);
                    if (!dateTimeRef_Test5.equals(date)) {
                        throw new IllegalStateException("Test5: Date ref : '" + dateTimeRef_Test5 + "' is different of '" + date
                                + "'");
                    }

                }
                System.out.println("test5 ok");
            }
        };

        test1.start();
        test2.start();
        test3.start();
        test4.start();
        test5.start();
    }
}
