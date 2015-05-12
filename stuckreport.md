
# Introduction #
When using Track To OLSA (Custom Report) mode sometimes the cycle will become stuck.

You can clear a stuck cycle by setting the "Reset the custom report cycle" setting in the [configuration](configuration#Enforce_Strict_AICC_student_id_format.md)

This will clear the previous "stuck" report and restart the cycle at the beginning by requesting a new report when the CRON job next runs.

If you are using Moodle 2.3+ you can install the optional [Diagnostic Report Tools](reports.md) to view the "Custom Report" request for diagnostics purposes.