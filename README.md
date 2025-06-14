# Php Test Server Timeout

This script performs a self-request to the server to verify its availability and response behavior: 

  - measures execution time
  - checks for cURL timeouts
  - checks HTTP 4xx/5xx errors
  - checks unexpected failures

If any issue occurs, it logs to a log file:

 - timestamp
 - duration
 - HTTP response
 - partial response body

It's designed to silently pass if everything works, logging only in case of server issues.
