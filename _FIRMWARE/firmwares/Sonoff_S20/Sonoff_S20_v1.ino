//Includes
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

//Variables
const char* ssid = " ";
const char* pasw = "";
const char* server = "http://dev.steelants.cz/vasek/home/api.php";
const char* hwId = "";
int lastState = 0;
int reconectAtemptsMax = 10; //time to wait before restart

//Constant
#define SONOFF 12
#define SONOFF_LED 13
#define SONOFF_BUT 0


void setup() {
    Serial.begin(9600);
    delay(10);
    Serial.println('\n');
    Serial.println("HW: " + String(hwId));
    
    pinMode(SONOFF, OUTPUT);
    pinMode(SONOFF_LED, OUTPUT);
    pinMode(SONOFF_BUT, INPUT);
    
    WiFi.persistent(false);
    WiFi.mode(WIFI_STA);
    
    WiFi.begin(ssid, pasw);
    Serial.print("Connecting to ");
    Serial.print(ssid); Serial.println(" ...");
    
    int i = 0;
    while (WiFi.status() != WL_CONNECTED) { // Wait for the Wi-Fi to connect
        delay(1000);
        Serial.print(++i); Serial.print(' ');
    }
    
    Serial.println('\n');
    Serial.println("Connection established!");  
    Serial.print("IP address:\t");
    Serial.println(WiFi.localIP());   
}

void loop() {
    StaticJsonDocument<200> jsonContent;
    jsonContent["token"] = hwId;
    
    if (!digitalRead(SONOFF_BUT)){
        jsonContent["values"]["on/off"]["value"] = (int) !lastState;
        if (!lastState == 1) {
            digitalWrite(SONOFF, HIGH)
        } else if (!lastState == 0){
            digitalWrite(SONOFF, LOW)
        }
        while(!digitalRead(SONOFF_BUT)) {
            delay(100);
        }
    }
    
    String requestJson = "";
    serializeJson(jsonContent, requestJson);
    Serial.println("JSON: " + requestJson);
    
    HTTPClient http;
    http.begin(server);
    http.addHeader("Content-Type", "text/plain");  //Specify content-type header
    int httpCode = http.POST(requestJson);
    String payload = http.getString();  //Get the response payload
    http.end();
    
    Serial.println("HTTP CODE: " + String(httpCode) + ""); //Print HTTP return code
    Serial.println("HTTP BODY: " + String(payload) + "");  //Print request response payload
    
    deserializeJson(jsonContent, payload);
    String hostname = jsonContent["device"]["hostname"];
    int state = jsonContent["value"];
    WiFi.hostname(hostname);
    
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
