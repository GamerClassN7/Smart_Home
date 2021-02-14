$lines = (ls  -r|sls '^\s*(#|$)' -a -n).Count
write-host $lines" of code"