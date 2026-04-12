#!/bin/bash

SESSION_NAME="read"
PYTHON_SCRIPT="read.py"

SESSION_NAME2="setmode"
PYTHON_SCRIPT2="setmode.py"

# Start een detached screen sessie met een echte bash shell
sudo -u pws screen -dmS $SESSION_NAME bash
# Wacht kort zodat screen klaar is
sleep 2
# Stuur Python commando naar de shell van screen
sudo -u pws screen -S $SESSION_NAME -X stuff "cd /home/pws/apache2/scripts \r"
sleep 2
sudo -u pws screen -S $SESSION_NAME -X stuff "python3 $PYTHON_SCRIPT\r"

# Start een detached screen sessie met een echte bash shell
sudo -u pws screen -dmS $SESSION_NAME2 bash
# Wacht kort zodat screen klaar is
sleep 2
# Stuur Python commando naar de shell van screen
sudo -u pws screen -S $SESSION_NAME2 -X stuff "cd /home/pws/apache2/scripts \r"
sleep 2
sudo -u pws screen -S $SESSION_NAME2 -X stuff "python3 $PYTHON_SCRIPT2\r"