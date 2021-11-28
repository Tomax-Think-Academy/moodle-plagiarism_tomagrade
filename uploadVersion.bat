zip.bat
aws s3 cp ./TG-moodle.zip s3://public.tomagrade.com/ApplicationSetup/WindowsSetup/TG-moodle.zip
aws s3api put-object-acl --bucket public.tomagrade.com --key ApplicationSetup/WindowsSetup/TG-moodle.zip --acl public-read

echo You can test the link now - https://s3.eu-central-1.amazonaws.com/public.tomagrade.com/ApplicationSetup/WindowsSetup/TG-moodle.zip
pause
