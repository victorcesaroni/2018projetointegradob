#include <BLEDevice.h>
#include <BLEServer.h>
#include <BLEUtils.h>
#include <BLE2902.h>
#include <WiFi.h>
#include <HTTPClient.h>

std::vector<std::string> loginQueue;

const char* ssid = " ";
const char* password =  " ";
const char* doorName = "ESP32-PORTA1";
const char* serverIp = "192.168.1.200";

BLEServer *server;

bool deviceConnected = false; //controle de dispositivo conectado

// https://www.uuidgenerator.net/
#define SERVICE_UUID              "ab0828b1-198e-4351-b779-901fa0e0371e" // UART service UUID
#define CHARACTERISTIC_UUID_LOGIN "03d5b556-7940-4692-bc80-d5027539b024"

//callback para receber os eventos de conexão de dispositivos
class ServerCallbacks: public BLEServerCallbacks {
    void onConnect(BLEServer* pServer) {
      deviceConnected = true;
      Serial.println("Device connected");
    };
    void onDisconnect(BLEServer* pServer) {
      deviceConnected = false;
      Serial.println("Device disconnected");
    }
};

class CharacteristicLogin : public BLECharacteristicCallbacks {
    void onWrite(BLECharacteristic *characteristic) {
      std::string rxValue = characteristic->getValue();
      if (rxValue.length() > 0) {
        Serial.print("[LOGIN] ");
        Serial.println(rxValue.c_str());
        loginQueue.push_back(rxValue);
      }
    }
};

void wifiConnect() {
  Serial.print("Conetando ao WiFi");
  WiFi.enableSTA(true);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    Serial.print(".");
    delay(500);
  }
  Serial.print(" ");
  Serial.print(WiFi.localIP());
  Serial.println(" OK");
}

void wifiDisconnect() {
  Serial.print("Desconectando do WiFi...");
  WiFi.disconnect(true);
  WiFi.mode(WIFI_OFF);
  Serial.println(" OK");
}

void sendHttpRequest(std::string info) {
  HTTPClient http;
  
  std::string url = "http://";
  url += std::string(serverIp);
  url += "/2018projetointegradob/api/autoriza.php?";
  url += "porta=" + std::string(doorName) + "&";
  url += info;    
  Serial.println(url.c_str());
  
  http.begin(url.c_str());
  int httpCode = http.GET();
  if (httpCode > 0) {
    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      Serial.println(payload);

      if(payload.indexOf("OK") > 0) {
        digitalWrite(2, HIGH);
      }
    }
  } else {
    Serial.printf("[HTTP] GET... failed, error: %s\n", http.errorToString(httpCode).c_str());
  }
  http.end();
}

void bleSetup() {
  Serial.print("Preparando BLE...");
  BLEDevice::init(doorName);

  server = BLEDevice::createServer();
  server->setCallbacks(new ServerCallbacks());

  BLEService *service = server->createService(SERVICE_UUID);
  BLECharacteristic *characteristic = service->createCharacteristic(CHARACTERISTIC_UUID_LOGIN, BLECharacteristic::PROPERTY_WRITE);

  characteristic->setCallbacks(new CharacteristicLogin());

  service->start();
  server->getAdvertising()->start();
  Serial.println(" OK");
}

void setup() {
  Serial.begin(9600);
  pinMode(2, OUTPUT);
  digitalWrite(2, LOW);
  bleSetup();
}

void loop() {
  if (!loginQueue.empty()) {
    wifiConnect();
    while (!loginQueue.empty()) {
      sendHttpRequest(loginQueue.back());
      loginQueue.pop_back();
    }
    wifiDisconnect();
    Serial.println("Restarting");
    delay(5000);
    esp_restart();
  }
  delay(1000);
}
