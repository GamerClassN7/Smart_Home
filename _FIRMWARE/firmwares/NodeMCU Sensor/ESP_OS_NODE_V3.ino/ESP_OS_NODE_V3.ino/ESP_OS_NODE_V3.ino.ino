#include <DHT.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

//Variables
const char* ssid   = "";
const char* pasw   = "";
const char* hwId   = "";
const char* url    = "http://dev.steelants.cz/vasek/home/api.php";

//Pins
#define pinDHT 4
#define LIGHTPIN A0

//Inicializations
DHT DHTs(pinDHT, DHT11);

void setup() {
  Serial.begin(9600);
  while (!Serial) continue;
  pinMode(LIGHTPIN, INPUT);
}

void loop() {
  WiFi.begin(ssid, pasw);
  checkConnection();

  //HTTP CLIENT
  HTTPClient http;
  http.begin(url);
  http.addHeader("Content-Type", "text/plain");  //Specify content-type header
  
  DHTs.begin();

  //JsonDocsDefinition
  StaticJsonDocument<265> doc;
  doc["token"] = hwId;
  
  float tem = DHTs.readTemperature();
  float hum = DHTs.readHumidity();
  Serial.println("TEMP" + String(tem) + ";HUMI" + String(hum));
  if (isnan(tem) || isnan(hum)) {
    Serial.println("Unable to read DHT");
  } else {
    doc["values"]["temp"]["value"] = tem;
    doc["values"]["temp"]["unit"] = "C";
    doc["values"]["humi"]["value"] = hum;
    doc["values"]["humi"]["unit"] = "%";
  }

  doc["values"]["light"]["value"] = analogRead(LIGHTPIN);
  doc["values"]["light"]["unit"] = "";

  /*More Senzores to come*/
  String jsonPayload = "";
  serializeJson(doc, jsonPayload);
  Serial.print("JSON: ");
  Serial.println(jsonPayload);

  int httpCode = http.POST(jsonPayload);
  String httpPayload = http.getString();  //Get the response payload
  Serial.println("HTTP CODE: " + String(httpCode) + ""); //Print HTTP return code
  Serial.println("HTTP BODY: " + String(httpPayload) + "");  //Print request response payload

  DeserializationError error = deserializeJson(doc, httpPayload);

  //configuration setup
  String hostName = doc["device"]["hostname"];
  int sleepTime = doc["device"]["sleepTime"];
  WiFi.hostname(hostName);

  http.end();  //Close connection
  Serial.println("DISCONECTED FROM WIFI");
  WiFi.disconnect();

  Serial.println("GOING TO SLEEP FOR " + String(sleepTime));
  if (sleepTime > 0) {
    ESP.deepSleep((sleepTime * 60) * 1000000, RF_DEFAULT);
  } else {
    delay(5000);
  }
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
    delay(500);
    Serial.print(".");
    count++;
  }
  Serial.println("Timed out.");
  return false;
}
