Set-Location -Path C:\Bitnami\wampstack-7.3.7-0\apache2\htdocs\betanalyzer\datapull

[int]$hour = get-date -format HH
If($hour -lt 1 -or $hour -gt 23){ 
exit
stop-process -Id $PID
}
Else{
Start-Sleep -s 60
cmd.exe /c 'update_nfl.bat' 
."C:\Bitnami\wampstack-7.3.7-0\apache2\htdocs\betanalyzer\datapull\update_nfl.ps1"
}