@echo off
TITLE PocketMine-SteadFast3 server software for Minecraft: Pocket Edition
cd /d %~dp0

if exist bin\php\php.exe (
	set PHPRC=""
	set PHP_BINARY=bin\php\php.exe
) else (
	set PHP_BINARY=php
)

if exist SteadFast3.phar (
	set SF_FILE=SteadFast3.phar
) else (
	echo SteadFast3.phar not found
	echo Downloads can be found at https://github.com/MFDGaming/PocketMine-SteadFast3/releases
	pause
	exit 1
)

REM pause on exitcode != 0 so the user can see what went wrong
%PHP_BINARY% -c bin\php %SF_FILE% %* || pause
