#!/bin/bash

SESSION_NAME="read"
SESSION_NAME2="setmode"

# \003 simuleert ctrl+c en stopt het script
# exit sluit de screen sessie

sudo -u pws screen -S $SESSION_NAME -X stuff $'\003'
sleep 2
sudo -u pws screen -S $SESSION_NAME -X stuff "exit\r"

sudo -u pws screen -S $SESSION_NAME2 -X stuff $'\003'
sleep 2
sudo -u pws screen -S $SESSION_NAME2 -X stuff "exit\r"