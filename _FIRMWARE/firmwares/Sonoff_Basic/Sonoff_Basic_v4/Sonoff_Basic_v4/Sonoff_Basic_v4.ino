//Includes
#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include <WiFiClientSecure.h>
#include <ESP8266HTTPClient.h>
#define ARDUINOJSON_DECODE_UNICODE 1
#include <ArduinoJson.h>
#include <EEPROM.h>
#include "ESP8266httpUpdate.h"

//Variables
const char* ssidServer = "";
const char* paswServer = "";
String ssid = "";
String pasw = "";
String apiToken = "";
const int httpsPort = 443;
const char* host = "http://dev.steelants.cz";
const char* url = "/vasek/home/api.php";

const char* fingerprint = "";
const char* host2 = "dev.steelants.cz";
const char* url2 = "/vasek/home/update.php";

String content;
bool conf = false;
bool buttonActive = false;
int state = 0;
String requestJson = "";
int unsuccessfulRounds = 0; //Unsucesful atmpt counter

ESP8266WebServer server(80);
StaticJsonDocument<250> jsonContent;
DeserializationError error;

//Pins
#define SONOFF 12
#define SONOFF_LED 13
#define SONOFF_BUT 0 //0

void ICACHE_RAM_ATTR handleInterrupt ();

void setup() {
  Serial.begin(9600);
  EEPROM.begin(100);
  while (!Serial) continue;
  delay(10);
  
  //read saved data
  ssid = ReadEeprom(1, 33);
  pasw = ReadEeprom(33, 65);
  apiToken = ReadEeprom(65, 97);
  
  //set pins
  pinMode(SONOFF_LED, OUTPUT);
  pinMode(SONOFF_BUT, INPUT_PULLUP);
  pinMode(SONOFF, OUTPUT);
  state = EEPROM.read(0);
  digitalWrite(SONOFF, state);
  
  attachInterrupt(digitalPinToInterrupt(SONOFF_BUT), handleInterrupt, FALLING);
  
  //wifi
  if (ssid != "") {
    WiFi.disconnect();
    WiFi.softAPdisconnect(true);
    WiFi.persistent(false);
    WiFi.mode(WIFI_STA);
    WiFi.begin(ssid, pasw);
    conf = wifiVerify(20);
    if (conf) {
      configTime(3 * 3600, 0, "pool.ntp.org");
      WiFiClientSecure client;
      Serial.print("connecting to ");
      Serial.println(host2);
      client.setInsecure();
      if (!client.connect(host2, httpsPort)) {
        Serial.println("connection failed");
        return;
      }
    
      if (client.verify(fingerprint, host2)) {
        Serial.println("certificate matches");
      } else {
        Serial.println("certificate doesn't match");
        return;
      }

      Serial.print("Starting OTA from: ");
      Serial.println(url2
      
      );
  
      auto ret = ESPhttpUpdate.update(client, host2, 80, url2);
      delay(500);
      switch(ret) {
          case HTTP_UPDATE_FAILED:
              Serial.printf("HTTP_UPDATE_FAILD Error (%d): %s", ESPhttpUpdate.getLastError(), ESPhttpUpdate.getLastErrorString().c_str());
              Serial.println();
              Serial.println();
              Serial.println();
              break;
  
          case HTTP_UPDATE_NO_UPDATES:
              Serial.println("HTTP_UPDATE_NO_UPDATES");
              Serial.println();
              Serial.println();
              break;
  
          case HTTP_UPDATE_OK:
              Serial.println("HTTP_UPDATE_OK");
              Serial.println();
              Serial.println();
              Serial.println();
              break;
      }
      delay(500);
      jsonContent = {};
      jsonContent["token"] = apiToken;
      jsonContent["values"]["on/off"]["value"] = (String)state;
      jsonContent["settings"]["network"]["ip"] = WiFi.localIP().toString();
      jsonContent["settings"]["network"]["mac"] = WiFi.macAddress();
      jsonContent["settings"]["firmware_hash"] = ESP.getSketchMD5();
      sendDataToWeb();
      return;
    }
  }
  setupAP();
}

void loop() {
  if (conf) {
    if (unsuccessfulRounds >= 5) {
      Serial.println("RESTARTING ESP");
      ESP.restart();
    }
    jsonContent = {};
    jsonContent["token"] = apiToken;
    requestJson = "";
    if (buttonActive) {
      jsonContent["values"]["on/off"]["value"] = (String)state;
      digitalWrite(SONOFF, state);
      EEPROM.write(0, state);
      EEPROM.commit();
      sendDataToWeb();
      buttonActive = false;
      delay(500);
    } else {
      sendDataToWeb();
      loadDataFromWeb();
    }
  } else {
    server.handleClient();
  }
}

void handleInterrupt() {
  buttonActive = true;
  state = !state;
  digitalWrite(SONOFF, state);
}

bool wifiVerify(int t) {
  int c = 0;
  Serial.println("Waiting for Wifi to connect to Shelly1");
  while (c < t) {
    if (WiFi.status() == WL_CONNECTED) {
      c = t;
      Serial.println();
      Serial.println("Connected!");
      digitalWrite(SONOFF_LED, HIGH);
      return true;
    }
    if (buttonActive == true){
      digitalWrite(SONOFF, state);
      EEPROM.write(0, state);
      EEPROM.commit();
      buttonActive = false;
    }
    digitalWrite(SONOFF_LED, HIGH);
    delay(125);
    digitalWrite(SONOFF_LED, LOW);
    delay(375);
    Serial.print(WiFi.status());
    c++;
  }
  return false;
}

void loadDataFromWeb() {
  if (error.code() != DeserializationError::Ok) {
    Serial.println(error.c_str());
    unsuccessfulRounds++;
    Serial.println("UNSUCCESSFUL ROUND NUMBER " + String(unsuccessfulRounds) + "FROM 5");
    error = DeserializationError::Ok;
    return;
  }

  //configuration setup
  String hostName = jsonContent["device"]["hostname"];
  String requestState = jsonContent["state"];
  if (!buttonActive) {
    state = (int)jsonContent["value"];
    Serial.println("state: " + (String)state);
    digitalWrite(SONOFF, state);
    EEPROM.write(0, state);
    EEPROM.commit();
    delay(500);
  }

  if (requestState != "succes") {
    unsuccessfulRounds++;
    Serial.println("UNSUCCESSFUL ROUND NUMBER " + String(unsuccessfulRounds) + "FROM 5");
  } else if (requestState == "succes") {
    unsuccessfulRounds = 0;
  }

  WiFi.hostname(hostName);
}

void sendDataToWeb() {
  serializeJson(jsonContent, requestJson);
  Serial.println("JSON: " + requestJson);
  error = deserializeJson(jsonContent, sendHttpRequest());
}

String sendHttpRequest () {
  HTTPClient http;
  http.setReuse(true);
  Serial.println("HTTP url: " + String(host) + String(url) + ""); //Print HTTP return code
  http.begin(String(host) + String(url));
  http.addHeader("Content-Type", "text/plain");  //Specify content-type header
  Serial.println("HTTP request: " + String(requestJson) + ""); //Print HTTP return code
  int httpCode = http.POST(requestJson);
  String payload = http.getString();  //Get the response payload
  http.end();

  Serial.println("HTTP CODE: " + String(httpCode) + ""); //Print HTTP return code
  Serial.println("HTTP BODY: " + String(payload) + "");  //Print request response payload

  if (httpCode == -1) {
    unsuccessfulRounds++;
    Serial.println("UNSUCCESSFUL ROUND NUMBER " + String(unsuccessfulRounds) + "FROM 5");
    return "";
  }
  return payload;
}

void CleanEeprom() {
  for (int i = 1; i < 100; ++i) {
    EEPROM.write(i, 0);
  }
}

void WriteEeprom (String data, int start = 1) {
  for (int i = 0; i < data.length(); ++i)
  {
    EEPROM.write(start + i, data[i]);
  }
  EEPROM.commit();
}

String ReadEeprom(int min, int max) {
  String localString;
  for (int i = min; i < max; ++i) {
    localString += char(EEPROM.read(i));
  }
  return localString;
}

void createWeb()
{
  server.on("/", []() {
    if (server.args() == 3) {
      ssid = server.arg("wifi-ssid");
      pasw = server.arg("wifi-pasw");
      apiToken = server.arg("apiToken");
      if (ssid != "" && pasw != "" && apiToken != "") {
        CleanEeprom();
        WriteEeprom(ssid);
        WriteEeprom(pasw, 33);
        WriteEeprom(apiToken, 65);
        server.send(200, "application/json", "Restarting esp");
        delay(500);
        ESP.restart();
      }
    }
    content = "<!DOCTYPE HTML><body>";
    content += "<head><style>";
    content += "html,body {height: 100%;}";
    content += "html {display: table;margin: auto;}";
    content += "body {display: table-cell;vertical-align: middle;}";
    content += "input {width: 100%;box-sizing: border-box}";
    content += "</style></head>";
    content += "<h2>WIFI Configuration</h2>";
    content += "<h4><b>" + (String)ssidServer + "</b></h4>";
    content += "<a href='#'>Refresh</a>";
    content += "<div class=\"wifi-list\">";
    int n = WiFi.scanNetworks();
    if (n == 0)
      content += "<label>No networks found...</label>";
    else
    {
      for (int i = 0; i < n; ++i)
      {
        content += "<a href=\"#\" onclick=\"fillSSID(this.innerHTML)\">" + WiFi.SSID(i) + "</a><br>";
      }
    }
    content += "</div>";
    content += "<form method='get' action=''><div class='wifi-form'>";
    content += "<label>SSID: </label><input name='wifi-ssid' id='wifi-ssid' length=32 type='text'><br>";
    content += "<label>Heslo: </label><input name='wifi-pasw' length=32 type='password'><br>";
    content += "<label>Api token: </label><input name='apiToken' length=32 type='password'><br>";
    content += "<input type='submit' value='Connect'>";
    content += "</div></form>";
    content += "<script>";
    content += "function fillSSID(value) {\r\n";
    content += "document.getElementById(\"wifi-ssid\").value = value;\r\n";
    content += "}";
    content += "</script>";
    content += "</body>";
    server.send(200, "text/html", content);
  });
}

void setupAP(void) {
  WiFi.mode(WIFI_STA);
  WiFi.disconnect();
  WiFi.softAPdisconnect(true);
  delay(100);
  int n = WiFi.scanNetworks();
  Serial.println("scan done");
  if (n == 0)
    Serial.println("no networks found");
  else
  {
    Serial.print(n);
    Serial.println(" networks found");
    for (int i = 0; i < n; ++i)
    {
      // Print SSID and RSSI for each network found
      Serial.print(i + 1);
      Serial.print(": ");
      Serial.print(WiFi.SSID(i));
      Serial.print(" (");
      Serial.print(WiFi.RSSI(i));
      Serial.print(")");
      Serial.println((WiFi.encryptionType(i) == ENC_TYPE_NONE) ? " " : "*");
      delay(10);
    }
  }
  delay(100);
  WiFi.softAP(ssidServer, paswServer);
  Serial.println("softap");
  Serial.println("");
  Serial.println("WiFi connected");
  Serial.print("Local IP: ");
  Serial.println(WiFi.localIP());
  Serial.print("SoftAP IP: ");
  Serial.println(WiFi.softAPIP());
  createWeb();
  // Start the server
  server.begin();
  Serial.println("Server started");
  Serial.println("over");
}