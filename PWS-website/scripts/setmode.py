import serial
import serial.tools.list_ports
import time
import os

BAUDRATE = 9600
MODE_FILE = "../mode.txt"
ser = None
last_sent = None

def find_arduino():
    # zoekt arduino op seriele poort
    for p in serial.tools.list_ports.comports():
        if "Arduino" in p.description or "ACM" in p.device:
            return p.device
    return None

def connect_arduino():
    # probeert met de arduino te verbinden zonder te crashes als dat niet lukt
    global ser
    if ser is None or not ser.is_open:
        port = find_arduino()
        if port:
            try:
                ser = serial.Serial(port, BAUDRATE, timeout=1)
                # print om te weten of het werkt, zichtbaar in terminal
                print(f"Connected to Arduino on {port}")
                time.sleep(2)
            except serial.SerialException as e:
                print(f"Failed to connect to {port}: {e}")
                ser = None
        else:
            ser = None

def send_mode(mode):
    # Stuur een modus naar de arduino zonder te crashes als hij niet is aangesloten
    global ser
    if ser and ser.is_open:
        try:
            ser.write(str(mode).encode())
            ser.flush()
            # weer print om te testen, zichtbaar in terminal
            print(f"Sent {mode} to Arduino")
            time.sleep(0.1)
        except serial.SerialException as e:
            # weer print om te testen, zichtbaar in terminal
            print(f"Error writing to Arduino: {e}")
            ser.close()
            ser = None

while True:
    # Verbind met arduino
    connect_arduino()

    # direct modus sturen zodra hij verbonden is
    if os.path.exists(MODE_FILE):
        try:
            with open(MODE_FILE, "r") as f:
                mode = f.read().strip()
        except Exception as e:
            # weer print om te testen, zichtbaar in terminal
            print(f"Error reading mode file: {e}")
            mode = None

        if mode in ["0", "1", "2"]:
            if last_sent is None or mode != last_sent:
                send_mode(mode)
                last_sent = mode
        else:
            print(f"Invalid mode in file: {mode}")

    # Wacht kort voordat de loop opnieuw loopt
    time.sleep(0.5)
