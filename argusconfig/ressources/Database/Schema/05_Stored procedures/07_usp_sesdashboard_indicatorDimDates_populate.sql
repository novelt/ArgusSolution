-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.16-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*
	This stored procedure fills the content of the table sesdashboard_indicatordimdate

	Use example: CALL usp_sesdashboard_indicatorDimDates_populate('2015/01/01', '2030/12/31');

	=============================================
	Description:	http://arcanecode.com/2009/11/18/populating-a-kimball-date-dimension/
	=============================================

	A few notes, this code does nothing to the existing table, no deletes
	are triggered before hand. Because the DateKey is uniquely indexed,
	it will simply produce errors if you attempt to insert duplicates.
	You can however adjust the Begin/End dates and rerun to safely add
	new dates to the table every year.

	If the begin date is after the end date, no errors occur but nothing
	happens as the while loop never executes.
	
	Changes:
		- 2017-06-13: MT-gcWxC3WJ-indicators-calculation-split-per-epidemiologic-week:	Fill the new column weekYear in the table sesdashboard_indicatordimdate, which "correlates" to the week number. (cf look the calendarYear and weekYear for '2017-01-01')
																						And changed the formula of weekOfYear, to be sure that the calculation of weekYear and weekOfYear have the same base (YEARWEEK(DateCounter, 3))
*/

-- Dumping structure for procedure avadar_test.usp_sesdashboard_indicatorDimDates_populate
DROP PROCEDURE IF EXISTS `usp_sesdashboard_indicatorDimDates_populate`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_sesdashboard_indicatorDimDates_populate`(
	IN `BeginDate` DATETIME,
	IN `EndDate` DATETIME
)
BEGIN

	# Holds a flag so we can determine if the date is the last day of month
	DECLARE LastDayOfMon CHAR(1);

	# Number of months to add to the date to get the current Fiscal date
	DECLARE FiscalYearMonthsOffset INT; 

	# These two counters are used in our loop.
	DECLARE DateCounter DATETIME;    #Current date in loop
	DECLARE FiscalCounter DATETIME;  #Fiscal Year Date in loop

	# Set this to the number of months to add to the current date to get
	# the beginning of the Fiscal year. For example, if the Fiscal year
	# begins July 1, put a 6 there.
	# Negative values are also allowed, thus if your 2010 Fiscal year
	# begins in July of 2009, put a -6.
	SET FiscalYearMonthsOffset = 6;

	# Start the counter at the begin date
	SET DateCounter = BeginDate;

	WHILE DateCounter <= EndDate DO
            # Calculate the current Fiscal date as an offset of
            # the current date in the loop

            SET FiscalCounter = DATE_ADD(DateCounter, INTERVAL FiscalYearMonthsOffset MONTH);

            # Set value for IsLastDayOfMonth
            IF MONTH(DateCounter) = MONTH(DATE_ADD(DateCounter, INTERVAL 1 DAY)) THEN
               SET LastDayOfMon = 0;
            ELSE
               SET LastDayOfMon = 1;
			END IF;

            # add a record into the date dimension table for this date
            INSERT IGNORE INTO sesdashboard_indicatordimdate
				   (id
				   ,fullDate
				   ,dateName
				   ,dateNameUS
				   ,dateNameEU
				   ,dayOfWeek
				   ,dayNameOfWeek
				   ,dayOfMonth
				   ,dayOfYear
				   ,weekdayWeekend
				   ,weekOfYear
				   ,weekYear
				   ,monthName
				   ,monthOfYear
				   ,isLastDayOfMonth
				   ,calendarQuarter
				   ,calendarYear
				   ,calendarYearMonth
				   ,calendarYearQtr
				   ,fiscalMonthOfYear
				   ,fiscalQuarter
				   ,fiscalYear
				   ,fiscalYearMonth
				   ,fiscalYearQtr)
            VALUES  (
                    ( YEAR(DateCounter) * 10000 ) + ( MONTH(DateCounter) * 100 ) + DAY(DateCounter)  #DateKey
                    , DateCounter # FullDate
                    , CONCAT(CAST(YEAR(DateCounter) AS CHAR(4)),'/',DATE_FORMAT(DateCounter,'%m'),'/',DATE_FORMAT(DateCounter,'%d')) #DateName
                    , CONCAT(DATE_FORMAT(DateCounter,'%m'),'/',DATE_FORMAT(DateCounter,'%d'),'/',CAST(YEAR(DateCounter) AS CHAR(4)))#DateNameUS
                    , CONCAT(DATE_FORMAT(DateCounter,'%d'),'/',DATE_FORMAT(DateCounter,'%m'),'/',CAST(YEAR(DateCounter) AS CHAR(4)))#DateNameEU
                    , DAYOFWEEK(DateCounter) #DayOfWeek
                    , DAYNAME(DateCounter) #DayNameOfWeek
                    , DAYOFMONTH(DateCounter) #DayOfMonth
                    , DAYOFYEAR(DateCounter) #DayOfYear
                    , CASE DAYNAME(DateCounter)
                        WHEN 'Saturday' THEN 'Weekend'
                        WHEN 'Sunday' THEN 'Weekend'
                        ELSE 'Weekday'
                      END #WeekdayWeekend
                    , RIGHT(CAST(YEARWEEK(DateCounter, 3) as CHAR(6)), 2) #WeekOfYear
					, LEFT(CAST(YEARWEEK(DateCounter, 3) as CHAR(6)), 4) #WeekYear
                    , MONTHNAME(DateCounter) #MonthName
                    , MONTH(DateCounter) #MonthOfYear
                    , LastDayOfMon #IsLastDayOfMonth
                    , QUARTER(DateCounter) #CalendarQuarter					
                    , YEAR(DateCounter) #CalendarYear
                    , CONCAT(CAST(YEAR(DateCounter) AS CHAR(4)),'-',DATE_FORMAT(DateCounter,'%m')) #CalendarYearMonth
                    , CONCAT(CAST(YEAR(DateCounter) AS CHAR(4)),'Q',QUARTER(DateCounter)) #CalendarYearQtr
                    , MONTH(FiscalCounter) #[FiscalMonthOfYear]
                    , QUARTER(FiscalCounter) #[FiscalQuarter]
                    , YEAR(FiscalCounter) #[FiscalYear]
                    , CONCAT(CAST(YEAR(FiscalCounter) AS CHAR(4)),'-',DATE_FORMAT(FiscalCounter,'%m')) #[FiscalYearMonth]
                    , CONCAT(CAST(YEAR(FiscalCounter) AS CHAR(4)),'Q',QUARTER(FiscalCounter)) #[FiscalYearQtr]
                    );

            # Increment the date counter for next pass thru the loop
            SET DateCounter = DATE_ADD(DateCounter, INTERVAL 1 DAY);
      END WHILE;

END//
DELIMITER ;