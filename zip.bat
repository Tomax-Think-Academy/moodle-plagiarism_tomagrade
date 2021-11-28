rm TG-moodle.zip
mkdir tomagrade
cp -r ./* ./tomagrade
rm -rf ./tomagrade/tomagrade
tar.exe -a -c -f TG-moodle.zip tomagrade
rm -rf ./tomagrade