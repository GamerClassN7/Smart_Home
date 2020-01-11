//Includes
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <EEPROM.h>

//Variables
const char* ssid = "ssid";
const char* pasw = "pasw";
const char* hwId = "hwId";
const char* server = "http://dev.steelants.cz/vasek/home/api.php";
int unsuccessfulRounds = 0; //Unsucesful atmpt counter
StaticJsonDocument<250> jsonContent;
bool buttonActive = false;
int interuptCount = 0;
int realState = 1;
int state = 0;
String requestJson = "";

//Pins
#define SONOFF 12 //12
#define SONOFF_LED 13
#define SONOFF_BUT 0 //0

void ICACHE_RAM_ATTR handleInterrupt ();

void setup() {
    Serial.begin(9600);
    EEPROM.begin(1);
    while (!Serial) continue;
    delay(10);
    Serial.println('\n');
    Serial.println("HW: " + String(hwId));

    pinMode(SONOFF_LED, OUTPUT);
    pinMode(SONOFF_BUT, INPUT_PULLUP);
    pinMode(SONOFF, OUTPUT);
    state = EEPROM.read(0);
    digitalWrite(SONOFF, state);
    realState = state;

    attachInterrupt(digitalPinToInterrupt(SONOFF_BUT), handleInterrupt, FALLING);


    WiFi.persistent(false);
    WiFi.mode(WIFI_STA);

    WiFi.begin(ssid, pasw);
    checkConnection();

    Serial.println('\n');
    Serial.println("Connection established!");
    Serial.print("IP address:");
    Serial.println(WiFi.localIP());
    Serial.print("MAC address:");
    Serial.println(WiFi.macAddress());
    
    jsonContent = {};
    jsonContent["token"] = hwId;
    jsonContent["values"]["on/off"]["value"] = (String)realState;
    
    serializeJson(jsonContent, requestJson);
    Serial.println("JSON: " + requestJson);
    DeserializationError error = deserializeJson(jsonContent, sendHttpRequest(requestJson));
}

void loop() {
    if(unsuccessfulRounds >= 5) {
      Serial.println("RESTARTING ESP");
      ESP.restart();
    }
    jsonContent = {};
    jsonContent["token"] = hwId;
    requestJson = "";
    if (buttonActive == true){
      jsonContent["values"]["on/off"]["value"] = (String)!realState;
      if (!realState == 1) {
        digitalWrite(SONOFF, HIGH);
        realState = 1;
      } else if (!realState == 0){
        digitalWrite(SONOFF, LOW);
        realState = 0;
      }
      EEPROM.write(0, 0);
      EEPROM.commit();
      serializeJson(jsonContent, requestJson);
      Serial.println("JSON: " + requestJson);

      //HTTP CLIENT
      DeserializationError error = deserializeJson(jsonContent, sendHttpRequest(requestJson));
      buttonActive = false;
    }
    jsonContent = {};
    jsonContent["token"] = hwId;
    
    requestJson = "";
    serializeJson(jsonContent, requestJson);
    Serial.println("JSON: " + requestJson);

    //HTTP CLIENT
    DeserializationError error = deserializeJson(jsonContent, sendHttpRequest(requestJson));
    
    if (error) {
      Serial.println(error.c_str());
      unsuccessfulRounds++;
      Serial.println("UNSUCCESSFUL ROUND NUMBER " + String(unsuccessfulRounds) + "FROM 5");
      return;
    }
    //configuration setup
    String hostName = jsonContent["device"]["hostname"];
    String ipAddress = jsonContent["device"]["ipAddress"];
    String gateway = jsonContent["device"]["gateway"];
    String subnet = jsonContent["device"]["subnet"];
    String requestState = jsonContent["state"];
    JsonObject object = jsonContent.as<JsonObject>();
    if (!object["value"].isNull()) {
      state = (int)jsonContent["value"];
    }

    if (requestState != "succes") {
        unsuccessfulRounds++;
        Serial.println("UNSUCCESSFUL ROUND NUMBER " + String(unsuccessfulRounds) + "FROM 5");
    } else if (requestState == "succes") {
        unsuccessfulRounds = 0;
    }

    //Set static ip
    setStaticIp(ipAddress, gateway, subnet);
    WiFi.hostname(hostName);
    Serial.println("state: " + (String)state + ", realState: " + (String)realState);
    if (state != realState){
        if (state == 1 && realState == 0) {
            Serial.println("ON state: " + (String)state + ", realState: " + (String)realState);
            digitalWrite(SONOFF, HIGH);   // Turn the LED on by making the voltage LOW
            realState = 1;
        } else if (state == 0 && realState == 1) {
            Serial.println("OFF");
            digitalWrite(SONOFF, LOW);   // Turn the LED on by making the voltage LOW
            realState = 0;
        }
        EEPROM.write(0, realState);
        EEPROM.commit();
    }
}

void handleInterrupt() {
    interuptCount++;
    buttonActive = true;
}

String sendHttpRequest (String requestJson) {
    HTTPClient http;
    http.setReuse(true);
    http.begin(server);
    http.addHeader("Content-Type", "text/plain");  //Specify content-type header

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

bool checkConnection() {
    int count = 0;
    Serial.print("Waiting for Wi-Fi connection");
    while ( count < 30 ) {
      if (buttonActive == true){
        if (!realState == 1) {
          digitalWrite(SONOFF, HIGH);
          realState = 1;
        } else if (!realState == 0){
          digitalWrite(SONOFF, LOW);
          realState = 0;
        }
        EEPROM.write(0, realState);
        EEPROM.commit();
        buttonActive = false;
      }
      digitalWrite(SONOFF_LED, HIGH);
      delay(125);
      digitalWrite(SONOFF_LED, LOW);
      delay(125);
      if (WiFi.status() == WL_CONNECTED) {
          Serial.println();
          Serial.println("Connected!");
          digitalWrite(SONOFF_LED, HIGH);
          return (true);
      }
      Serial.print(".");
      count++;
    }
    Serial.println("Timed out.");
    return false;
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
