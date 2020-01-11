//Includes
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

//USER CONFIGURATION
const char* WIFI_SSID = "";
const char* WIFI_PASS = "";
const char* HW_TOKEN = "";
const char* SERVER_ADRESS = "";

StaticJsonDocument<250> jsonBuffer;
String requestJson = "";
ADC_MODE(ADC_VCC);

//Pins
#define SENZORPIN 12 //12

void setup() {   
    Serial.begin(9600);
    WiFi.persistent(false);
    WiFi.mode(WIFI_STA);

    WiFi.begin(WIFI_SSID, WIFI_PASS);
    checkConnection();
    Serial.print("TEST");
    jsonBuffer = {};
    jsonBuffer["token"] = HW_TOKEN;
    jsonBuffer["values"]["door"]["value"] = 0;

    int batteryVoltage = (ESP.getVcc() + 600) / 1000;
    jsonBuffer["values"]["battery"]["value"] = batteryVoltage;

    serializeJson(jsonBuffer, requestJson);
    sendHttpRequest(requestJson);

    delay(500);
    ESP.deepSleep(0);    
}

void loop() {
}

void setStaticIp(String ipAddress, String subnet, String gateway){
  //Set static ip
  IPAddress staticIpAddress;
  IPAddress subnetIpAddress;
  IPAddress gatewayIpAddress;

  if (
    staticIpAddress.fromString(ipAddress) &&
    subnetIpAddress.fromString(subnet) &&
    gatewayIpAddress.fromString(gateway) &&
    WiFi.localIP() != staticIpAddress
  ) {
      WiFi.config(staticIpAddress, subnetIpAddress, gatewayIpAddress);
      Serial.print("STATIC IP address:");
      Serial.println(WiFi.localIP());
  }
}

String sendHttpRequest (String requestJson) {
    HTTPClient http;
    http.setReuse(true);
    http.begin(SERVER_ADRESS);
    http.addHeader("Content-Type", "text/plain");  //Specify content-type header

    int httpCode = http.POST(requestJson);
    String payload = http.getString();  //Get the response payload
    http.end();

    if (httpCode == -1) {
      ESP.restart();
    }
    return payload;
}

bool checkConnection() {
    int count = 0;
    while ( count < 30 ) {
      delay(500);
      if (WiFi.status() == WL_CONNECTED) {
          return true;
      }
      count++;
    }
    return false;
}
