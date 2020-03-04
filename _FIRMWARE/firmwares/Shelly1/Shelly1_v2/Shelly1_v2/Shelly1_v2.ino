//Includes
#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <EEPROM.h>

//Variables
const char* ssidServer = "";
const char* paswServer = "";
String ssid = "";
String pasw = "";
String apiToken = "";
const char* host = "http://dev.steelants.cz";
const char* url = "/vasek/home/api.php";

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
#define RELAY 4 //12
#define SWITCH 5 //0

void setup() {
  Serial.begin(115200);
  EEPROM.begin(100);
  while (!Serial) continue;
  delay(10);
  //read saved data
  ssid = ReadEeprom(1, 33);
  pasw = ReadEeprom(33, 65);
  apiToken = ReadEeprom(65, 97);
  //set pins
  pinMode(SWITCH, INPUT);
  pinMode(RELAY, OUTPUT);
  state = EEPROM.read(0);
  digitalWrite(RELAY, state);
  detachInterrupt(digitalPinToInterrupt(SWITCH));
  attachInterrupt(digitalPinToInterrupt(SWITCH), handleInterrupt, CHANGE);
  //wifi
  if (ssid != "") {
    WiFi.persistent(false);
    WiFi.mode(WIFI_STA);
    WiFi.begin(ssid, pasw);
    conf = wifiVerify(20);
    if (conf) {
      Serial.println(WiFi.localIP());
      jsonContent = {};
      jsonContent["token"] = apiToken;
      jsonContent["values"]["on/off"]["value"] = (int)state;
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
    if (buttonActive) {
<<<<<<< HEAD
=======
      realState = !state;
>>>>>>> 9d9bdc192f48b909e5406167be3e532d9b5d07e5
      jsonContent = {};
      jsonContent["token"] = apiToken;
      requestJson = "";
      jsonContent["values"]["on/off"]["value"] = (int)state;
      digitalWrite(RELAY, state);
      EEPROM.write(0, state);
      EEPROM.commit();
      sendDataToWeb();
      buttonActive = false;
    } else {
      loadDataFromWeb();
    }
  } else {
    server.handleClient();
  }
}

void handleInterrupt() {
  buttonActive = true;
  state = !state;
  digitalWrite(RELAY, state);
}

bool wifiVerify(int t) {
  int c = 0;
  Serial.println("Waiting for Wifi to connect to Shelly1");
  while (c < t) {
    if (WiFi.status() == WL_CONNECTED) {
      c = t;
      return true;
    }
    delay(500);
    Serial.print(WiFi.status());
    c++;
  }
  return false;
}

void loadDataFromWeb() {
  delay(500);
  jsonContent = {};
  jsonContent["token"] = apiToken;
  requestJson = "";
  sendDataToWeb();

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

  WiFi.hostname(hostName);
<<<<<<< HEAD
  Serial.println("state: " + (String)state;
  if (!buttonActive) {
    if (state == 1) {
      Serial.println("ON");
    } else if (state == 0) {
      Serial.println("OFF");
    }
    digitalWrite(RELAY, state);
    EEPROM.write(0, state);
=======
  Serial.println("state: " + (String)state + ", realState: " + (String)realState);
  if (state != realState && !buttonActive) {
    realState = state;
    digitalWrite(RELAY, realState);
    EEPROM.write(0, realState);
>>>>>>> 9d9bdc192f48b909e5406167be3e532d9b5d07e5
    EEPROM.commit();
  } else {
    state = realState;
  }
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
