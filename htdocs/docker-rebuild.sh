#!/bin/bash

configPath="htdocs/config.ini";

if [ ! -f $configPath ]; then
cat >$configPath <<EOL;
## config.tpl.ini
[auth]
allowedIPs[] = 127.0.0.1
allowedIPs[] = 172.17.0.1
httpAuthUser = username
httpAuthPass = password
debug = on

[sensor]
debug = on

[fan]
# provider = jablotron
debug = on

[dummy]
debug = on

[jablotron]
debug=on
EOL
fi

name="luftdaten-api-fancontrol";
docker build -t $name .;
docker stop $name;
docker rm $name;
echo 'docker run -d --name $name -p 80 $name';
docker run -d --name $name -p 80 $name;
docker port $name;
sleep 2

port="$(docker port $name |grep 0.0.0.0|awk -F '0.0.0.0:' '{print $2}')"
function curlTest() {
    echo "curl-test";
    curl --data '{"sensordatavalues":[{"value_type":"SDS_P1","value":"1"},{"value_type":"SDS_P2","value":"2"}]}' http://127.0.0.1:$1/data.php  -u username:password
}
curlTest $port;
sleep 1;
curlTest $port;
