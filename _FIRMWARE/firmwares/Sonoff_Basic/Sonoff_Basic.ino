//Includes
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

//Variables
const char* ssid = "";
const char* pasw = "";
const char* server = "http://ESP:ESP@dev.steelants.cz/projekty/rest_vasek/api/out.php";
const char* hwId = "";
int lastState = 0;

//Constant
#define SONOFF 12
#define SONOFF_LED 13
#define SONOFF_BUT 0

void setup() {
  Serial.begin(9600);
  Serial.println("HW: " + String(hwId));
  pinMode(SONOFF, OUTPUT);
  pinMode(SONOFF_LED, OUTPUT);
  pinMode(SONOFF_BUT, OUTPUT);
  pinMode(SONOFF_BUT, INPUT);
  // WI-FI CONECTING
  WiFi.persistent(false);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, pasw);
}

void loop() {
  if(WiFi.status() != WL_CONNECTED){
    WiFi.begin(ssid, pasw);
    while (WiFi.status() != WL_CONNECTED) { // Wait for the Wi-Fi to connect
      digitalWrite(SONOFF_LED, LOW); // LOW will turn on the LED
      delay(1000);
      digitalWrite(SONOFF_LED, HIGH); // HIGH will turn off the LED
      delay(1000);
    }
    Serial.println("\nCONECTED TO WIFI");
    Serial.println("IP: " + String(WiFi.localIP()));
  }
  
  bool buttonState = digitalRead(SONOFF_BUT);
  HTTPClient http;
  http.begin(server);
  http.addHeader("Content-Type", "text/plain");  //Specify content-type header
  String requestJson = "{";
  requestJson += "\"tocken\":\"" + String(hwId) + "\"";
  
  if (buttonState == true) {
    requestJson += ",";
    requestJson += "\"on/off\":{";
    requestJson += "\"value\":\"" + String(~lastState) + "\",";
    requestJson += "\"unit\":\"\"";
    requestJson += "}";
  }
  
  while(buttonState == true) {
    delay(50); // keeps a small delay
  }
  
  requestJson += "}";
  Serial.println("JSON: " + requestJson);
  
  int httpCode = http.POST(requestJson);
  String payload = http.getString();  //Get the response payload
  
  Serial.println("HTTP CODE: " + String(httpCode) + ""); //Print HTTP return code
  Serial.println("HTTP BODY: " + String(payload) + "");  //Print request response payload
  
  DynamicJsonDocument doc(1024);
  deserializeJson(doc, payload);
  
  string hostname = doc["device"]["hostname"];
  sleepTime = doc["device"]["sleepTime"];
  
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