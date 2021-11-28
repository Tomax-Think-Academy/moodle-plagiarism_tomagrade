del  TG-moodle.zip
mkdir tomagrade
copy * tomagrade
Xcopy  /S /I /E classes  tomagrade\classes
Xcopy  /S /I /E db  tomagrade\db
Xcopy  /S /I /E lang  tomagrade\lang
Xcopy  /S /I /E pix  tomagrade\pix
rmdir tomagrade\tomagrade /s /q
tar.exe -a -c -f TG-moodle.zip tomagrade
rmdir tomagrade /s /q