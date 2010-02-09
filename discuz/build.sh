#/bin/sh

ROOT=$PWD
cd static_src/webapi
make clean
make
cd $ROOT
echo $ROOT
make clean
make static

java -jar static_src/build/yuicompressor-2.4.2.jar  --type js static/webim.all.temp.js > static/webim.temp.js 
native2ascii -encoding utf-8 static/webim.temp.js > static/webim.all.js
rm static/*temp.js
