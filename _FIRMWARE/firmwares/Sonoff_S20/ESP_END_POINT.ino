//Includes
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

//Variables
const char* ssid = "";
const char* pasw = "";
const char* server = "httpa://dev.steelants.cz/vasek/home/api.php";
const char* hwId = "";
int lastState = 0;
int reconectAtemptsMax = 10; //time to wait before restart

//Constant
#define SONOFF 12
#define SONOFF_LED 13
#define SONOFF_BUT 0
HTTPClient http;

void setup() {
    Serial.begin(9600);
    Serial.println("HW: " + String(hwId));
    pinMode(SONOFF, OUTPUT);
    pinMode(SONOFF_LED, OUTPUT);
    pinMode(SONOFF_BUT, OUTPUT);
    pinMode(SONOFF_BUT, INPUT);
    
    WiFi.persistent(false);
    WiFi.mode(WIFI_STA);
    
    WiFi.setAutoConnect (true);
    WiFi.setAutoReconnect (true);
    
    WiFi.begin(ssid, pasw);
    http.begin(server);
}

void loop() {
    int reconectAtempts = 0;
    while(WiFi.status() != WL_CONNECTED){
        if (reconectAtemptsMax == reconectAtempts) {
            ESP.restart();
        }
        WiFi.begin(ssid, pasw);
        delay(1000);
    }
    
    bool buttonState = digitalRead(SONOFF_BUT);
    
    http.addHeader("Content-Type", "text/plain");  //Specify content-type header
    
    StaticJsonBuffer<1024> jsonContent;
    jsonContent["token"] = hwId;
    
    if (buttonState){
        jsonContent["values"]["on/off"]["value"] = lastState;
        jsonContent["values"]["on/off"]["unit"] = '';
        while(buttonState) {
            delay(100);
        }
    }
    
    String requestJson = "";
    serializeJson(jsonContent, requestJson);
    jsonContent.clean();
    Serial.println("JSON: " + requestJson);
    
    int httpCode = http.POST(jsonContent);
    String payload = http.getString();  //Get the response payload
    
    Serial.println("HTTP CODE: " + String(httpCode) + ""); //Print HTTP return code
    Serial.println("HTTP BODY: " + String(payload) + "");  //Print request response payload
    
    deserializeJson(doc, payload);
    
    string hostname = doc["device"]["hostname"];
    
    WiFi.hostname(hostname);
    
    int state = doc["state"];
    
    if (state == 1 && lastState == 0) {
        Serial.println("ON");
        digitalWrite(SONOFF, HIGH);   // Turn the LED on by making the voltage LOW
        digitalWrite(SONOFF_LED, LOW);   // Turn the LED on by making the voltage LOW
    } else {
        Serial.println("OFF");
        digitalWrite(SONOFF, LOW);   // Turn the LED on by making the voltage LOW
        digitalWrite(SONOFF_LED, HIGH);   // Turn the LED on by making the voltage LOW
    }
    
    lastState = state;
    delay(1000);
}