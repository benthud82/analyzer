import requests
import mysql.connector
import json
from datetime import datetime

# Fetch the JSON data
response = requests.get('https://site.api.espn.com/apis/site/v2/sports/football/nfl/scoreboard')
data = response.json()

# Connect to the MySQL database
cnx = mysql.connector.connect(user='bentley', password='dave41', host='104.154.153.225', database='betanalyzer')
cursor = cnx.cursor()

# Loop through each event
for event in data['events']:
    # Parse the data
    nfl_id = event['id']
    game_datetime = datetime.strptime(event['date'], '%Y-%m-%dT%H:%MZ')
    game_date = game_datetime.date()  # Coordinated Universal Time (UTC).
    game_time = game_datetime.time()  # Coordinated Universal Time (UTC).
    home_team = event['competitions'][0]['competitors'][0]['team']['displayName']
    home_team_short = event['competitions'][0]['competitors'][0]['team']['abbreviation']
    away_team = event['competitions'][0]['competitors'][1]['team']['displayName']
    away_team_short = event['competitions'][0]['competitors'][1]['team']['abbreviation']
    quarter = event['status']['period']
    time_left_in_quarter = event['status']['displayClock']
    home_score = event['competitions'][0]['competitors'][0]['score']
    away_score = event['competitions'][0]['competitors'][1]['score']

    # Calculate the time remaining in the game
    minutes_left_in_quarter = int(time_left_in_quarter.split(':')[0])
    seconds_left_in_quarter = int(time_left_in_quarter.split(':')[1])
    time_left_in_quarter_in_minutes = minutes_left_in_quarter + seconds_left_in_quarter / 60
    time_remaining_in_game = (4 - quarter) * 15 + time_left_in_quarter_in_minutes

    # Insert the data into the database
    query = ("INSERT INTO nfl_scores "
             "(nfl_id, game_date, game_time, home_team, home_team_short, away_team, away_team_short, quarter, time_left_in_quarter, home_score, away_score, time_remaining_in_game) "
             "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s) "
             "ON DUPLICATE KEY UPDATE quarter = VALUES(quarter), time_left_in_quarter = VALUES(time_left_in_quarter), home_score = VALUES(home_score), away_score = VALUES(away_score), time_remaining_in_game = VALUES(time_remaining_in_game)")
    values = (nfl_id, game_date, game_time, home_team, home_team_short, away_team, away_team_short, quarter, time_left_in_quarter, home_score, away_score, time_remaining_in_game)
    cursor.execute(query, values)
# Commit the changes and close the connection
cnx.commit()
cursor.close()
cnx.close()