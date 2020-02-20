//Includes
#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <EEPROM.h>

//Variables
const char* ssidServer = "ESPFilip";
const char* paswServer = "Sapatr6";
String ssid = "";
String pasw = "";
String apiToken = "";
const char* host = "http://dev.steelants.cz";
const char* url = "/vasek/home/api.php";

//NetworkData
// IPAddress staticIpAddress = "";
// IPAddress subnetIpAddress = "";
// IPAddress gatewayIpAddress = "";

String content;
bool conf = false;

ESP8266WebServer server(80);
StaticJsonDocument<250> jsonContent;

//Pins
#define RELAY 4 //12
#define SWITCH 5 //0

void setup() {
    Serial.begin(115200);
    EEPROM.begin(100);
    while (!Serial) continue;
    delay(10);
    //read saved data
    ssid = ReadEeprom(1,33);
    pasw = ReadEeprom(33,65);
    apiToken = ReadEeprom(65,97);

    //set pins
    pinMode(SWITCH, INPUT);
    pinMode(RELAY, OUTPUT);

    //wifi  
    if (ssid != "") {
      WiFi.persistent(false);
      WiFi.mode(WIFI_STA);
      #if defined(staticIpAddress) && defined(subnetIpAddress) && defined(gatewayIpAddress)
        WiFi.config(staticIpAddress, subnetIpAddress, gatewayIpAddress);
      #endif
      WiFi.begin(ssid, pasw);
      conf = wifiVerify(20);
      if (conf) {
        return;
      } 
    }
    setupAP();
}

void loop() {
  if (!conf) {
    server.handleClient();
  }
}

bool wifiVerify(int t){
  int c = 0;
  Serial.println("Waiting for Wifi to connect to Shelly1");  
  while (c < t) {
    if (WiFi.status() == WL_CONNECTED) { return true; } 
    delay(500);
    Serial.print(WiFi.status());    
    c++;
  }
  return false;
}

void CleanEeprom(){
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

String ReadEeprom(int min, int max){
  String localString;
  for(int i = min; i < max; ++i) {
    localString += char(EEPROM.read(i));
  }
  return localString;
}

void createWeb()
{
  server.on("/", []() {
    if (server.args() == 3){
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
    content += "<a href='#'>Refresh</a>";
    content += "<form method='get' action=''><div class='wifi-form'>";
    content += "<input name='wifi-ssid' length=32 type='text'><br>";
    content += "<input name='wifi-pasw' length=32 type='password'><br>";
    content += "<input name='apiToken' length=32 type='password'><br>";
    content += "<input type='submit' value='Connect'>";
    content += "</div></form>";
    content += "</body>";
    server.send(200, "text/html", content);  
  });
}

void setupAP(void) {
  WiFi.mode(WIFI_STA);
  WiFi.disconnect();
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
      Serial.println((WiFi.encryptionType(i) == ENC_TYPE_NONE)?" ":"*");
      delay(10);
     }
  }
  delay(100);
  WiFi.softAP(ssidServer);
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
