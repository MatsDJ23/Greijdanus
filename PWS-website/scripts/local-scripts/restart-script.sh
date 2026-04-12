#!/bin/bash

SESSION_NAME="read"
PYTHON_SCRIPT="read.py"

SESSION_NAME2="setmode"
PYTHON_SCRIPT2="setmode.py"

# Stuur Python commando naar de shell van screen
sudo -u pws screen -S $SESSION_NAME -X stuff $'\003'
sleep 2
sudo -u pws screen -S $SESSION_NAME -X stuff "python3 $PYTHON_SCRIPT\r"

# Stuur Python commando naar de shell van screen
sudo -u pws screen -S $SESSION_NAME2 -X stuff $'\003'
sleep 2
sudo -u pws screen -S $SESSION_NAME2 -X stuff "python3 $PYTHON_SCRIPT2\r"
