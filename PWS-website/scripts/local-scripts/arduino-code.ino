#include <Servo.h>
// servo stand bijhouden
int huidigeServoHoek = -1;

// sensor input
#define ANALOG_INPUT A0
#define SERVO_PIN 9

// Mode knoppen
#define KNOP_UIT   2
#define KNOP_AAN   3
#define KNOP_AUTO  4

// Mode LEDs
#define LED_UIT   5
#define LED_AAN   6
#define LED_AUTO  7

Servo co2Servo;

// Modes
enum Modus {MODUS_UIT, MODUS_AAN, MODUS_AUTO};
Modus huidigeModus = MODUS_UIT;

// Variabelen voor automatische modus
int laatsteHoek = 0;
bool servoOmhoog = false;

void setup() {
  Serial.begin(9600);
  pinMode(ANALOG_INPUT, INPUT);

  // Knoppen instellen
  pinMode(KNOP_UIT, INPUT_PULLUP);
  pinMode(KNOP_AAN, INPUT_PULLUP);
  pinMode(KNOP_AUTO, INPUT_PULLUP);

  // LEDs instellen
  pinMode(LED_UIT, OUTPUT);
  pinMode(LED_AAN, OUTPUT);
  pinMode(LED_AUTO, OUTPUT);

  zetModus(MODUS_UIT);
}

// beweeg servo en stop PWM om vibratie te voorkomen
void beweegServoEnStop(int hoek) {
  co2Servo.attach(SERVO_PIN);    
  co2Servo.write(hoek);          
  delay(500);                    
  co2Servo.detach();             
  huidigeServoHoek = hoek;       
}

void loop() {
  // Kijken of er commando van de website komt
  if (Serial.available()) {
    char c = Serial.read();
    if (c == '0') zetModus(MODUS_UIT);
    if (c == '1') zetModus(MODUS_AAN);
    if (c == '2') zetModus(MODUS_AUTO);
  }

  // kijken of er commando van knoppen komt
  if (digitalRead(KNOP_UIT) == LOW) { zetModus(MODUS_UIT); delay(200); }
  if (digitalRead(KNOP_AAN) == LOW) { zetModus(MODUS_AAN); delay(200); }
  if (digitalRead(KNOP_AUTO) == LOW) { zetModus(MODUS_AUTO); delay(200); }

  // Servo besturen afhankelijk van input
  if (huidigeModus == MODUS_UIT) {
    if (huidigeServoHoek != 0) {
      beweegServoEnStop(0);
    }
  }
  else if (huidigeModus == MODUS_AAN) {
    if (huidigeServoHoek != 180) {
      beweegServoEnStop(180);
    }
  }
  // automatische modus
  else if (huidigeModus == MODUS_AUTO) {
    automatischeModus();
  }

  delay(100);
}

// Functie om modus te veranderen
void zetModus(Modus m) {
  huidigeModus = m;

  // LEDs aan of uit
  digitalWrite(LED_UIT,  m == MODUS_UIT);
  digitalWrite(LED_AAN,  m == MODUS_AAN);
  digitalWrite(LED_AUTO, m == MODUS_AUTO);

  Serial.print("AFZUIGING = ");
  if (m == MODUS_UIT)  Serial.println("UIT");
  if (m == MODUS_AAN)  Serial.println("AAN");
  if (m == MODUS_AUTO) Serial.println("AUTO");
}

// Automatische modus functie
void automatischeModus() {

  int meting = analogRead(ANALOG_INPUT);
  // linieaire functie werkte het best omdat we maar 2 meetpunten hadden
  int co2PPM = round(0.6408 * meting + 674.23);
  delay(1000);
  
  if (!servoOmhoog && co2PPM >= 950) {
    laatsteHoek = 180;
    servoOmhoog = true;
  } 
  else if (servoOmhoog && co2PPM <= 850) {
    laatsteHoek = 0;
    servoOmhoog = false;
  }

  // Servo alleen bewegen als de hoek echt veranderd
  if (laatsteHoek != huidigeServoHoek) {
    beweegServoEnStop(laatsteHoek);
  }

  // Waarde naar de seriële monitor zodat het op de website komt
  Serial.println(co2PPM);
}