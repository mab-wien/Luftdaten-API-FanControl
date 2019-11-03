# Luftdaten-API-FanControl
## Description
Room air control Based on air quality
### Functionality
1) The fan starts with good air quality
2) Maximum performance with best air quality
3) In case of bad values, the ventilation is stopped
### Classification 
The air quality classification is based on iOS app Breathe \
see https://apps.apple.com
## Environment/Setup
### SDS011 [PM2.5/10], DHT22 [Temp/Hum]
See https://luftdaten.at
### LAMP on Raspbian [API/data.php]
See This-Gitlab
### LG Ventech System [Fan/Ventilation]
See https://www.pichlerluft.at
### JB-118N [Bus/Relais]
See https://www.jablotron.com
## Notice
Use return flap and Bus isolator
## Requirements
### PHP >= 7.0
#### Provider
##### myJabloton
+ PHP/curl Support
### Optional
#### Docker
+ docker build -t luftdaten-apil .
+ docker run -d --name luftdaten-api -d -p 80:80  -v "htdocs":/var/www/html fan-control