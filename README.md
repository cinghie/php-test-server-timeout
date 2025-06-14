# Php Test Server Timeout
![License: BSD 3-Clause](https://img.shields.io/badge/License-BSD%203--Clause-blue.svg)
![Last Commit](https://img.shields.io/github/last-commit/cinghie/php-test-server-timeout)
![GitHub all releases](https://img.shields.io/github/downloads/cinghie/php-test-server-timeout/total)

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
