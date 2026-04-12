import serial
import serial.tools.list_ports
import time
from serial.serialutil import SerialException

BAUDRATE = 9600
OUTPUT_FILE = "sensor.txt"
ser = None

# vind de arduino op seriele poort
def find_arduino():
    for p in serial.tools.list_ports.comports():
        if "Arduino" in p.description or "ACM" in p.device:
            return p.device
    return None


while True:
    try:
        # kijkt of de arduino verbonden is
        if ser is None or not ser.is_open:
            port = find_arduino()
            if not port:
                with open(OUTPUT_FILE, "w") as f:
                    f.write("sensor niet verbonden\n")
                time.sleep(1)
                continue

            ser = serial.Serial(port, BAUDRATE, timeout=1)
            time.sleep(2) 

        # Kijkt voor de seriele waarde die de arduino geeft
        line = ser.readline().decode("utf-8", errors="ignore").strip()

        if line:
            try:
                # als het een integer is word dit gedaan (want co2 waarde)
                value = int(line)
                with open(OUTPUT_FILE, "w") as f:
                    f.write(f"Luchtkwaliteit: {value} ppm\n")
            except ValueError:
                # Als het geen integer is word hij direct doorgegeven
                with open(OUTPUT_FILE, "w") as f:
                    f.write(line + "\n")

    except SerialException:
        if ser:
            ser.close()
            ser = None
        time.sleep(1)
