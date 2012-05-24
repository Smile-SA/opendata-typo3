#
# Delete tx_icsodappstore_month_logs's data
#
DELETE FROM tx_icsodappstore_month_logs WHERE DATEDIFF(CURDATE(),FROM_UNIXTIME(tstamp,'%Y-%m-%d'))>30;

#
# Copy data from tx_icsodappstore_logs to tx_icsodappstore_month_logs
#
INSERT INTO tx_icsodappstore_month_logs 
SELECT * FROM tx_icsodappstore_logs
WHERE deleted=0 AND DATEDIFF(CURDATE(),FROM_UNIXTIME(tstamp,'%Y-%m-%d'))>0
;

#
# Count log: insert from tx_icsodappstore_logs to tx_icsodappstore_statistics
#
INSERT INTO tx_icsodappstore_statistics (date, application, cmd, count)
SELECT UNIX_TIMESTAMP(FROM_UNIXTIME(tstamp,'%Y-%m-%d')) AS day,application,cmd,COUNT(cmd) FROM tx_icsodappstore_logs 
WHERE deleted=0 AND DATEDIFF(CURDATE(),FROM_UNIXTIME(tstamp,'%Y-%m-%d'))>0
GROUP BY day,cmd,application
ORDER BY day,application
;

#
# Delete tx_icsodappstore_logs's data
#
DELETE FROM tx_icsodappstore_logs WHERE DATEDIFF(CURDATE(),FROM_UNIXTIME(tstamp,'%Y-%m-%d'))>0;