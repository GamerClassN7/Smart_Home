//Includes
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

//Variables
const char* ssid = "";
const char* pasw = "";
const char* hwId = "";
const char* server = "http://dev.steelants.cz/vasek/home/api.php";
int unsuccessfulRounds = 0; //Unsucesful atmpt counter
int lastState = 0;

//Pins
#define SONOFF 12
#define SONOFF_LED 13
#define SONOFF_BUT 0

void setup() {
    Serial.begin(9600);
    while (!Serial) continue;
    delay(10);
    Serial.println('\n');
    Serial.println("HW: " + String(hwId));
    
    pinMode(SONOFF, OUTPUT);
    pinMode(SONOFF_LED, OUTPUT);
    pinMode(SONOFF_BUT, INPUT);
    
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
}

void loop() {
    StaticJsonDocument<250> jsonContent;
    jsonContent["token"] = hwId;
    
   if (!digitalRead(SONOFF_BUT)){
        jsonContent["values"]["on/off"]["value"] = (int) !lastState;
        if (!lastState == 1) {
            digitalWrite(SONOFF, HIGH);
        } else if (!lastState == 0){
            digitalWrite(SONOFF, LOW);
        }
        while(!digitalRead(SONOFF_BUT)) {
            delay(100);
        }
    }
    
    String requestJson = "";
    serializeJson(jsonContent, requestJson);
    Serial.println("JSON: " + requestJson);
    
    //HTTP CLIENT
    HTTPClient http;
    http.begin(server);
    http.addHeader("Content-Type", "text/plain");  //Specify content-type header
    
    int httpCode = http.POST(requestJson);
    String payload = http.getString();  //Get the response payload
    http.end();
    
    Serial.println("HTTP CODE: " + String(httpCode) + ""); //Print HTTP return code
    Serial.println("HTTP BODY: " + String(payload) + "");  //Print request response payload
    
    DeserializationError error =  deserializeJson(jsonContent, payload);

    //configuration setup
    String hostName = jsonContent["device"]["hostname"];
    String ipAddress = jsonContent["device"]["ipAddress"];
    String gateway = jsonContent["device"]["gateway"];
    String subnet = jsonContent["device"]["subnet"];
    String requestState = jsonContent["state"];
    int state = jsonContent["value"];
    
    if (requestState != "succes") {
        unsuccessfulRounds++;
        Serial.println("UNSUCCESSFUL ROUND NUMBER " + String(unsuccessfulRounds) + "FROM 5");
    } else if (requestState == "succes") {
        unsuccessfulRounds = 0;
    }

    //Set static ip
    setStaticIp(ipAddress, gateway, subnet);
    WiFi.hostname(hostName);
    
    if(unsuccessfulRounds == 5) {
        Serial.println("RESTARTING ESP");
        ESP.restart();
    }
    
    if (state !=  lastState){
        if (state == 1 && lastState == 0) {
            Serial.println("ON");
            digitalWrite(SONOFF, HIGH);   // Turn the LED on by making the voltage LOW
        } else {
            Serial.println("OFF");
            digitalWrite(SONOFF, LOW);   // Turn the LED on by making the voltage LOW
        }
    }
    lastState = state;
}

bool checkConnection() {
    int count = 0;
    Serial.print("Waiting for Wi-Fi connection");
    while ( count < 30 ) {
        if (WiFi.status() == WL_CONNECTED) {
            Serial.println();
            Serial.println("Connected!");
            return (true);
        }
        digitalWrite(SONOFF, HIGH);
        delay(250);
        digitalWrite(SONOFF, HIGH);
        delay(250);
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
