#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include <ESP8266WebServer.h>
#include <EEPROM.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include "DHT.h"

String ssid = "";
String pasw = "";
String hwId = "";
String url = "";

//Pins
#define LIGHTPIN 13
#define DHTPIN 4
#define DHTTYPE DHT11
#define LED_BUILTIN 16

//Settings
bool deepSleepOn = true;
long sleepTime = 4;  //in minutes
bool lightSensor = true;
bool humiditySensor = true;
bool temperatureSensor = true;

ESP8266WebServer server(80); 
DHT dht(DHTPIN, DHTTYPE);



void setup() {
  Serial.begin(9600);
  Serial.setDebugOutput(true);
  EEPROM.begin(512);
  
  Serial.println("");  
}

void loop() {
  restorSetting();
  if (!checkConnection()) {
    settingMode();
    while (true){ 
      Serial.println("CONECTION SETTING LOOP");
      server.handleClient();
    }
  } else {
    httpRequest();
    sleep();
  }
}


//webpages
void handleRoot() {
  String s = "<!DOCTYPE html><html><head>";
  s += "<meta name=\"viewport\" content=\"width=device-width,user-scalable=0\">";
  s += "<title>";
  s += "Main";
  s += "</title>";
  s += "</head>";
  s += "<body>";
  s += "<a href=\"/restart\"><p>Restartovat</p><a/>";
  s += "<a href=\"/setting\"><p>Refrest WIFI Networks</p><a/>";
  s += "</body>";
  s += "</html>";
  server.send(200, "text/html", s);
}

void handleSetting() {
  String wifiList = "";
  int n = WiFi.scanNetworks();
  Serial.println("scan done");
  if (n == 0) {
    Serial.println("no networks found");
  } else {
    for (int i = 0; i < n; ++i) {
      //Serial.println("SSID: " + WiFi.SSID(i));
      wifiList = wifiList + String("<option value=\"");
      wifiList = wifiList + String(WiFi.SSID(i));
      wifiList = wifiList + String("\">");
      wifiList = wifiList + String(WiFi.SSID(i) + "(" + WiFi.RSSI() + ")");
      wifiList = wifiList + String("</option>");
    }
  }
  
  String s = "<!DOCTYPE html><html><head>";
  s += "<meta name=\"viewport\" content=\"width=device-width,user-scalable=0\">";
  s += "<title>";
  s += "Main";
  s += "</title>";
  s += "</head>";
  s += "<body>";
  s += "<form method=\"get\" action=\"set\">";
  s += "<br>SSID: <select name=\"ssid\">";
  s += wifiList;
  s += "</select>";
  s += "<br>Password: <input name=\"pasw\" length=64 type=\"password\">";
  s += "<br>Token: <input name=\"token\" length=64 type=\"text\">";
  s += "<br>Api Url: <input name=\"url\" length=64 type=\"url\">";
  s += "<input type=\"submit\">";
  s += "</form>";
  s += "</body>";
  s += "</html>";
  server.send(200, "text/html", s);
}

//functions
bool restorSetting() {
  Serial.println("Reading EEPROM");
  ssid = "";
  pasw = "";
  hwId = "";
  url = "";
  if (EEPROM.read(0) != 0) {
    Serial.println("Reading EEPROM");
    for (int i = 0; i < 64; ++i) {
      ssid += char(EEPROM.read(i));
    }
    Serial.println("SSID: " + String(ssid));
    for (int i = 64; i < 128; ++i) {
      pasw += char(EEPROM.read(i));
    }
    Serial.println("PASS: " + String(pasw));
    for (int i = 128; i < 192; ++i) {
      hwId += char(EEPROM.read(i)); 
    }
    Serial.println("TOKEN: " + String(hwId));
    for (int i = 192; i < 256; ++i) {
      url += char(EEPROM.read(i));
    }
    Serial.println("URL: " + String(url));
    return true;
  } else {
    return false;
  }
}

String urlDecode(String input) {
  String s = input;
  s.replace("%20", " ");
  s.replace("+", " ");
  s.replace("%21", "!");
  s.replace("%22", "\"");
  s.replace("%23", "#");
  s.replace("%24", "$");
  s.replace("%25", "%");
  s.replace("%26", "&");
  s.replace("%27", "\'");
  s.replace("%28", "(");
  s.replace("%29", ")");
  s.replace("%30", "*");
  s.replace("%31", "+");
  s.replace("%2C", ",");
  s.replace("%2E", ".");
  s.replace("%2F", "/");
  s.replace("%2C", ",");
  s.replace("%3A", ":");
  s.replace("%3A", ";");
  s.replace("%3C", "<");
  s.replace("%3D", "=");
  s.replace("%3E", ">");
  s.replace("%3F", "?");
  s.replace("%40", "@");
  s.replace("%5B", "[");
  s.replace("%5C", "\\");
  s.replace("%5D", "]");
  s.replace("%5E", "^");
  s.replace("%5F", "-");
  s.replace("%60", "`");
  return s;
}

bool checkConnection() {
  int count = 0;
  WiFi.persistent(false);
  WiFi.mode(WIFI_STA);
  WiFi.forceSleepWake();
  WiFi.begin(ssid, pasw);
  Serial.print("Waiting for Wi-Fi connection");
  while ( count < 30 ) {
    if (WiFi.status() == WL_CONNECTED) {
      Serial.println();
      Serial.println("Connected!");
      return (true);
    }
    delay(500);
    Serial.print(".");
    count++;
  }
  Serial.println("Timed out.");
  return false;
}

void settingMode(){
  WiFi.mode(WIFI_AP);          
  WiFi.softAP("NodeMeter"); 
  
  IPAddress myIP = WiFi.softAPIP(); 
  Serial.print("HotSpt IP:");
  Serial.println(myIP);
  
  server.on("/", handleRoot); 
  server.on("/restart", []() {
    ESP.restart();
  });
  server.on("/setting", handleSetting);
  server.on("/set", []() {
    for (int i = 0; i < 96; ++i) {
      EEPROM.write(i, 'e');
    }
    EEPROM.commit();
    ssid = server.arg("ssid");
    pasw = server.arg("pasw");
    hwId = server.arg("token");
    url = server.arg("url");
    
    for (int i = 0; i < 256; ++i) {
      EEPROM.write(i, 0);
    }
    
    Serial.println("Writing EEPROM...");
    Serial.println("SSID:" + ssid);
    for (int i = 0; i < ssid.length(); ++i) {
      EEPROM.write(i,ssid[i]);
    }
    
    Serial.println("PASW:" + pasw);
    for (int i = 0; i < pasw.length(); ++i) {
      EEPROM.write(64 + i, pasw[i]);
    }
    
    Serial.println("TOKEN:" + hwId);
    for (int i = 0; i < hwId.length(); ++i) {
      EEPROM.write(128 + i, hwId[i]);
    }
    
    Serial.println("URL:" + url);
    for (int i = 0; i < url.length(); ++i) {
      EEPROM.write(192 + i, url[i]);
    }
    EEPROM.commit();
    
    Serial.println("Write EEPROM done!");
    String s = "<h1>Setup complete.</h1><p>device will be connected to \"";
    s += ssid;
    s += "\" after the restart.";
    server.send(200, "text/html", s);
    ESP.restart();
  });
  
  server.begin();
  Serial.println("HTTP server started");
}

bool httpRequest(){
  StaticJsonDocument<1024> sendContent;
  sendContent["token"] = hwId;
  if (temperatureSensor){
    sendContent["values"]["temp"]["value"] = String(getTemperature());
    sendContent["values"]["temp"]["unit"] = "C";
  }
  
  if (humiditySensor){
    sendContent["values"]["humi"]["value"] = String(getHumidity());
    sendContent["values"]["humi"]["unit"] = "%";
  }
  
  if (lightSensor){
    sendContent["values"]["light"]["value"] = String(getLight());
    sendContent["values"]["light"]["unit"] = "";
  }
  
  String requestJson = "";
  serializeJson(sendContent, requestJson);
  Serial.println("JSON: " + requestJson);
  
  HTTPClient http;
  http.begin(String(url));
  http.addHeader("Content-Type", "application/json");  //Specify content-type header
  
  int httpCode = http.POST(requestJson);
  String payload = http.getString();  //Get the response payload
  http.end();
  
  DynamicJsonDocument doc(1024);
  deserializeJson(doc, payload);
  
  String hostname = doc["device"]["hostname"];
  sleepTime = doc["device"]["sleepTime"];
  WiFi.hostname(hostname);
  
  Serial.println("HTTP CODE: " + String(httpCode) + ""); //Print HTTP return code
  Serial.println("HTTP BODY: " + String(payload) + "");  //Print request response payload
  http.end();  //Close connection
}

void sleep(){
  Serial.println("DISCONECTED FROM WIFI");
  WiFi.disconnect();
  
  if (deepSleepOn) {
    Serial.println("GOING TO SLEEP FOR " + String(sleepTime));
    ESP.deepSleep((sleepTime * 60) * 1000000, RF_DEFAULT);  // 20e6 is 20 microseconds
    delay(1000);
  } else {
    delay(1000);
    delay(sleepTime);
  }
}

//ReadTemperatures
float getTemperature() {
  float t = dht.readTemperature();
  //Serial.print(dht.readTemperature());
  //Serial.println(t);
  if (isnan(t)) {
    Serial.println( "Failed to read temperature from sensor!") ;
    return 999;
  }
  return t;
}


float getHumidity() {
  float h = dht.readHumidity();
  //Serial.println(h);
  if (isnan(h)) {
    Serial.println("Failed to read humidity from sensor!");
    return 999;
  }
  return h;
}


int getLight() {
  int l = analogRead(LIGHTPIN);
  if (l > 1000) {
    return 1;
  } else {
    return 0;
  }
  Serial.println("Failed to read light from sensor!");
  return 999;
}
